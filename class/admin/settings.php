<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Admin_Settings class
 *
 * Displays site-wide configuration options for the Youneeq Recommendations plugin.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5 Refactored and moved display code into separate file (view/settings.php).
 * @since   3.0.1 Added Google Analytics tracker settings.
 * @since   3.0.0
 */
class Yqr_Admin_Settings extends Yqr_Admin_Update {

    /**
     * Creates a new settings menu object.
     *
     * @since 3.0.0
     */
    public function __construct() {
        parent::__construct( 'settings', esc_html_x( 'Settings', 'Browser page title of Youneeq settings page', 'youneeq-panel' ), esc_html_x( 'Settings', 'Displayed name of Youneeq settings page', 'youneeq-panel' ) );
    }

    /**
     * Gets Settings help tabs.
     *
     * @since 3.0.4 Added Google Analytics override function explanation.
     * @since 3.0.1 Added Google Analytics settings explanation.
     * @since 3.0.0
     *
     * @return array Help tab contents.
     */
    protected function get_help_tabs() {
        return [[
            'id'      => 'yqr_settings_help',
            'title'   => __( 'Settings', 'youneeq-panel' ),
            'content' => '<p>'
                . __( 'These settings are applied across the entire site.', 'youneeq-panel' )
                . '</p><ul><li>'
                /* translators: %s is an option title (Image Size) */
                . sprintf( __( '<strong>%s</strong> &mdash; Determines the size of the image when generating post observe metadata. Affects the size of images for the current site\'s posts when being recommended on any network site.', 'youneeq-panel' ),
                    __( 'Image Size', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Ignored Categories) */
                . sprintf( __( '<strong>%s</strong> &mdash; Posts belonging to the selected categories will not be observed for recommendation.', 'youneeq-panel' ),
                    __( 'Ignored Categories', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Display Script File) */
                . sprintf( __( '<strong>%s</strong> &mdash; Designates a Javascript file to be loaded whenever a Youneeq Recommender widget is displayed.', 'youneeq-panel' ),
                    __( 'Display Script File', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Youneeq Search Page) */
                . sprintf( __( '<strong>%s</strong> &mdash; Designates a Page on the website to act as the results page for Youneeq Search.', 'youneeq-panel' ),
                    __( 'Youneeq Search Page', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Include jQuery UI Styles) */
                . sprintf( __( '<strong>%s</strong> &mdash; Indicates whether or not default CSS style rules should be applied to jQuery UI datepicker widgets, which are used by Youneeq Search forms for date entry in browsers that do not support the HTML5 date input type.', 'youneeq-panel' ),
                    __( 'Include jQuery UI Styles', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Include Explorer Styles) */
                . sprintf( __( '<strong>%s</strong> &mdash; Indicates whether or not default CSS style rules should be applied to Youneeq Explorer widgets.', 'youneeq-panel' ),
                    __( 'Include Explorer Styles', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Infinite Scroll Posts) */
                . sprintf( __( '<strong>%s</strong> &mdash; Enables infinite scrolling on post pages. Must also be configured through custom PHP code in order to work.', 'youneeq-panel' ),
                    __( 'Infinite Scroll Posts', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Scroll Offset) */
                . sprintf( __( '<strong>%s</strong> &mdash; Sets the offset value for the height trigger (in pixels). A higher number causes new posts to be loaded sooner.', 'youneeq-panel' ),
                    __( 'Scroll Offset', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Scroll Cooldown) */
                . sprintf( __( '<strong>%s</strong> &mdash; Time to wait between requesting new stories in infinite scroll (in milliseconds).', 'youneeq-panel' ),
                    __( 'Scroll Cooldown', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Scroll Attach Selector) */
                . sprintf( __( '<strong>%s</strong> &mdash; A CSS selector that fetches the element which contains the post container element.', 'youneeq-panel' ),
                    __( 'Scroll Attach Selector', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Scroll Post Selector) */
                . sprintf( __( '<strong>%s</strong> &mdash; A CSS selector that fetches the last post on the page.', 'youneeq-panel' ),
                    __( 'Scroll Post Selector', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Google Analytics Function) */
                . sprintf( __( '<strong>%s</strong> &mdash; Defines the name of the Google Analytics main function. By default, this is <code>ga</code>. Does nothing if Google Analytics is not running on the site.', 'youneeq-panel' ),
                    __( 'Google Analytics Function', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Google Analytics Tracker) */
                . sprintf( __( '<strong>%s</strong> &mdash; Defines the name of the Google Analytics tracker to be used. Does nothing if Google Analytics is not running on the site.', 'youneeq-panel' ),
                    __( 'Google Analytics Tracker', 'youneeq-panel' ) )
                . '</li><li>'
                /* translators: %s is an option title (Google Analytics Override) */
                . sprintf( __( '<strong>%s</strong> &mdash; Defines the name of a Javascript function which returns Google Analytics tracking instructions.', 'youneeq-panel' ),
                    __( 'Google Analytics Override', 'youneeq-panel' ) )
                . '</li></ul>'
        ], [
            'id'      => 'yqr_sites_help',
            'title'   => __( 'Network Sites', 'youneeq-panel' ),
            'content' => '<p>'
                . __( 'Other websites operated by your organization can be defined here to allow filtering cross-site recommendations. To add sites to the list, just enter a domain name (or list of domain names, separated by spaces) in the text box next to the Add Sites button, then press the button. Each site can be given a name for easy identification.', 'youneeq-panel' )
                . '</p><p>'
                . __( 'The Add This Site button will automatically register the website it is being used from and set its name.', 'youneeq-panel' )
                . '</p><p>'
                . __( 'To remove sites from the list, click the checkbox next to it and then press the "X" button at the top of the list.', 'youneeq-panel' )
                . '</p>'
        ]];
    }

    /**
     * Updates plugin setting values.
     *
     * @since 3.0.5
     */
    protected function set_update_vals() {
        $this->set_string( 'plugin_version' );
        $this->set_string( 'pic_observe' );
        $this->set_cats( 'filtered_cats' );
        $this->set_doms( 'domains' );
        $this->set_int( 'js_file' );
        $this->set_int( 'search_page' );
        $this->set_bool( 'use_jqueryui_css' );
        $this->set_bool( 'use_explorer_css' );
        $this->set_bool( 'scroll_enable' );
        $this->set_int( 'scroll_offset' );
        $this->set_int( 'scroll_cooldown' );
        $this->set_string( 'scroll_attach' );
        $this->set_string( 'scroll_story' );
        $this->set_string( 'ga_function' );
        $this->set_string( 'ga_tracker' );
        $this->set_string( 'ga_override' );
    }

    /**
     * Retrieves, sanitizes, and sets a selection of category IDs from POST.
     *
     * @since 3.0.5
     *
     * @param string $field Name of setting to update.
     */
    protected function set_cats( $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            $this->new[ $field ] = array_map( 'intval', $_POST[ $field ] );
        }
        else {
            $this->new[ $field ] = [];
        }
    }

    /**
     * Retrieves, sanitizes, and sets a list of domain key-value string pairs.
     *
     * @since 3.0.5
     *
     * @param string $field Name of setting to update.
     */
    protected function set_doms( $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            $doms = [];

            foreach ( $_POST[ $field ] as $dom ) {
                $url = sanitize_text_field( $dom[ 'url' ] );
                $name = sanitize_text_field( $dom[ 'name' ] );
                $doms[ $url ] = $name;
            }

            $this->new[ $field ] = $doms;
        }
        else {
            $this->new[ $field ] = [];
        }
    }

}

/* end */