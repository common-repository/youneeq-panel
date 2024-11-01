/**
 * Represents an individual Youneeq recommendation instance.
 *
 * A YouneeqHandler instance is automatically created for each element with the ID or class
 * "youneeq", or of the "youneeq-section" type. Parameters for each YouneeqHandler instance can be
 * passed as data attributes on the containing element, and must be prefixed with "data-yq-".
 * Some parameters can have multiple values if they are separated by "|". For example:
 *
 *     <div id="youneeq" data-yq-suggest-count="6" data-yq-observe="true" data-yq-content-id="12345"
 *         data-yq-features="google-analytics|gigya"></div>
 *
 * Recognized parameters include:
 *
 * suggest-count: Number of articles to display.
 * suggest-function: Name of a function that gathers suggest parameters and returns it as an object.
 *     Either suggest-function or suggest-count must be defined
 *     in order for recommendations to be returned.
 * suggest-categories: Category filter for recommended articles. Accepts multiple values.
 * suggest-domains: Domain filter for recommended articles.
 *     Can be "true", "false", or a list of domains. Accepts multiple values.
 * suggest-date-start: Starting date for date filter.
 * suggest-date-end: Ending date for date filter.
 * suggest-panel-custom: Defines article metadata fields to be returned. Accepts multiple values.
 *
 * observe: Indicates that the article should be observed. Can be "true" or "false".
 * observe-function: Name of a function that gathers article metadata and returns it as an object.
 *     Either observe-function or observe and content-id must be defined
 *     in order for articles to be observed.
 * observe-title: Title of the article.
 * observe-image: Article image URL.
 * observe-description: Article description.
 * observe-date: Article publish date.
 * observe-categories: Article categories. Accepts multiple values.
 * observe-tags: Article tags. Accepts multiple values.
 *
 * content-id: Content ID of the current article.
 *     Content ID can also be provided through suggest-function or observe-function.
 * domain: Indicates the current site's domain name.
 * alt-href: Page URL.
 * display-function: Name of a function that accepts "response" and "tags" parameters
 *     and generates recommendation HTML.
 * ajax-display-function:
 * features: List of additional behaviours to be assigned to this instance.
 *     Recognized features include "no-google-analytics", "gigya", and "infinite-scroll".
 *     Accepts multiple values.
 * priority: Priority of the recommendation element.
 *     Elements with a higher priority will process first.
 *     Defaults to 0 if not set.
 *
 * @version 3.0.6
 * @since   3.0.6 Added initialization arguments to constructor.
 * @since   3.0.5 Improved height triggering for infinite scroll handling.
 * @since   3.0.4 Added new GA override ability.
 * @since   3.0.3 Improved infinite scroll handling.
 * @since   3.0.2 Removed GA tracker auto-detection.
 * @since   3.0.1 Added manual GA tracker settings.
 * @since   3.0.0 Version number now matches WordPress plugin version.
 *                Refactored click tracking, now require HTTPS for yqmin script,
 *                added error check to constructor.
 * @since   1.2.0 Refactored observe metadata auto-detection.
 * @since   1.1.0 Refactored feature detection.
 * @since   1.0.0
 */
class YouneeqHandler {

    public box: HTMLElement;
    public id_num: number;
    public content_id: string;
    public alt_href: string;
    public content_type: string;
    public suggest: object;
    public observe: object;
    public tracking: object;
    public scrolling: object;
    public features: string[];
    public is_loading: boolean;
    public use_scroll_handler: boolean = false;
    public scroll_ready: boolean = true;

    public static instances: YouneeqHandler[] = [];

