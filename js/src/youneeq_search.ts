/**
 * @typedef YqSearchResultsList
 * @type    {object}
 */

/**
 * Represents an individual Youneeq Search instance.
 *
 * @version 1.0.0
 * @since 1.0.0
 *
 * @param {HTMLElement} box  The HTML element on the page that will
 *                            act as the search results container.
 * @param {boolean}     auto If true, this object will request articles
 *                            immediately after initialization is complete.
 */
class YouneeqSearchHandler {

    public box: HTMLElement;
    public search: object;
    public search_type: string;
    public no_results_msg: string;
    public next_msg: string;
    public prev_msg: string;
    public is_loading: boolean;
    public is_waiting_for_id: boolean;
    public results_count: number;

    public static instances: YouneeqSearchHandler[] = [];

    private static readonly SEARCH_HOST       = `http://search.youneeq.ca/`;
    private static readonly SEARCH_PATH       = `api/search`;
    private static readonly SEARCH_IMAGE_PATH = `api/imagesearch`;
    private static readonly SESSION_ID_FILE   = `http://api.youneeq.ca/app/sessionid`;

    /**
     * @member {number}
     */
    public get page_count(): number {
        return Math.ceil( this.results_count / ( this.search_type == `image` ? 12 : 10 ) );
    }

    public constructor( box: HTMLElement, auto: boolean = true ) {
        if ( !this || !this.init_all_data ) {
            throw 'YouneeqSearchHandler() must be called with "new"';
        }

        this.box = box;
        this.search = {};
        this.search_type = `article`;
        this.no_results_msg = ``;
        this.next_msg = ``;
        this.prev_msg = ``;
        this.is_loading = false;
        this.is_waiting_for_id = false;
        this.results_count = 0;

        YouneeqSearchHandler.instances.push(
            auto ? this.init_all_data().request( [ `first` ] ) : this.init_all_data()
        );
    }

    /**
     * Generates YouneeqSearchHandler instances for each valid HTML element on the page.
     *
     * @since 1.0.0
     */
    public static generate(): void {
        let search_boxes = jQuery( `#youneeq-search, .youneeq-search, youneeq-search` ).get().sort( ( a, b ) => {
            let prio_a = jQuery( a ).attr( `data-yq-priority` ),
                prio_b = jQuery( b ).attr( `data-yq-priority` );

            prio_a = prio_a ? parseInt( prio_a ) : 0;
            prio_b = prio_b ? parseInt( prio_b ) : 0;

            if ( prio_a > prio_b ) {
                return 1;
            }
            else if ( prio_a < prio_b ) {
                return -1;
            }

            return 0;
        });

        search_boxes.forEach( e => {
            let box = jQuery( e ),
                handler = new YouneeqSearchHandler( e, !box.hasClass( `yq-no-auto` ) );
            box.data( `youneeqSearchHandler`, handler )
                .on( `yq:searchPopulatePrepare`, { handler: handler }, ( event, response ) => {
                    event.data.handler.results_count = response && response.numResults ? response.numResults : 0;
                });
        });
    }

    /**
     * Re-initializes search parameters and sends another search request.
     *
     * @since 1.0.0
     *
     * @param {string[]} tags  List of tags specifying request context.
     * @param {string}   query Search query text.
     */
    public refresh( tags: string[] = [], query: string = null ): void {
        this.search = {};
        this.init_all_data( query ).request( tags );
    }

    /**
     * Navigates to a given page of results.
     *
     * @since 1.0.0
     *
     * @param {number} page_num Page to navigate to.
     */
    public page( page_num: number ): void {
        if ( page_num > 0 && page_num <= this.page_count ) {
            this.search.pageNumber = page_num;
            this.request( [ `change_page` ] );
        }
    }

    /**
     * Navigates to the previous page of results.
     *
     * @since 1.0.0
     */
    public page_prev(): void {
        this.page( `pageNumber` in this.search ? this.search.pageNumber - 1 : 1 );
    }

