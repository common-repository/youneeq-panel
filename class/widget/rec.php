<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * youneeq-panel: Yqr_Widget_Rec class
 *
 * Displays a Youneeq element on a page, which can be configured on the widgets page.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.6 Added priority option.
 * @since   3.0.5 Refactored manual display and fixed display bugs.
 * @since   3.0.1 Added Google Analytics custom tracker handling.
 * @since   3.0.0
 * @see     WP_Widget
 */
class Yqr_Widget_Rec extends Yqr_Widget_Base {

    /**
     * Minimum recommended post count.
     *
     * @since 3.0.0
     * @var   int
     */
    const COUNT_MIN = 0;
    /**
     * Maximum recommended post count.
     *
     * @since 3.0.0
     * @var   int
     */
    const COUNT_MAX = 16;
    /**
     * Minimum post age (in days).
     *
     * @since 3.0.0
     * @var   int
     */
    const AGE_MIN = 1;
    /**
     * Maximum post age (in days). Equal to about 20 years.
     *
     * @since 3.0.0
     * @var   int
     */
    const AGE_MAX = 7305;
    /**
     * Minimum post priority.
     *
     * @since 3.0.6
     * @var   int
     */
    const PRIO_MIN = -10;
    /**
     * Maximum post priority.
     *
     * @since 3.0.6
     * @var   int
     */
    const PRIO_MAX = 10;

    /**
     * Creates a new Youneeq recommendation widget object.
     *
     * @since 3.0.0
     */
    public function __construct() {
        parent::__construct( 'youneeq_recommender', _x( 'Youneeq Recommender', 'Title of Youneeq widget', 'youneeq-panel' ), [
            'description' => __( 'Fetches Youneeq recommendations.', 'youneeq-panel' )
        ], [] );
    }

    /**
     * Updates settings for this widget instance.
     *
     * @since 3.0.6 Added priority option.
     * @since 3.0.3 Added infinite scroll option.
     * @since 3.0.0
     *
     * @return array      Updated settings.
     * @param  array $new New settings from the configuration form.
     * @param  array $old Old settings.
     */
    public function update( $new, $old ) {
        $this->new_vals = &$new;

        $this->set_string( 'title' );
        $this->set_int( 'count', 0, self::COUNT_MIN, self::COUNT_MAX );
        $this->set_string( 'display_function' );
        $this->set_bool( 'section_filter' );
        $this->set_cats( 'categories' );
        $this->set_bool( 'cross_site' );
        $this->set_doms( 'domains' );
        $this->set_dates( 'date' );
        $this->set_int( 'age', 14, self::AGE_MIN, self::AGE_MAX );
        $this->set_string( 'user_history' );
        $this->set_bool( 'paging_enabled' );
        $this->set_bool( 'infinite_scroll' );
        $this->set_bool( 'strict_categories' );
        $this->set_int( 'priority', 0, self::PRIO_MIN, self::PRIO_MAX );

        return $this->new_vals;
    }

    /**
     * Outputs this widget.
     *
     * @since 3.0.0
     *
     * @param array $args     Display arguments for this widget instance.
     * @param array $instance Settings for this widget instance.
     */
    public function widget( $args, $instance ) {
        $attrs = [];

        if ( isset( $instance[ 'title' ] ) ) {
            $instance[ 'title' ] = apply_filters( 'the_title', $instance[ 'title' ] );
        }
        else {
            $instance['title'] = '';
        }

        echo $args[ 'before_widget' ];
        if ( !empty( $instance[ 'title' ] ) ) {
            echo $args[ 'before_title' ], esc_html( $instance[ 'title' ] ), $args[ 'after_title' ];
        }
        self::output(
            /**
             * Processes HTML attributes array for this widget.
             *
             * @since 3.0.0
             *
             * @param string[] $attrs    Key-value pairs of HTML attributes.
             * @param array    $instance Settings for this widget instance.
             * @param array    $args     Display arguments for this widget instance.
             */
            apply_filters( 'yqr_widget_rec', $attrs, $instance, $args )
        );
        echo $args[ 'after_widget' ];
    }

