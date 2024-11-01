/**
 * Loads a story through ajax and adds it to the story river.
 *
 * @since 3.0.5
 *
 * @param {YqResultsStory} story Data for the story to be displayed.
 * @param {YouneeqHandler} self  YouneeqHandler instance that is handling story caching.
 */
function yqr_load_story( story, self ) {
    let attach_point = jQuery( yq_scroll_params.attach_selector ),
        ga_obj = self.tracking.ga || null,
        ga_handler = self.tracking.ga_tracker ? `${self.tracking.ga_tracker}.send` : null,
        pageview = false;

    // Get GA object and handler from override function.
    if ( self.tracking.ga_override && typeof window[ self.tracking.ga_override ] != `undefined` ) {
        let ga_override = window[ self.tracking.ga_override ]( null, null, ga_obj, ga_handler );

        ga_obj = ga_override.obj || ga_obj;
        ga_handler = ga_override.handler || ga_handler;
    }

    jQuery.post( yq_scroll_params.ajax_url, {
        action: `yqr_ajax_post`,
        post_id: story.id
    }, function( response ) {
        if ( response.length > 16 ) {
            attach_point.append( response );

            if ( ga_obj && ga_handler && typeof window[ ga_obj ] != `undefined` ) {
                window[ ga_obj ]( ga_handler, {
                    hitType:       `event`,
                    eventCategory: `Articles`,
                    eventAction:   `Scrolled View`,
                    eventLabel:    story.url
                });

                if ( pageview ) {
                    window[ ga_obj ]( ga_handler, {
                        hitType:  `pageview`,
                        location: story.url
                    });
                }
            }
        }
    });
}

/**
 * Retrieves recommended stories and caches them for infinite scroll.
 *
 * Overrides the YouneeqHandler instance's display function.
 * Also initializes scroll handlers on first run.
 *
 * @since 3.0.5
 *
 * @param {YqResultsList} response Returned data from Youneeq request.
 * @param {string[]}      tags     List of tags specifying request context.
 */
function yqr_cache_stories( response, tags ) {
    if ( !this.use_scroll_handler ) {
        this.use_scroll_handler = true;
        this.story_cache = this.story_cache || [];

        let page = jQuery( window ),
            self = this;

        page.on( `scroll.yq`, function() {
            let last_story = jQuery( yq_scroll_params.story_selector );

            if ( self.scroll_ready && ( last_story.offset().top + last_story.height() -
                page.scrollTop() - page.height() ) < 500 ) {
                self.scroll_ready = false;
                page.trigger( `yq:scrollStory` );

                window.setTimeout( function( self ) {
                    self.scroll_ready = true;
                }, 3000, self );
            }
        }).on( `yq:scrollStory`, function() {
                if ( self.story_cache.length ) {
                    yqr_load_story( self.story_cache.pop(), self );

                    if ( !self.story_cache.length ) {
                        self.request( [ `scroll`, `cache` ] );
                    }
                }
                else {
                    self.request( [ `scroll`, `cache` ] );
                }
            });
        }

    if ( response && response.suggest && response.suggest.node ) {
        let stories = response.suggest.node;

        for ( let i = 0, max = stories.length; i < max; i++ ) {
            if ( stories[ i ].id ) {
                this.story_cache.push( stories[ i ] );
            }
        }
    }
}