    /**
     * Navigates to the next page of results.
     *
     * @since 1.0.0
     */
    public page_next(): void {
        this.page( `pageNumber` in this.search ? this.search.pageNumber + 1 : this.page_count );
    }

    /**
     * Navigates to the first page of results.
     *
     * @since 1.0.0
     */
    public page_first(): void {
        this.page( 1 );
    }

    /**
     * Navigates to the last page of results.
     *
     * @since 1.0.0
     */
    public page_last(): void {
        this.page( this.page_count );
    }

    /**
     * Send a Youneeq search request.
     *
     * @since 1.0.0
     *
     * @return {YouneeqSearchHandler}
     * @param  {string[]}             tags List of tags specifying request context.
     */
    public request( tags: string[] = [] ): this {
        if ( this.is_waiting_for_id ) {
            this.is_waiting_for_id = false;
            let self = this;

            window.setTimeout( t => {
                self.request( t );
            }, 1000, tags );
        }
        else if ( !this.is_loading && `domain` in this.search && this.search.domain && `search` in this.search ) {
            let api_url = YouneeqSearchHandler.SEARCH_HOST;
            this.is_loading = true;

            switch ( this.search_type ) {
                case `image`:
                    api_url += YouneeqSearchHandler.SEARCH_IMAGE_PATH;
                    break;
                case `article`:
                default:
                    api_url += YouneeqSearchHandler.SEARCH_PATH;
            }

            jQuery.ajax({
                url: api_url,
                crossDomain: true,
                dataType: `jsonp`,
                data: {
                    json: JSON.stringify( this.search )
                }
            })
            .done( this._populate( tags, `ajax_display` in this ) );
        }

        return this;
    }

    /**
     * Creates a set of page navigation links.
     *
     * @since 1.0.0
     *
     * @param {string[]} tags List of tags specifying request context.
     */
    public display_page_selector( tags: string[] ): void {
        let $sub_box = jQuery( `<div class="yq-search-page-container">` ).appendTo( this.box ),
            entry_first = 1,
            entry_last = 1,
            page_count = this.page_count,
            page_number = `pageNumber` in this.search ? this.search.pageNumber : 1;

        // Generate page nav links.
        if ( this.results_count ) {
            if ( page_count < 11 ) {
                entry_last = page_count;
            }
            else {
                entry_first = page_number - 4;
                entry_last = page_number + 5;

                if ( entry_first < 1 ) {
                    entry_last -= entry_first - 1;
                    entry_first = 1;
                }
                else if ( entry_last > page_count ) {
                    entry_first -= entry_last - page_count;
                    entry_last = page_count;
                }
            }

            if ( 1 == page_number ) {
                $sub_box.append( `<span class="yq-search-page-prev">${ this.prev_msg }</span> ` );
            }
            else {
                $sub_box.append( `<a class="yq-search-page-prev" href="#">${ this.prev_msg }</a> ` );
            }
            for ( let i = entry_first; i <= entry_last; i++ ) {
                if ( i == page_number ) {
                    $sub_box.append( `<span class="yq-search-page-selector selected">${ i }</span> ` );
                }
                else {
                    $sub_box.append( `<a class="yq-search-page-selector" href="#" data-yq-page="${ i }">${ i }</a> ` );
                }
            }
            if ( page_count == page_number ) {
                $sub_box.append( `<span class="yq-search-page-next">${ this.next_msg }</span>` );
            }
            else {
                $sub_box.append( `<a class="yq-search-page-next" href="#">${ this.next_msg }</a>` );
            }
        }

        // Attach nav link event handler.
        if ( tags.indexOf( `first` ) != -1 ) {
            jQuery( this.box ).on( `click`, `.yq-search-page-container a`, { handler: this }, event => {
                event.preventDefault();

                let button = jQuery( event.currentTarget ),
                    self = event.data.handler;

                if ( button.hasClass( `yq-search-page-prev` ) ) {
                    self.page_prev();
                }
                else if ( button.hasClass( `yq-search-page-next` ) ) {
                    self.page_next();
                }
                else {
                    self.page( parseInt( button.data( `yqPage` ) ) );
                }
            });
        }
    }

