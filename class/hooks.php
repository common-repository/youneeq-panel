<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * youneeq-panel: Yqr_Hooks class
 *
 * Hooks handler for the Youneeq Recommendations plugin.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5 Refactored and added infinite scroll handlers.
 * @since   3.0.0
 */
class Yqr_Hooks {

    /**
     * Post options menu object.
     *
     * @since 3.0.0
     * @var   Yqr_Menu_Post
     */
    private static $menu_post;

    /**
     * Adds action and filter hooks, and assigns menu objects.
     *
     * @since 3.0.5 Now initializes post menu object.
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    public static function init( $is_admin = false ) {
        if ( $is_admin ) {
            self::$menu_post = new Yqr_Menu_Post();
        }
        else {
            self::$menu_post = null;
        }

        self::set_actions( $is_admin );
        self::set_filters( $is_admin );
    }

    /**
     * Registers plugin scripts and stylesheets.
     *
     * @since 3.0.5 Registers infinite scroll script.
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    public static function register_scripts( $is_admin ) {
        wp_register_style( 'jquery-ui-datepicker-yqstyle', Yqr_Main::$base_url . '/css/jquery-ui-date.css', [], Yqr_Main::VERSION );
        wp_register_script( 'modernizr-inputtypes', Yqr_Main::$base_url . '/js/modernizr.js', [], Yqr_Main::VERSION );
        wp_register_script( 'youneeq-date-fix', Yqr_Main::$base_url . '/js/date-fix.min.js', [], Yqr_Main::VERSION );
        wp_localize_script( 'youneeq-date-fix', 'yq_date_fix_l10n', Yqr_Lib::get_date_fix_l10n() );

        wp_register_style( 'youneeq-explorer-style', Yqr_Main::$base_url . '/css/explorer.min.css', [], Yqr_Main::VERSION );

        if ( $is_admin ) {
            wp_register_style( 'youneeq-admin-style', Yqr_Main::$base_url . '/css/admin.min.css', [], Yqr_Main::VERSION );
        }
        else {
            wp_register_script( 'youneeq-api', 'https://api.youneeq.ca/app/yqmin', [ 'jquery' ], Yqr_Main::VERSION );
            wp_register_script( 'detect-timezone', Yqr_Main::$base_url . '/js/detect-timezone.min.js', [], Yqr_Main::VERSION );

            wp_register_script( 'youneeq-lib', Yqr_Main::$base_url . '/js/youneeq-lib.min.js', [ 'jquery', 'youneeq-api', 'detect-timezone' ], Yqr_Main::VERSION );

            wp_register_script( 'youneeq-search-lib', Yqr_Main::$base_url . '/js/youneeq-search.min.js', [ 'jquery' ], Yqr_Main::VERSION );

            wp_register_script( 'youneeq-scroll', Yqr_Main::$base_url . '/js/youneeq-scroll.min.js', [ 'youneeq-lib' ], Yqr_Main::VERSION );
            wp_localize_script( 'youneeq-scroll', 'yq_scroll_params', [ 'ajax_url' => admin_url( 'admin-ajax.php' ), 'attach_selector' => Yqr_Main::settings( 'scroll_attach' ), 'story_selector' => Yqr_Main::settings( 'scroll_story' ) ] );
        }
    }

    /**
     * Enqueues admin scripts.
     *
     * @since 3.0.5
     *
     * @param string $page Current admin page.
     */
    public static function enqueue_admin_scripts( $page ) {
        switch ( $page ) {
            case 'widgets.php':
                wp_enqueue_script( 'jquery-ui-datepicker' );
                wp_enqueue_style(  'jquery-ui-datepicker-yqstyle' );
                wp_enqueue_script( 'modernizr-inputtypes' );
                wp_enqueue_script( 'youneeq-date-fix' );
            case 'post.php':
            case 'toplevel_page_youneeq-settings':
                wp_enqueue_media();
            case 'youneeq_page_youneeq-dashboard':
            case 'youneeq_page_youneeq-support':
                wp_enqueue_style( 'youneeq-admin-style' );
        }
    }

    /**
     * Enqueues standard site scripts.
     *
     * @since 3.0.5
     */
    public static function enqueue_scripts() {
        wp_enqueue_script( 'youneeq-lib' );
        wp_add_inline_script( 'youneeq-lib', Yqr_Observe::get_observe() );
    }

    /**
     * Adds action hooks.
     *
     * @since 3.0.5 Adds infinite scroll ajax handling.
     *              No longer adds admin notice hook for admin settings page.
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    private static function set_actions( $is_admin ) {
        // Add search data feeds and shortcodes.
        add_action( 'init', function() {
            add_feed( 'yqdata', 'Yqr_Feed::display' );
            Yqr_Search::add_shortcodes();
        });

        // Registers widgets with WordPress core.
        add_action( 'widgets_init', function() {
            register_widget( 'Yqr_Widget_Rec' );
            register_widget( 'Yqr_Widget_Explorer' );
            register_widget( 'Yqr_Widget_Search' );
        });

        // Registers plugin javascript and stylesheets.
        add_action( 'wp_loaded', function() use ( $is_admin ) {
            self::register_scripts( $is_admin );
        });

        // Registers infinite scroll ajax handler for posts.
        add_action( 'wp_ajax_yqr_ajax_post', 'Yqr_Lib::ajax_post' );
        add_action( 'wp_ajax_nopriv_yqr_ajax_post', 'Yqr_Lib::ajax_post' );

        if ( $is_admin ) {
            // Add post options meta box.
            add_action( 'add_meta_boxes', [ self::$menu_post, 'register' ] );

            // Save post options on save.
            add_action( 'save_post', [ self::$menu_post, 'update' ] );

            // Add admin settings menu.
            add_action( 'admin_menu', function() {
                Yqr_Admin_Base::register_menu( new Yqr_Admin_Settings() );
                ( new Yqr_Admin_Dashboard() )->register();
                ( new Yqr_Admin_Support() )->register();
            });

            // Add help tabs to widgets page.
            add_action( 'load-widgets.php', 'Yqr_Widget_Rec::admin_help' );
            add_action( 'load-widgets.php', 'Yqr_Widget_Search::admin_help' );

            // Adds links to settings page on the plugins overview page.
            add_filter( 'plugin_action_links_' . Yqr_Main::$base_name , function( $links, $file, $plugin_data ) {
               if ( Yqr_Main::$base_name == $file && current_user_can( 'manage_options' ) ) {
                    array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=youneeq-settings' ) ) . '">'. _x( 'Settings', 'displayed name of Youneeq settings link in plugins menu', 'youneeq-panel' ) . '</a>' );
                }
                return $links;
            }, 10, 3 );

            // Add admin section javascript and stylesheets.
            add_action( 'admin_enqueue_scripts', 'Yqr_Hooks::enqueue_admin_scripts' );
        }
        else {
            // Add javascript and stylesheets.
            add_action( 'wp_enqueue_scripts', 'Yqr_Hooks::enqueue_scripts' );

            // Add infinite scroll ajax handler.
            add_action( 'wp_footer', 'Yqr_Lib::ajax_handler' );
        }
    }

    /**
     * Adds filter hooks.
     *
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    private static function set_filters( $is_admin ) {
        if ( !$is_admin ) {
            // Process recommender widget suggest params before creating element attributes on page.
            Yqr_Widget_Rec::set_filters( $is_admin );

            // Process search widget params before creating element attributes on page.
            Yqr_Widget_Search::set_filters( $is_admin );

            // Process post observe data before creating JS function on page.
            Yqr_Observe::set_filters( $is_admin );
        }
    }

}

/* end */