    /**
     * Outputs a generic recommendation widget.
     *
     * @since 3.0.5
     *
     * @param array    $args  Widget instance settings.
     * @param string[] $attrs Associative array of HTML attributes to output.
     */
    public static function display( $args = [], $attrs = null ) {
        if ( !isset( $args[ 'count' ] ) ) {
            $args[ 'count' ] = 5;
        }

        self::output(
            /**
             * Processes generic HTML attributes array for this widget type.
             *
             * @since 3.0.5
             *
             * @param string[] $attrs Key-value pairs of HTML attributes.
             * @param array    $args  Generic widget instance settings.
             */
            apply_filters( 'yqr_widget_rec', $attrs, $args, [] )
        );
    }

    /**
     * Outputs a recommendation element.
     *
     * @since 3.0.5
     *
     * @param string[] $attrs Associative array of HTML attributes to output.
     */
    private static function output( $attrs = null ) {
        echo '<youneeq-section', Yqr_Lib::build_attr_string( $attrs ), '></youneeq-section>';
    }

    /**
     * Adds filter hooks.
     *
     * @since 3.0.6 Added priority option filter.
     * @since 3.0.3 Added handler features filter.
     * @since 3.0.1 Added Google Analytics tracking filter.
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    public static function set_filters( $is_admin ) {
        $filter = 'yqr_widget_rec';

        add_filter( $filter, 'Yqr_Widget_Rec::add_observe_data',      10 );
        add_filter( $filter, 'Yqr_Widget_Base::add_display_function', 10, 2 );
        add_filter( $filter, 'Yqr_Widget_Base::add_js_file',          10 );
        add_filter( $filter, 'Yqr_Widget_Base::add_count',            10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_categories',        10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_domains',           10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_dates',             10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_options',           10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_features',          10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_panel_type',        10 );
        add_filter( $filter, 'Yqr_Widget_Rec::add_explorer',          10 );
        add_filter( $filter, 'Yqr_Widget_Base::add_priority',         10, 2 );
        add_filter( $filter, 'Yqr_Widget_Rec::set_ga_tracking',       10 );
    }

    /**
     * Sets observe attributes in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     */
    public static function add_observe_data( $attrs ) {
        if ( Yqr_Observe::can_observe() ) {
            $attrs[ 'data-yq-observe-function' ] = 'yqr_observe';
        }

        return $attrs;
    }