    /**
     * Generate recommended article HTML and display it on the page.
     *
     * @since 1.0.0
     *
     * @param {YqSearchResultsList} response Returned data from Youneeq search request.
     * @param {string[]}            tags     List of tags specifying request context.
     */
    private display( response: YqSearchResultsList, tags: string[] ): void {
        if ( response ) {
            if ( `numResults` in response && ! response.numResults ) {
                jQuery( this.box ).text( this.no_results_msg.replace( `%1`, this.search.search ) );
            }
            else {
                let items = [],
                    is_images = false,
                    $box = jQuery( this.box ).empty();

                if ( response.stories ) {
                    items = response.stories;
                }
                else if ( response.images ) {
                    items = response.images;
                    is_images = true;
                }

                for ( let i = 0, max = items.length; i < max; i++ ) {
                    let id    = items[ i ].contentId ? items[ i ].contentId : ``,
                        title = items[ i ].title ? items[ i ].title : ``,
                        url   = items[ i ].url ? items[ i ].url : ``,
                        img   = items[ i ].imageUrl ? items[ i ].imageUrl : ``,
                        desc  = is_images ? ( items[ i ].caption ? items[ i ].caption : `` ) :
                            ( items[ i ].description ? items[ i ].description : `` );

                    if ( is_images ) {
                        $box.append(
`<a href="${ url }" class="yqs-article-image" data-yq-id="${ id }" data-yq-title="${ title }" data-yq-url="${ url }">
    ${ img ? `<img class="yq-image" src="${ img }" alt="${ title }" title="${ desc }" />` : `` }
</a>`
                        );
                    }
                    else {
                        $box.append(
`<div class="yqs-article" data-yq-id="${ id }" data-yq-title="${ title }" data-yq-url="${ url }">
    <a href="${ url }">
        ${ img ? `<img class="yq-image" src="${ img }" alt="${ title }" />` : `` }
        <h3 class="yq-title">${ title }</h3>
     </a>
     ${ desc ? `<p class="yq-desc">${ desc }</p>` : `` }
</div>`
                        );
                    }
                }
            }

            this.display_page_selector( tags );
        }
    }

    /**
     * Call object initialization methods.
     *
     * @since 1.0.0
     *
     * @return {YouneeqSearchHandler}
     * @param  {string}               query Search query text.
     */
    private init_all_data( query: string = null ): this {
        let args = this.get_args();

        return this.init_request_data( args )
            .init_search_data( args, query )
            .init_method_overrides( args );
            //.init_features( args );
    }

    /**
     * Get element arguments from data attributes,
     * search form element, and search data function.
     *
     * @since 1.0.0
     *
     * @return {object}
     */
    private get_args(): object {
        let args = {}, data = {}, form = {}, form_element = null;

        // Get args from data attributes.
        for ( let i = 0, max = this.box.attributes.length; i < max; i++ ) {
            let arg_name = this.box.attributes[ i ].name;

            if ( arg_name.substr( 0, 8 ) == `data-yq-` ) {
                args[ arg_name.substr( 8 ).replace( /-/g, `_` ) ] = this.box.attributes[ i ].value;
            }
        };

        // Get args from search data function.
        if ( `search_function` in args && args.search_function in window ) {
            if ( typeof args.search_function == `function` ) {
                data = window[ args.search_function ]( this );
            }
            else if ( typeof args.search_function == `object` ) {
                data = window[ args.search_function ];
            }
        }

        // Get args from search form.
        if ( `search_form` in data && data.search_form ) {
            form_element = data.search_form;
        }
        else if ( `search_form_id` in args && args.search_form_id ) {
            form_element = `#${ args.search_form_id }`;
        }
        if ( form_element ) {
            jQuery( form_element ).submit( { self: this }, event => {
                event.preventDefault();
                event.data.self.refresh();
            })
            .serializeArray().forEach( p => {
                if ( p.value ) {
                    switch ( p.name ) {
                        case `search`:
                        case `s`:
                        case `q`:
                        case `query`:
                            form[`search`] = p.value;
                            break;
                        default:
                            form[ p.name ] = p.value;
                    }
                }
            });
        }

        return { ...args, ...form, ...data };
    }