    /**
     * Creates and registers a new YouneeqHandler.
     *
     * @since 3.0.6 Added args parameter.
     * @since 3.0.0 Added error check.
     * @since 1.0.0
     *
     * @param {HTMLElement} box  The HTML element on the page that will
     *                           act as the recommendation container.
     * @param {boolean}     auto If true, this object will request articles
     *                           immediately after initialization is complete.
     * @param {object|null} args Initialization arguments. Will be overridden by data attributes.
     */
    public constructor( box: HTMLElement, auto: boolean = true, args: object|null = null ) {
        if ( !this || !this.init_all_data ) {
            throw `YouneeqHandler() must be called with "new"`;
        }

        this.box = box;
        this.id_num = YouneeqHandler.instances.length;
        this.content_id = ``;
        this.alt_href = ``;
        this.content_type = `article`;
        this.suggest = {};
        this.observe = {};
        this.tracking = {};
        this.scrolling = {};
        this.features = [];
        this.is_loading = false;

        // Initialize suggest/observe/etc arguments and push to instances array.
        YouneeqHandler.instances.push(
            auto ? this.init_all_data( args ).request( [ `first`, `observe` ] ) : this.init_all_data( args)
        );
    }

    /**
     * Generates YouneeqHandler instances for each valid HTML element on the page.
     *
     * @since 3.0.0 Always use HTTPS for yqmin script.
     * @since 1.2.0 Can now disable automatic loading of helper scripts.
     * @since 1.0.0
     *
     * @param {boolean} include_scripts If additional helper scripts should be
     *                                  loaded from the Youneeq API site.
     */
    public static generate( include_scripts: boolean = true ): void {
        // Get and sort recommender elements.
        let youneeq_boxes = jQuery( `#youneeq, .youneeq, youneeq-section` ).get().sort( function( a, b ) {
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

        // Create handlers for recommender elements.
        let builder;
        if ( youneeq_boxes.length ) {
            builder = function() {
                Yq.onready( function() {
                    youneeq_boxes.forEach( e => {
                        let box = jQuery( e ),
                            handler = new YouneeqHandler( e, ! box.hasClass( `yq-no-auto` ) );
                        box.data( `youneeqHandler`, handler );
                    });
                });
            }
        }
        else {
            builder = jQuery.noop;
        }

        // Fetch helper scripts.
        if ( include_scripts ) {
            let scripts = [];

            if ( !( 'JSON' in window ) ) {
                scripts.push( jQuery.getScript( `//api.youneeq.ca/scripts/json2.js` ) );
            }
            if ( !( 'jzTimezoneDetector' in window ) ) {
                scripts.push( jQuery.getScript( `//api.youneeq.ca/scripts/detect_timezone.js` ) );
            }
            if ( !( 'Yq' in window ) ) {
                scripts.push( jQuery.getScript( `https://api.youneeq.ca/app/yqmin` ) );
            }

            if ( scripts.length ) {
                jQuery.when.apply( jQuery, scripts ).then( builder );
            }
            else {
                builder();
            }
        }
        else {
            builder();
        }
    }

    /**
     * Handle story link clicks. Need to handle left click differently than right/middle clicks
     * since GA data has to finish sending before navigating away from the page.
     *
     * @since 3.0.4
     *
     * @param {object} story jQuery object of the story element.
     * @param {object} link  jQuery object of the clicked link.
     * @param {object} event GA tracking parameters.
     */
    public static track_click( story, link, event ): void {
        let left_click = event.which == 1;

        if ( left_click ) {
            event.preventDefault();
        }

        let yq_id = story.data( `yqId` ),
            yq_title = Yq.titleTrim( story.data( `yqTitle` ) ),
            yq_url = story.data( `yqUrl` ),
            link_url = link.attr( `href` ),
            ga_obj = event.data.ga || null,
            ga_handler = event.data.ga_handler || null;

        Yq.yq_panel_click( yq_url, yq_title, yq_id );

        // Get GA object and handler from override function.
        if ( event.data.ga_override && typeof window[ event.data.ga_override ] != 'undefined' ) {
            let ga_override = window[ event.data.ga_override ]( story, link, ga_obj, ga_handler );

            ga_obj = ga_override.obj || ga_obj;
            ga_handler = ga_override.handler || ga_handler;
        }

        // Check if GA object exists, then send event.
        if ( ga_obj && ga_handler && typeof window[ ga_obj ] != 'undefined' ) {
            let ga_data = {
                hitType:       `event`,
                eventCategory: `Articles`,
                eventAction:   `Youneeq View`,
                eventLabel:    yq_url
            };

            // Send GA event using beacon API if supported by the browser.
            if ( `sendBeacon` in navigator ) {
                ga_data.transport = `beacon`;
                window[ ga_obj ]( ga_handler, ga_data );

                if ( left_click ) {
                    window.location = link_url;
                }
            }
            else if ( left_click ) {
                let do_nav = function() {
                    window.location = link_url;
                };

                ga_data.hitCallback = do_nav;
                window[ ga_obj ]( ga_handler, ga_data );
                window.setTimeout( do_nav, 1000 );
            }
            else {
                window[ ga_obj ]( ga_handler, ga_data );
            }
        }
        else if ( left_click ) {
            window.location = link_url;
        }
    }

    /**
     * Registers event handler to detect user scrolling to the bottom of the recommendation element.
     *
     * @since 3.0.5 Now tracks user position on page relative to the recommendation container.
     * @since 3.0.3
     *
     * @param {YouneeqHandler} self Youneeq handler object to initialize scrolling for.
     * @param {object}         page jQuery object containing the window object.
     */
    public static register_scroll_handler( self, page ): void {
        if ( !self.use_scroll_handler ) {
            self.use_scroll_handler = true;

            let box = jQuery( self.box );

            page.on( `scroll.yq`, function( e ) {
                if ( self.scroll_ready && ( box.offset().top + box.height() -
                    page.scrollTop() - page.height() ) < self.scrolling.offset ) {
                    self.scroll_ready = false;
                    page.trigger( `yq:scrollBottom${ self.id_num }` );

                    window.setTimeout( function( self ) {
                        self.scroll_ready = true;
                    }, self.scrolling.cooldown, self );
                }
            });
        }
    }

    /**
     * Re-initializes suggest parameters and sends another recommendation request.
     *
     * @since 1.2.0
     *
     * @param {string[]} tags List of tags specifying request context.
     */
    public refresh( tags: string[] = [] ): void {
        this.suggest = {};
        this.init_all_data().request( tags );
    }

    /**
     * Send a Youneeq request.
     *
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {string[]}       tags List of tags specifying request context.
     */
    public request( tags: string[] = [] ): this {
        if ( !this.is_loading ) {
            this.is_loading = true;
            let data = {},
                can_observe = false,
                request_method = window.yq_sent_request ? Yq.observeMin : Yq.observe;
            window.yq_sent_request = true;

            for ( let i = 0, max = tags.length; i < max; i++ ) {
                if ( `observe` == tags[ i ] ) {
                    can_observe = true;
                }
            }

            if ( this.content_id ) {
                data.content_id = this.content_id;
            }
            if ( this.content_type ) {
                data.content_type = this.content_type;
            }
            if ( this.alt_href ) {
                data.alt_href = this.alt_href;
            }

            if ( Object.keys( this.suggest ).length ) {
                data.suggest = [ this.suggest ];
            }
            if ( can_observe && Object.keys( this.observe ).length ) {
                data.observe = [ this.observe ];
            }

            request_method( data, this._populate( tags, `ajax_display` in this ) );
        }

        return this;
    }

    /**
     * Generate recommended article HTML and display it on the page.
     *
     * @since 1.0.0
     *
     * @param {YqResultsList} response Returned data from Youneeq request.
     * @param {string[]}      tags     List of tags specifying request context.
     */
    public display( response: YqResultsList, tags: string[] ): void {
        if ( response && response.suggest && response.suggest.node ) {
            let stories = response.suggest.node,
                $box = jQuery( this.box );

            for ( let i = 0, max = stories.length; i < max; i++ ) {
                let id    = stories[ i ].id ? stories[ i ].id : ``,
                    title = stories[ i ].title ? stories[ i ].title : ``,
                    url   = stories[ i ].url ? stories[ i ].url : ``,
                    img   = stories[ i ].image ? stories[ i ].image : ``,
                    desc  = stories[ i ].description ? stories[ i ].description : ``;

                $box.append(
`<div class="yq-article" data-yq-id="${ id }" data-yq-title="${ title }" data-yq-url="${ url }">
    <a href="${ url }">${ img ? `<img class="yq-image" src="${ img }" alt="${ title }" />` : `` }
        <h3 class="yq-title">${ title }</h3>
    </a>
    ${ desc ? `<p class="yq-desc">${ desc }</p>` : `` }
</div>`
                );
            }
        }
    }

    /**
     * Call object initialization methods.
     *
     * @since 3.0.1 Added GA tracking initialization.
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     */
    private init_all_data( base_args ): this {
        let args = this.get_args( base_args );
        return this.init_request_data( args )
            .init_suggest_data( args )
            .init_observe_data( args )
            .init_tracking( args )
            .init_scrolling( args )
            .init_method_overrides( args )
            .init_features( args )
            .init_handlers( args );
    }

    /**
     * Get element arguments from data attributes.
     *
     * @since 1.0.0
     *
     * @return {object} Arguments object.
     */
    private get_args( base_args ): object {
        let args = base_args ? base_args : {};

        for ( let i = 0, max = this.box.attributes.length; i < max; i++ ) {
            let arg_name = this.box.attributes[ i ].name;
            if ( arg_name.substr( 0, 8 ) == `data-yq-` ) {
                args[ arg_name.substr( 8 ).replace( /-/g, `_` ) ] = this.box.attributes[ i ].value;
            }
        }

        return args;
    }

    /**
     * Collect basic request data.
     *
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
     */
    private init_request_data( args: object ): this {
        if ( `content_id` in args ) {
            this.content_id = args.content_id;
        }

        if ( `alt_href` in args ) {
            this.alt_href = args.alt_href;
        }
        else {
            this.alt_href = YouneeqHandler.get_og_tag( `url` );
        }

        if ( `content_type` in args ) {
            this.content_type = args.content_type;
        }

        return this;
    }

    /**
     * Collect suggest request data.
     *
     * @since 1.2.0 Suggest data function now has the current
     *              YouneeqHandler instance passed as a parameter.
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
     */
    private init_suggest_data( args: object ): this {
        if ( args.count || args.suggest_count || args.suggest_function ) {
            let count = 0, data = {};

            if ( `suggest_function` in args && args.suggest_function in window ) {
                data = window[ args.suggest_function ]( this );
            }

            if ( `name` in data ) {
                this.content_id = data.name;
            }

            if ( `count` in data ) {
                count = data.count;
            }
            else if ( `count` in args ) {
                count = args.count;
            }
            else if ( `suggest_count` in args ) {
                count = args.suggest_count;
            }

            if ( count ) {
                this.suggest = {
                    type:              `node`,
                    count:             `${ count }`,
                    is_panel_detailed: `true`,
                };

                if ( `categories` in data ) {
                    this.suggest.categories = data.categories;
                }
                else if ( `suggest_categories` in args ) {
                    this.suggest.categories = YouneeqHandler.split( args.suggest_categories );
                }

                if ( `domains` in data ) {
                    if ( data.domains === true || data.domains === false ) {
                        this.suggest.isAllClientDomains = `true`;
                    }
                    else {
                        this.suggest.domains = data.domains;
                    }
                }
                else if ( `suggest_domains` in args ) {
                    if ( args.suggest_domains == `true` ) {
                        this.suggest.isAllClientDomains = `true`;
                    }
                    else {
                        this.suggest.domains = YouneeqHandler.split( args.suggest_domains );
                    }
                }
                else {
                    this.suggest.isAllClientDomains = `false`;
                }

                if ( `date_start` in data ) {
                    this.suggest.date_start = data.date_start;
                }
                else if ( `suggest_date_start` in args ) {
                    this.suggest.date_start = new Date( args.suggest_date_start ).toISOString();
                }

                if ( `date_end` in data ) {
                    this.suggest.date_end = data.date_end;
                }
                else if ( `suggest_date_end` in args ) {
                    this.suggest.date_end = new Date( args.suggest_date_end ).toISOString();
                }

                if ( `panel_custom` in data ) {
                    this.suggest.panel_custom = data.panel_custom;
                }
                else if ( `suggest_panel_custom` in args ) {
                    this.suggest.panel_custom = YouneeqHandler.split( args.suggest_panel_custom );
                }

                if ( `panel_type` in data ) {
                    this.suggest.panel_type = data.panel_type;
                }
                else if ( `suggest_panel_type` in args ) {
                    this.suggest.panel_type = args.suggest_panel_type;
                }

                if ( `options` in data ) {
                    this.suggest.options = data.options;
                }
                else if ( `suggest_options` in args ) {
                    this.suggest.options = YouneeqHandler.split( args.suggest_options );
                }
            }
        }

        return this;
    }

    /**
     * Collect observe request data.
     *
     * @since 1.2.0 Observe data function now has the current
     *              YouneeqHandler instance passed as a parameter.
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
     */
    private init_observe_data( args: object ): this {
        if ( args.observe || args.observe_function ) {
            let title = ``, data = {};

            if ( `observe_function` in args ) {
                data = window[ args.observe_function ]( this );
            }

            if ( `name` in data ) {
                this.content_id = data.name;
            }

            if ( `observe` in data && ! data.observe ) {
                return this;
            }

            if ( `title` in data ) {
                title = data.title;
            }
            else if ( `observe_title` in args ) {
                title = args.observe_title;
            }
            else {
                title = YouneeqHandler.get_og_tag( `title` );
            }

            if ( this.content_id && title ) {
                this.observe = {
                    type:  `node`,
                    title: title
                };

                if ( `url` in data ) {
                    this.alt_href = data.url;
                }

                if ( `image` in data ) {
                    this.observe.image = data.image;
                }
                else if ( `observe_image` in args ) {
                    this.observe.image = args.observe_image;
                }
                else {
                    this.observe.image = YouneeqHandler.get_og_tag( `image` );
                }

                if ( `description` in data ) {
                    this.observe.description = data.description;
                }
                else if ( `observe_description` in args ) {
                    this.observe.description = args.observe_description;
                }
                else {
                    this.observe.description = YouneeqHandler.get_og_tag( `description` );
                }

                if ( `create_date` in data ) {
                    this.observe.create_date = data.create_date;
                }
                else if ( `observe_date` in args ) {
                    this.observe.create_date = new Date( args.observe_date ).toISOString();
                }
                else {
                    let date = YouneeqHandler.get_meta_tag( `article:published_time`, `date` );
                    this.observe.create_date = date instanceof Date ? date.toISOString() : date;
                }

                if ( `categories` in data ) {
                    this.observe.categories = data.categories;
                }
                else if ( `observe_categories` in args ) {
                    this.observe.categories = YouneeqHandler.split( args.observe_categories );
                }

                if ( `tags` in data ) {
                    this.observe.tags = data.tags;
                }
                else if ( `observe_tags` in args ) {
                    this.observe.tags = YouneeqHandler.split( args.observe_tags );
                }

                if ( `content_type` in data ) {
                    this.content_type = data.content_type;
                }
            }
        }

        return this;
    }

    /**
     * Set up Google Analytics tracking parameters.
     *
     * @since 3.0.4 Added runtime override function.
     * @since 3.0.1
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
     */
    private init_tracking( args: object ): this {
        // GA object to be used.
        if ( args.ga_function ) {
            this.tracking.ga = args.ga_function;
        }
        else {
            this.tracking.ga = `ga`;
        }

        // Name of GA tracker to use (without '.send' appended at the end).
        if ( args.ga_tracker ) {
            this.tracking.ga_tracker = args.ga_tracker;
        }
        else {
            this.tracking.ga_tracker = null;
        }

        // Name of GA runtime override function.
        if ( args.ga_override_function ) {
            this.tracking.ga_override = args.ga_override_function;
        }
        else {
            this.tracking.ga_override = null;
        }

        return this;
    }

    private init_scrolling( args: object ): this {
        if ( args.scroll_offset ) {
            let offset = parseInt( args.scroll_offset );
            this.scrolling.offset = !isNaN( offset ) ? offset : 300;
        }
        else {
            this.scrolling.offset = 300;
        }

        if ( args.scroll_cooldown ) {
            let cooldown = parseInt( args.scroll_cooldown );
            this.scrolling.cooldown = !isNaN( cooldown ) ? cooldown : 3000;
        }
        else {
            this.scrolling.cooldown = 3000;
        }

        return this;
    }

    /**
     * Set up method overrides.
     *
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
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
     * Activate optional recommendation functionality.
     *
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
     */
    private init_features( args: object ): this {
        if ( args.features ) {
            this.features = YouneeqHandler.split( args.features );
        }

        return this;
    }

    /**
     * Set up event handlers.
     *
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     * @param  {object}         args Arguments object.
     */
    private init_handlers( args: object ): this {
        let $box = jQuery( this.box ),
            disable_ga_tracking = false;

        for ( let i = 0, max = this.features.length; i < max; i++ ) {
            switch ( this.features[ i ] ) {
                case `no-google-analytics`:
                    disable_ga_tracking = true;
                    break;
                case `gigya`:
                    this.send_gigya_data();
                    break;
                case `infinite-scroll`:
                    this.setup_infinite_scroll();
                    break;
                case `google-analytics`:
                    break;
                default:
                    window.console.warn( `YouneeqHandler: ${ this.features[ i ] } is not a recognized feature` );
            }
        }

        $box.one( `yq:populateAttach`, { disable_ga_tracking: disable_ga_tracking, tracking_args: this.tracking }, this.attach_click_tracking );

        return this;
    }

    /**
     * Attaches infinite scroll handler.
     *
     * @since 3.0.5 Now adds separate scroll handler for each instance.
     * @since 3.0.3 Separated scroll detection into separate function.
     * @since 1.0.0
     *
     * @return {YouneeqHandler}
     */
    private setup_infinite_scroll(): this {
        let page = jQuery( window ),
            self = this;

        YouneeqHandler.register_scroll_handler( self, page );

        page.on( `yq:scrollBottom${ this.id_num }`, function() {
            self.request( [ `scroll` ] );
        });

        return this;
    }

    /**
     * Attaches click tracking to each displayed article.
     *
     * @since 3.0.4 Added GA tracker runtime override.
     * @since 3.0.2 Removed GA tracker auto-detection.
     * @since 3.0.1 Added support for manually setting GA tracker.
     * @since 1.0.0
     *
     * @param {object}        event    jQuery event object.
     * @param {YqResultsList} response Returned data from Youneeq request.
     * @param {string[]}      tags     List of tags specifying request context.
     */
    private attach_click_tracking( event: object, response: YqResultsList, tags: string[] ): void {
        let ga_handler = event.data.tracking_args.ga_tracker ? `${event.data.tracking_args.ga_tracker}.send` : `send`;

        jQuery( this ).on( `mousedown.yq`, `.yq-article a:not(.no-yq-tracking)`, {
            ga:          event.data.tracking_args.ga,
            ga_handler:  ga_handler,
            ga_override: event.data.tracking_args.ga_override
        }, function( e ) {
            let link = jQuery( this );
            YouneeqHandler.track_click( link.parents( `.yq-article` ), link, e );
        })
        .on( `mousedown.yq`, `a.yq-article:not(.no-yq-tracking)`, {
            ga:          event.data.tracking_args.ga,
            ga_handler:  ga_handler,
            ga_override: event.data.tracking_args.ga_override
        }, function( e ) {
            let link = jQuery( this );
            YouneeqHandler.track_click( link, link, e );
        });
    }

    /**
     * Sends Gigya user profile to the server.
     *
     * @since 1.2.0 Updated to current Youneeq IDM specifications.
     * @since 1.0.0
     *
     * @param {number} retry Number of times to retry if Gigya is not yet ready.
     */
    private send_gigya_data( retry: number = 1 ): void {
        if ( gigya.isReady ) {
            gigya.socialize.getUserInfo({
                callback: response => {
                    if ( response.errorCode == 0 ) {
                        let user = response.user,
                            fields = [
                                `birthDay`,
                                `birthMonth`,
                                `birthYear`,
                                `city`,
                                `country`,
                                `email`,
                                `firstName`,
                                `gender`,
                                `lastName`,
                                `loginProvider`,
                                `loginProviderUID`,
                                `nickname`,
                                `providers`,
                                `state`,
                                `zip`
                            ],
                            data = {
                                idm: {
                                    id: user.ID,
                                    profile: { UID: user.ID }
                                }
                            };

                        for ( let field of fields ) {
                            if ( field in user ) {
                                data.idm.profile[ field ] = user[ field ];
                            }
                        }
                        Yq.observeMin( data, jQuery.noop );
                    }
                }
            });
        }
        else if ( retry > 0 ) {
            window.setTimeout( retry => {
                this.send_gigya_data( retry );
            }, 5000, retry - 1 );
        }
    }

    /**
     * Attempts to split a string into an array of strings.
     *
     * @since 1.1.0
     *
     * @return {string[]}         Array of strings split from subject.
     * @param  {string}   subject String to split.
     */
    private static split( subject: string ): string[] {
        let pipe_matches = subject.match( /\|/g ),
            pipes = pipe_matches ? pipe_matches.length : 0;

        if ( pipes ) {
            return subject.split( `|` );
        }
        else {
            return subject.split( `,` );
        }
    }

    /**
     * Gets the content of a meta element on the page.
     *
     * Normally returns a string, but may return a Date if 'date' is passed as the value for "format."
     *
     * @since 1.2.0
     *
     * @return {string|Date|null}        Formatted meta tag content.
     * @param  {string}           name   Name of the meta tag.
     * @param  {string}           format If "date" is passed, a Date object will be returned.
     * @param  {string}           id     Name of the HTML attribute containing the tag name.
     * @param  {string}           value  Name of the HTML attribute containing the tag content.
     */
    private static get_meta_tag( name: string, format?: string, id: string = `property`, value: string = `content` ):
        string|Date|null {
        let tag = jQuery( `meta[${ id }="${ name }"]` ),
            result = null;

        switch ( format ) {
            case `date`:
                result = tag.length ? new Date( tag.attr( value ) ) : null;
                break;
            default:
                result = tag.length ? tag.attr( value ) : ``;
        }

        return result;
    }

    /**
     * Gets the content of an open graph tag on the page.
     *
     * @since 1.2.0
     *
     * @return {string}
     * @param  {string} name Name of the meta tag (with "og:" prefix omitted).
     */
    private static get_og_tag( name: string ): string {
        return YouneeqHandler.get_meta_tag( `og:${ name }` ) as string;
    }

    /**
     * Return a function that triggers populate events and displays recommendations.
     *
     * @since 1.0.0
     *
     * @return {Function}
     * @param  {string[]} tags List of tags specifying request context.
     * @param  {boolean}  ajax True if ajax display method should be used
     */
    private _populate( tags: string[], ajax: boolean = false ): Function {
        if ( ajax ) {
            return response => {
                this.is_loading = false;
                let $box = jQuery( this.box );

                $box.trigger( `yq:populatePrepare`, [ response, tags ] );
                this.ajax_display( response, tags, r => {
                    $box.trigger( `yq:populateAttach`, [ r, tags ] );
                });
            };
        }
        else {
            return response => {
                this.is_loading = false;
                let $box = jQuery( this.box );

                $box.trigger( `yq:populatePrepare`, [ response, tags ] );
                this.display( response, tags );
                $box.trigger( `yq:populateAttach`, [ response, tags ] );
            };
        }
    }

}

interface YqResultsList {
    suggest:   YqResultsListSuggest;
    meta_info: any[];
    page_hit:  boolean;
    submitted: boolean;
}

interface YqResultsListSuggest {
    node?: YqResultsStory[];
}

interface YqResultsStory {
    id?:          string;
    title?:       string;
    url?:         string;
    image?:       string;
    description?: string;
}

// Automatically detect and initialize YouneeqHandler instances.
jQuery( function() {
    if ( !jQuery( `html.yq-no-auto, body.yq-no-auto` ).length ) {
        YouneeqHandler.generate();
    }
});