    /**
     * Sets category filter list in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_categories( $attrs, $instance ) {
        if ( isset( $instance[ 'section_filter' ] ) && $instance[ 'section_filter' ] && is_category() ) {
            $attrs[ 'data-yq-suggest-categories' ] = esc_js( get_queried_object()->name );
        }
        elseif ( isset( $instance[ 'categories' ] ) && $instance[ 'categories' ] ) {
            $attrs[ 'data-yq-suggest-categories' ] = implode( '|', array_filter( array_map( function( $id ) {
                $cat = get_category( $id );
                return $cat ? esc_js( str_replace( '|', '', $cat->name ) ) : '';
            }, $instance[ 'categories' ] ) ) );
        }

        return $attrs;
    }

    /**
     * Sets domain filter list in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_domains( $attrs, $instance ) {
        if ( isset( $instance[ 'cross_site' ] ) && $instance[ 'cross_site' ] ) {
            if ( isset( $instance[ 'domains' ] ) && $instance[ 'domains' ] ) {
                $attrs[ 'data-yq-suggest-domains' ] = implode( '|', array_map( 'esc_attr', $instance[ 'domains' ] ) );
            }
            else {
                $attrs[ 'data-yq-suggest-domains' ] = 'true';
            }
        }

        return $attrs;
    }

    /**
     * Sets start and end dates in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_dates( $attrs, $instance ) {
        $date_start = null;
        $date_end = null;
        $timezone = new DateTimeZone( get_option( 'timezone_string', 'UTC' ) );

        if ( isset( $instance[ 'date_start' ] ) && $instance[ 'date_start'] ) {
            $date_start = new DateTime( $instance[ 'date_start' ], $timezone );
        }
        if ( isset( $instance[ 'age' ] ) && $instance[ 'age' ] > 0 && $instance[ 'age' ] != 14 ) {
            $age_start = ( new DateTime( null, $timezone ) )->sub( new DateInterval( 'P' . ( $instance[ 'age' ] - 1 ) . 'D' ) )->setTime( 0, 0, 0 );
            $date_start = $date_start == null ? $age_start : ( $date_start->getTimestamp() < $age_start->getTimestamp() ? $age_start : $date_start );
        }
        if ( isset( $instance[ 'date_end' ] ) && $instance[ 'date_end'] ) {
            $date_end = new DateTime( $instance[ 'date_end' ], $timezone );
            $date_end = $date_start != null && $date_start->getTimestamp() > $date_end->getTimestamp() ? null : $date_end;
        }

        if ( $date_start ) {
            $attrs[ 'data-yq-suggest-date-start' ] = $date_start->format( 'c' );
        }
        if ( $date_end ) {
            $attrs[ 'data-yq-suggest-date-end' ] = $date_end->format( 'c' );
        }

        return $attrs;
    }

    /**
     * Sets suggest options in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_options( $attrs, $instance ) {
        $options = [];

        if ( isset( $instance[ 'user_history' ] ) ) {
            if ( 'disable' == $instance[ 'user_history' ] ) {
                $options[] = 'disable_history';
            }
            elseif ( 'show' == $instance[ 'user_history' ] ) {
                $options[] = 'show_history';
            }
        }
        if ( isset( $instance[ 'paging_enabled' ] ) && $instance[ 'paging_enabled' ] ) {
            $options[] = 'paging_enabled';
        }
        if ( isset( $instance[ 'strict_categories' ] ) && $instance[ 'strict_categories' ] ) {
            $options[] = 'strict_categories';
        }

        if ( $options ) {
            $attrs[ 'data-yq-suggest-options' ] = implode( ',', $options );
        }

        return $attrs;
    }

    /**
     * Sets handler features in the element attribute array.
     *
     * @since 3.0.3
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_features( $attrs, $instance ) {
        $features = [];

        if ( isset( $instance['infinite_scroll'] ) && $instance['infinite_scroll'] ) {
            $features[] = 'infinite-scroll';
            $attrs['data-yq-scroll-offset'] = esc_attr( Yqr_Main::settings( 'scroll_offset' ) );
            $attrs['data-yq-scroll-cooldown'] = esc_attr( Yqr_Main::settings( 'scroll_cooldown' ) );
        }

        if ( $features ) {
            $attrs['data-yq-features'] = implode( ' ', $features );
        }

        return $attrs;
    }

    /**
     * Sets panel type in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     */
    public static function add_panel_type( $attrs ) {
        if ( is_single() ) {
            $attrs[ 'data-yq-suggest-panel-type' ] = 'article_panel';
        }
        elseif ( is_category() ) {
            $attrs[ 'data-yq-suggest-panel-type' ] = 'category_panel';
        }
        elseif ( is_home() ) {
            $attrs[ 'data-yq-suggest-panel-type' ] = 'home_panel';
        }

        return $attrs;
    }

    /**
     * Sets Youneeq Explorer as the panel's suggest function.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     */
    public static function add_explorer( $attrs ) {
        $attrs[ 'data-yq-suggest-function' ] = 'yq_explorer_options';

        return $attrs;
    }

    /**
     * Sets Google Analytics function and tracker.
     *
     * @since 3.0.4 Added override function.
     * @since 3.0.1
     *
     * @return string[]
     * @param  string[] $attrs Array of HTML attributes.
     */
    public static function set_ga_tracking( $attrs ) {
        $ga = Yqr_Main::settings( 'ga_function' );
        $tracker = Yqr_Main::settings( 'ga_tracker' );
        $override = Yqr_Main::settings( 'ga_override' );

        if ( strlen( $ga ) ) {
            $attrs['data-yq-ga-function'] = esc_js( $ga );
        }

        if ( strlen( $tracker ) ) {
            $attrs['data-yq-ga-tracker'] = esc_js( $tracker );
        }

        if ( strlen( $override ) ) {
            $attrs['data-yq-ga-override-function'] = esc_js( $override );
        }

        return $attrs;
    }