    /**
     * Collect basic search handler properties.
     *
     * @since 1.0.0
     *
     * @return {YouneeqSearchHandler}
     * @param  {object}               args Arguments object.
     */
    private init_request_data( args: object ): this {
        if ( `search_type` in args ) {
            this.search_type = args.search_type;
        }

        if ( `no_results_msg` in args ) {
            this.no_results_msg = args.no_results_msg;
        }
        else {
            this.no_results_msg = `No results found`;
        }

        if ( `next_msg` in args ) {
            this.next_msg = args.next_msg;
        }
        else {
            this.next_msg = `Next`;
        }

        if ( `prev_msg` in args ) {
            this.prev_msg = args.prev_msg;
        }
        else {
            this.prev_msg = `Prev`;
        }

        return this;
    }

    /**
     * Collect search request data.
     *
     * @since 1.0.0
     *
     * @return {YouneeqSearchHandler}
     * @param  {object}               args  Arguments object.
     * @param  {string}               query Search query string.
     */
    private init_search_data( args: object, query: string = null ): this {
        if ( query !== null ) {
            this.search.search = query;
        }
        else {
            this.init_search_query( args );
        }

        if ( !this.process_search_param( args, `domain`, null, `search_domain` ) ) {
            this.search.domain = window.location.hostname;
        }
        this.init_user_id( args );
        this.process_search_param( args, `contentInfo`, Boolean, `search_content_info` );
        this.process_search_param( args, `endDate`, YouneeqSearchHandler.process_date_string, `search_end_date` );
        this.process_search_param( args, `maxArticleAge`, parseInt, `search_max_age` );
        this.process_search_param( args, `personalized`, Boolean, `search_personalized` );
        if ( !this.process_search_param( args, `pageNumber`, parseInt, `search_page_number` ) ) {
            this.search.pageNumber = 1;
        }
        this.process_search_param( args, `startDate`, YouneeqSearchHandler.process_date_string, `startdate`, `search_start_date` );
        this.process_search_param( args, `killPromoteInfo`, Boolean, `search_kp_info` );

        if ( !this.process_search_param( args, `orderBy`, null, `search_order_by` ) ) {
            this.search.orderBy = `relevance`;
        }
        return this;
    }

    /**
     * Collects the search query string.
     *
     * @since 1.0.0
     *
     * @param {object} args Arguments object.
     */
    private init_search_query( args: object ): void {
        if ( !this.process_search_param( args, `search`, null, `search_query` ) ) {
            let query_params = {}, query = ``;

            window.location.search.substr( 1 ).split( `&` ).forEach( c => {
                let entry = c.split( `=` );

                query_params[ entry[0] ] = entry.length > 1 ? entry[1] : false;
            });

            if ( `search_param` in args && args.search_param in args ) {
                this.search.search = args[ args.search_param ];
            }
            else if ( `search_param` in args && args.search_param in query_params ) {
                query = query_params[ args.search_param ];
            }
            else if ( `s` in query_params ) {
                query = query_params.s;
            }
            else if ( `search` in query_params ) {
                query = query_params.search;
            }
            else if ( `query` in query_params ) {
                query = query_params.query;
            }
            else if ( `q` in query_params ) {
                query = query_params.q;
            }

            if ( query ) {
                try {
                    this.search.search = decodeURIComponent( query.replace( /\+/, ` ` ) );
                }
                catch ( e ) {}
            }
        }
    }

