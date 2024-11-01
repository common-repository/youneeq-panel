<?php
/**
 * youneeq-panel: Yqr_Main class
 *
 * Handles plugin initialization, creating hooks, and accessing plugin settings and defaults.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Main {

    /**
     * Plugin version number.
     *
     * @since 3.0.0
     * @var   string
     */
    const VERSION = '3.0.5';
    /**
     * Name of WordPress option stored in site database.
     *
     * @since 3.0.0
     * @var   string
     */
    const OPTION_NAME = 'yqr_settings';
    /**
     * Name of the base plugin directory.
     *
     * @since 3.0.5
     * @var   string
     */
    public static $base_dir = null;
    /**
     * Base plugin URL.
     *
     * @since 3.0.0
     * @var   string
     */
    public static $base_url = null;
    /**
     * Base plugin name.
     *
     * @since 3.0.0
     * @var   string
     */
    public static $base_name = null;
    /**
     * List of plugin-wide settings.
     *
     * @since 3.0.0
     * @var   array
     */
    private static $settings_list = null;
    /**
     * List of default setting values.
     *
     * @since 3.0.0
     * @var   array
     */
    private static $defaults_list = null;

    /**
     * Initializes the plugin.
     *
     * Registers all actions and filters (including the activation hook),
     * and imports all plugin PHP files.
     *
     * @since 3.0.5 Added $base_dir param.
     * @since 3.0.0
     *
     * @param string $base_dir  Name of the base plugin directory.
     * @param string $base_url  Base plugin URL.
     * @param string $base_name Base plugin name.
     * @param bool   $is_admin  Should be true if called on admin-facing pages.
     */
    public static function init( $base_dir, $base_url, $base_name, $is_admin = false ) {
        self::$base_dir = $base_dir;
        self::$base_url = $base_url;
        self::$base_name = $base_name;
        if ( !defined( 'PHP_INT_MIN' ) ) define( 'PHP_INT_MIN', ~PHP_INT_MAX );

        require_once 'feed.php';
        require_once 'hooks.php';
        require_once 'lib.php';
        require_once 'menu-post.php';
        require_once 'admin/base.php';
        require_once 'admin/update.php';
        require_once 'admin/settings.php';
        require_once 'admin/dashboard.php';
        require_once 'admin/support.php';
        require_once 'observe.php';
        require_once 'search.php';
        require_once 'widget/base.php';
        require_once 'widget/explorer.php';
        require_once 'widget/rec.php';
        require_once 'widget/search.php';

        // Prepare for action and filter initialization.
        add_action( 'plugins_loaded', function() use ( $is_admin ) {
            self::on_load( $is_admin );
        });
    }

    /**
     * Get plugin settings.
     *
     * Can get either a single setting (if its name is provided) or a list of all setting names.
     *
     * @since 3.0.0
     *
     * @return mixed              Single setting with provided name (or null if not found),
     *                            or list of all setting names.
     * @param  string|null $prop  Name of a setting to retrieve. If not included or set to null,
     *                            a list of all setting names will be retrieved.
     * @param  bool        $reset If true, the settings will be fetched from the database again.
     */
    public static function settings( $prop = null, $reset = false ) {
        if ( $reset || null == self::$settings_list ) {
            self::$settings_list = wp_parse_args( get_option( self::OPTION_NAME, self::$defaults_list ), self::$defaults_list );
        }

        return null == $prop ? array_keys( $settings_list ) : ( isset( self::$settings_list[ $prop ] ) ? self::$settings_list[ $prop ] : null );
    }

    /**
     * Get the default values for plugin settings.
     *
     * Can get either a single default (if its name is provided) or a list of all default names.
     *
     * @since 3.0.0
     *
     * @return mixed             Single setting default value with provided name (or null if not found),
     *                           or list of all default names.
     * @param  string|null $prop Name of a setting to retrieve default value for.
     *                           If not included or set to null, a list of all default names
     *                           will be retrieved.
     */
    public static function defaults( $prop = null ) {
        return null == $prop ? array_keys( $defaults_list ) : ( isset( self::$defaults_list[ $prop ] ) ? self::$defaults_list[ $prop ] : null );
    }

    /**
     * Activation hook.
     *
     * Should be fired only on plugin activation. Adds the search data feed and updates site rewrite rules.
     *
     * @since 3.0.0
     */
    public static function activate() {
        add_feed( 'yqdata', 'Yqr_Feed::display' );
        flush_rewrite_rules();
    }

    /**
     * Deactivation hook.
     *
     * Should be fired only on plugin deactivation. Updates site rewrite rules.
     *
     * @since 3.0.0
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Uninstall hook.
     *
     * Should be fired only on plugin uninstallation. Deletes plugin settings.
     *
     * @since 3.0.0
     */
    public static function uninstall() {
        delete_option( self::OPTION_NAME );
        delete_option( 'yq_settings' );
    }

    /**
     * Sets default setting values, creates menu objects and adds action and filter hooks.
     *
     * @since 3.0.5 No longer initializes menu page objects.
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    private static function on_load( $is_admin ) {
        self::set_defaults();
        Yqr_Hooks::init( $is_admin );
    }

    /**
     * Sets default setting values.
     *
     * @since 3.0.5 Added infinite scroll settings.
     * @since 3.0.4 Added Googly Analytics override function.
     * @since 3.0.1 Added Google Analytics tracking settings.
     * @since 3.0.0
     */
    private static function set_defaults() {
        self::$defaults_list = [
            'pic_observe'      => 'medium',
            'pic_default'      => 0,
            'filtered_cats'    => [],
            'domains'          => [],
            'js_file'          => 0,
            'search_page'      => 0,
            'use_jqueryui_css' => true,
            'use_explorer_css' => true,
            'scroll_enable'    => false,
            'scroll_offset'    => 300,
            'scroll_cooldown'  => 3000,
            'scroll_attach'    => '',
            'scroll_story'     => '',
            'ga_function'      => 'ga',
            'ga_tracker'       => '',
            'ga_override'      => ''
        ];
    }

}

/* end */