    /**
     * Generates widget help tabs for the widgets page.
     *
     * @since 3.0.6 Added priority option explanation.
     * @since 3.0.3 Added infinite scroll option explanation.
     * @since 3.0.0
     */
    public static function admin_help() {
        $screen = get_current_screen();

        if ( !$screen ) {
            return;
        }

        $screen->add_help_tab([
            'id'      => 'yqr_widget_help',
            'title'   => _x( 'Youneeq Recommender', 'Label for help tab on widgets page', 'youneeq-panel' ),
            'content' => '<p>'
                . __( "The Youneeq Recommender widget displays posts recommended by Youneeq's service. The widget can be configured using the following options:", 'youneeq-panel' )
                . '</p><ul><li>'
                /* translators: %s is an option title (Title) */
                . sprintf( __( '<strong>%s</strong> &mdash; Will be displayed above the widget.', 'youneeq-panel' ),
                    _x( 'Title', 'Widget title', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Post Count) */
                . sprintf( __( '<strong>%s</strong> &mdash; Number of posts to recommend. If set to 0, recommendations will be disabled.', 'youneeq-panel' ),
                    __( 'Post Count', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Filter on Category Pages) */
                . sprintf( __( '<strong>%s</strong> &mdash; Automatically filter recommended posts on category pages. Overrides the Category Filter option.', 'youneeq-panel' ),
                    __( 'Filter on Category Pages', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Strict Category Filter) */
                . sprintf( __( '<strong>%s</strong> &mdash; If selected, category filter settings will exclude any posts outside the given categories. If there are too few posts in the given categories, the recommendation engine may recommend fewer posts than requested.', 'youneeq-panel' ),
                    __( 'Strict Category Filter', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Category Filter) */
                . sprintf( __( '<strong>%s</strong> &mdash; Posts from selected categories will be prioritized by the recommendation engine.', 'youneeq-panel' ),
                    __( 'Category Filter', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Cross-Site Recommendations) */
                . sprintf( __( '<strong>%s</strong> &mdash; If selected, posts may be recommended from other Youneeq-enabled sites in your network.', 'youneeq-panel' ),
                    __( 'Cross-Site Recommendations', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Site Filter) */
                . sprintf( __( '<strong>%s</strong> &mdash; Restricts cross-site recommendations to the selected sites. Sites can be registered on the Youneeq plugin options page.', 'youneeq-panel' ),
                    __( 'Site Filter', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Maximum Age) */
                . sprintf( __( '<strong>%s</strong> &mdash; Maximum allowed age for recommended posts, in days. May override the Date Filter option.', 'youneeq-panel' ),
                    __( 'Maximum Age', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Date Filter) */
                . sprintf( __( '<strong>%s</strong> &mdash; If set, only posts published within the set start and end date will be recommended.', 'youneeq-panel' ),
                    __( 'Date Filter', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Include/Exclude History) */
                . sprintf( __( '<strong>%s</strong> &mdash; Selects behavior for filtering posts previously viewed by each user. By default, users will not see posts they have viewed within the last 10 days.', 'youneeq-panel' ),
                    __( 'Include/Exclude History', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Display Function) */
                . sprintf( __( '<strong>%s</strong> &mdash; Name of a Javascript function on the page that overrides the default display function and allows customization of the recommended post layout. The Display Script File option on the Youneeq plugin options page may be used to include this function.', 'youneeq-panel' ),
                    /* translators: Refers to a function for displaying post data, not "display a function." */
                    __( 'Display Function', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Enable Paging) */
                . sprintf( __( '<strong>%s</strong> &mdash; Allows multiple recommendation sets to be returned for the same page without duplicating recommended posts between them.', 'youneeq-panel' ),
                    __( 'Enable Paging', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Infinite Scroll) */
                . sprintf( __( '<strong>%s</strong> &mdash; Allows additional recommendations to be fetched when the user scrolls near the bottom of the page.', 'youneeq-panel' ),
                    __( 'Infinite Scroll', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Widget Priority) */
                . sprintf( __( '<strong>%s</strong> &mdash; Determines the order in which the Youneeq handler object executes. Lower values execute first.', 'youneeq-panel' ),
                    __( 'Widget Priority', 'youneeq-panel' ) )
                . '</li></ul>'
        ]);
    }

    /**
     * Returns default arguments array.
     *
     * @since 3.0.6 Added priority option.
     * @since 3.0.3 Added infinite scroll option.
     * @since 3.0.0
     *
     * @return array
     */
    protected function get_default_args() {
        return [
            /* translators: Default widget title. */
            'title'             => __( 'Recommended for You', 'youneeq-panel' ),
            'count'             => 5,
            'display_function'  => '',
            'section_filter'    => false,
            'categories'        => [],
            'cross_site'        => false,
            'domains'           => [],
            'date_start'        => '',
            'date_end'          => '',
            'age'               => 14,
            'user_history'      => 'default',
            'paging_enabled'    => false,
            'infinite_scroll'   => false,
            'strict_categories' => false,
            'priority'          => 0,
        ];
    }

    /**
     * Displays the widget configuration form.
     *
     * @since 3.0.0
     *
     * @param array $args Widget settings.
     */
    protected function display_form( $args ) {
        $this->display_script( $args );
        $this->display_fields( $args );
    }

    /**
     * Displays edit form script.
     *
     * @since 3.0.0
     *
     * @param array $args Widget settings.
     */
    private function display_script( $args ) {
        $id = $this->get_field_id( 'script' );
        $start_day = get_option( 'start_of_week', 0 );

        ?>
        <script id="<?php echo esc_attr( $id ); ?>">
            jQuery( function( $ ) {
                $( '#<?php echo esc_js( $id ); ?>' ).parents( '.widget' ).each( function( i, self ) {
                    yq_date_fix( '#<?php echo esc_js( $this->get_field_id( 'date_start') ) . ', #' . esc_js( $this->get_field_id( 'date_end') ); ?>', self );
                });
            });
        </script>
        <?php
    }

    /**
     * Displays edit form fields.
     *
     * @since 3.0.6 Added priority option.
     * @since 3.0.3 Added infinite scroll option.
     * @since 3.0.0
     *
     * @param array $args Widget settings.
     */
    private function display_fields( $args ) {
        echo '<div class="yqr-widget-content"><p>',
            $this->get_menu_field( 'title', _x( 'Title', 'Widget title', 'youneeq-panel' ), $args[ 'title' ], 'text' ),
            '</p><p>',
            $this->get_menu_field( 'count', __( 'Post Count', 'youneeq-panel' ), $args[ 'count' ], 'number', [ 'min' => self::COUNT_MIN, 'max' => self::COUNT_MAX ] ),
            '</p><p>',
            $this->get_menu_field( 'section_filter', __( 'Filter on Category Pages', 'youneeq-panel' ), $args[ 'section_filter' ] ),
            '<br />',
            $this->get_menu_field( 'strict_categories', __( 'Strict Category Filter', 'youneeq-panel' ), $args[ 'strict_categories' ] ),
            '</p>',
            $this->get_category_menu( 'categories', __( 'Category Filter', 'youneeq-panel' ), $args[ 'categories' ] ),
            '<p>',
            $this->get_menu_field( 'cross_site', __( 'Cross-Site Recommendations', 'youneeq-panel' ), $args[ 'cross_site' ] ),
            '</p>',
            $this->get_domain_menu( 'domains', __( 'Site Filter', 'youneeq-panel' ), $args[ 'domains' ] ),
            '<p>',
            $this->get_menu_field( 'age', __( 'Maximum Age', 'youneeq-panel' ), $args[ 'age' ], 'number', [ 'min' => self::AGE_MIN, 'max' => self::AGE_MAX ] ),
            '</p><p>',
            $this->get_date_menu( 'date', __( 'Date Filter', 'youneeq-panel' ), $args[ 'date_start' ], $args[ 'date_end' ] ),
            '</p><p>',
            $this->get_menu_field( 'user_history', __( 'Include User History', 'youneeq-panel' ), $args[ 'user_history' ], 'radio', 'show' ),
            '<br />',
            $this->get_menu_field( 'user_history', __( 'Exclude User History (10 days)', 'youneeq-panel' ), $args[ 'user_history' ], 'radio', 'default' ),
            '<br />',
            $this->get_menu_field( 'user_history', __( 'Exclude User History (30 days)', 'youneeq-panel' ), $args[ 'user_history' ], 'radio', 'disable' ),
            '</p><p>',
            /* translators: Refers to a function for displaying post data, not "display a function." */
            $this->get_menu_field( 'display_function', __( 'Display Function', 'youneeq-panel' ), $args[ 'display_function' ], 'text' ),
            '</p><p>',
            $this->get_menu_field( 'paging_enabled', __( 'Enable Paging', 'youneeq-panel' ), $args[ 'paging_enabled' ] ),
            '<br />',
            $this->get_menu_field( 'infinite_scroll', __( 'Infinite Scroll', 'youneeq-panel' ), $args[ 'infinite_scroll' ] ),
            '</p><p>',
            $this->get_menu_field( 'priority', __( 'Widget Priority', 'youneeq-panel' ), $args[ 'priority' ], 'number', [ 'min' => self::PRIO_MIN, 'max' => self::PRIO_MAX ] ),
            '</p></div>';
    }

}

/* end */