    /**
     * Collects the Youneeq UID string.
     *
     * @param {object} args Arguments object.
     */
    private init_user_id( args: object ): void {
        if ( !this.process_search_param( args, `userId`, null, `search_user_id` ) ) {
            let user_id = null,
                has_storage = true;

            try {
                user_id = localStorage.getItem( `yq_session` );
            }
            catch ( e ) {
                has_storage = false;
            }

            if ( user_id && user_id.length >= 8 ) {
                this.search.userId = user_id;
            }
            else if ( ( 'yqs' in window ) && ( typeof yqs == 'string' ) && yqs.length >= 8 ) {
                this.search.userId = yqs;
            }
            else {
                this.is_waiting_for_id = true;
                let self = this;

                jQuery( `<span></span>` ).load( YouneeqSearchHandler.SESSION_ID_FILE, id => {
                    if ( id.length >= 8 ) {
                        self.search.userId = id;
                        if ( has_storage ) {
                            localStorage.setItem( `yq_session`, id );
                        }
                    }

                    self.is_waiting_for_id = false;
                });
            }
        }
    }

    /**
     * Set up method overrides.
     *
     * @since 1.0.0
     *
     * @return {YouneeqSearchHandler}
     * @param  {object}               args Arguments object.
     */
    private init_method_overrides( args: object ): this {
        if ( args.ajax_display_function ) {
            this.ajax_display = window[ args.ajax_display_function ];
        }
        else if ( args.display_function ) {
            this.display = window[ args.display_function ];
        }

        return this;
    }

    /**
     * Finds, processes, and sets a value from a given arguments
     * array into this YouneeqSearchHandler's 'search' object.
     *
     * @since 1.0.0
     * @see   YouneeqSearchHandler#search
     *
     * @return {boolean}            Whether or not a key was found in args.
     * @param  {object}   args      Arguments object.
     * @param  {string}   param     Key value to search for and save into search object.
     * @param  {Function} processor Function with which to process the value.
     * @param  {string[]} arg_names Extra keys to search for in args.
     */
    private process_search_param( args: object, param: string, processor: Function, ...arg_names: string[] ): boolean {
        if ( param in args ) {
            if ( !processor ) {
                processor = v => v;
            }

            try {
                this.search[ param ] = processor( args[ param ] );
                return true;
            }
            catch ( e ) {};
        }

        for ( let i = 0, max = arg_names.length; i < max; i++ ) {
            if ( arg_names[ i ] in args ) {
                if ( !processor ) {
                    processor = v => v;
                }

                try {
                    this.search[ param ] = processor( args[ param ] );
                    return true;
                }
                catch ( e ) {};
            }
        }

        return false;
    }

    /**
     * Formats a Date object or date string into an ISO-8601 date string.
     *
     * @since 1.0.0
     *
     * @return {string}
     * @param  {Date|string} date Date or string to process.
     */
    private static process_date_string( date: Date|string ): string {
        let result = ``;

        try {
            result = ( date instanceof Date ? date : new Date( date ) ).toISOString();

            try {
                result = result.replace( `Z`, jzTimezoneDetector.determine_timezone().timezone.utc_offset );
            }
            catch ( e ) {}
        }
        catch ( e ) {}

        return result;
    }

    /**
     * Return a function that triggers populate events and displays recommendations.
     *
     * @since 1.0.0
     *
     * @return {Function}
     * @param  {string[]} tags List of tags specifying search request context.
     * @param  {boolean}  ajax True if ajax display method should be used
     */
    private _populate( tags: string[], ajax: boolean = false ): Function {
        if ( ajax ) {
            return response => {
                this.is_loading = false;
                let $box = jQuery( this.box );

                $box.trigger( `yq:searchPopulatePrepare`, [ response, tags ] );
                this.ajax_display( response, tags, r => {
                    $box.trigger( `yq:searchPopulateAttach`, [ r, tags ] );
                });
            }
        }
        else {
            return response => {
                this.is_loading = false;
                let $box = jQuery( this.box );

                $box.trigger( `yq:searchPopulatePrepare`, [ response, tags ] );
                this.display( response, tags );
                $box.trigger( `yq:searchPopulateAttach`, [ response, tags ] );
            }
        }
    }

}

// Automatically detect and initialize YouneeqSearchHandler instances.
jQuery( function() {
    if ( ! jQuery( `html.yq-no-auto` ).length ) {
        YouneeqSearchHandler.generate();
    }
});
