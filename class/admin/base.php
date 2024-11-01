<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * youneeq-panel: Yqr_Admin_Base class
 *
 * Base admin page class for the Youneeq Recommendations plugin.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5 Renamed to Yqr_Admin_Base
 * @since   3.0.0
 */
abstract class Yqr_Admin_Base {

    /**
     * List of slugs for all Youneeq admin pages.
     *
     * @since 3.1.0
     * @var   string[]
     */
    private static $pages_list = [];
    /**
     * Top level page slug.
     *
     * @since 3.1.0
     * @var   string
     */
    private static $main_page = '';

    /**
     * Title of the page.
     *
     * @since 3.0.0
     * @var   string
     */
    private $page_title;
    /**
     * Displayed name of the page.
     *
     * @since 3.0.0
     * @var   string
     */
    private $page_name;
    /**
     * Unique page slug.
     *
     * @since 3.0.0
     * @var   string
     */
    private $page_slug;
    /**
     * WordPress identifier for this page.
     *
     * @since 3.1.0
     * @var   string
     */
    private $page_id;
    /**
     * Generator string for the menu authentication nonce.
     *
     * @since 3.1.0
     * @var   string
     */
    private $nonce_base;

    /**
     * Base contructor - sets nonce value.
     *
     * @since 3.1.0 Adds new page to static page list.
     * @since 3.0.0
     */
    protected function __construct( $page_slug, $page_title, $page_name ) {
        if ( !isset( self::$pages_list[ $page_slug ] ) ) {
            $this->nonce_base = 'yqr' . $page_slug . get_current_user_id();
            $this->page_title = $page_title;
            $this->page_name = $page_name;
            $this->page_slug = $page_slug;
            $this->page_id = 'youneeq_page_' . $page_slug;

            self::$pages_list[ $page_slug ] = $this;
        }
        else {
            throw new Exception( 'youneeq-panel: Page with slug \'' . $page_slug . '\' has already been created.', -1 );
        }
    }

    /**
     * Gets the page with the given slug, or null if not found.
     *
     * @since 3.1.0
     *
     * @return Yqr_Admin_Base|null
     * @param  string              $page_slug Slug of the page to retrieve.
     */
    public static function get_page( $page_slug ) {
        return isset( self::$pages_list[ $page_slug ] ) ? self::$pages_list[ $page_slug ] : null;
    }

    /**
     * Registers the top level page for the Youneeq admin submenu.
     *
     * @since 3.0.0
     *
     * @param Yqr_Admin_Base $first_page Page to be used as the top level link.
     */
    public static function register_menu( $first_page ) {
        self::$main_page = $first_page->page_slug;
        $icon = file_get_contents( Yqr_Main::$base_dir . '/img/yq-icon.svg' );

        $first_page->page_id = add_menu_page( $first_page->page_title, esc_html_x( 'Youneeq', 'Displayed name of Youneeq admin menu', 'youneeq-panel' ), 'manage_options', 'youneeq-' . $first_page->page_slug, [ $first_page, 'display' ], ( $icon ? 'data:image/svg+xml;base64,' . base64_encode( $icon ) : 'none' ) );
        add_action( 'load-' . $first_page->get_screen_id(), [ $first_page, 'set_actions' ] );

        add_action( 'admin_head', 'Yqr_Admin_Base::set_menu_title' );
    }

    /**
     * Corrects the title of the first item in the Youneeq admin submenu.
     *
     * @since 3.1.0
     */
    public static function set_menu_title() {
        global $submenu;
        $first_page = self::$pages_list[ self::$main_page ];

        $slug = 'youneeq-' . $first_page->page_slug;

        if ( isset( $submenu[ $slug ] ) && isset( $submenu[ $slug ][0] ) ) {
            $submenu[ $slug ][0][0] = $first_page->page_name;
        }
    }

    /**
     * Registers the page in the Youneeq admin menu.
     *
     * @since  3.0.0
     */
    public function register() {
        if ( self::$main_page ) {
            $this->page_id = add_submenu_page( 'youneeq-' . self::$main_page, $this->page_title, $this->page_name, 'manage_options', 'youneeq-' . $this->page_slug, [ $this, 'display' ] );
            add_action( 'load-' . $this->get_screen_id(), [ $this, 'set_actions' ] );
        }
    }

    /**
     * Registers help tabs for this page.
     *
     * @since 3.0.0
     */
    public function get_admin_help() {
        $screen = get_current_screen();

        if ( !$screen ) {
            return;
        }
        else {
            $help_tabs = $this->get_help_tabs();

            if ( $help_tabs && count( $help_tabs ) ) {
                foreach( $help_tabs as $help_tab ) {
                    $screen->add_help_tab( $help_tab );
                }
            }
        }
    }

    /**
     * Gets the WordPress menu identifier for this page.
     *
     * @since 3.1.0 No longer abstract.
     * @since 3.0.0
     *
     * @return string
     */
    public function get_screen_id() {
        return $this->page_id;
    }

    /**
     * Generates a user authentication nonce for this page.
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_nonce() {
        return wp_create_nonce( $this->nonce_base );
    }

    /**
     * Verifies the given nonce for this page.
     *
     * @since 3.0.0
     *
     * @return bool          True if the nonce is valid.
     * @param  string $nonce Nonce to verify.
     */
    public function verify_nonce( $nonce ) {
        return wp_verify_nonce( $nonce, $this->nonce_base );
    }

    /**
     * Outputs the page contents.
     *
     * @since 3.1.0 No longer abstract; gets page contents from file.
     * @since 3.0.0
     */
    public function display() {
        // Must check that the user has the required capability
        if ( current_user_can( 'manage_options' ) ) {
            include Yqr_Main::$base_dir . '/view/' . $this->page_slug . '.php';
        }
        else {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'youneeq-panel' ) );
        }
    }

    /**
     * Displays settings menu header section.
     *
     * @since 3.0.0
     *
     * @param array $plugin_data Youneeq plugin metadata.
     */
    public function display_header() {
        ?>
        <div>
            <a class="yqr-header-link" href="http://www.youneeq.ca/">
                <picture>
                    <source srcset="<?= Yqr_Main::$base_url, '/img/yq-logo.svg' ?>" type="image/svg+xml" />
                    <img src="<?= Yqr_Main::$base_url, '/img/yq-logo.png' ?>" width="300" />
                </picture>
            </a>
            <span class="yq-plugin-version"><?= esc_html( Yqr_Main::VERSION ) ?></span>
        </div>
        <h1><?= $this->page_title ?></h1>
        <?php
    }

    /**
     * Registers WordPress actions for this page.
     *
     * @since 3.1.0
     */
    public function set_actions() {
        // Add help tabs to Youneeq options page.
        $this->get_admin_help();

        // Display admin notices.
        add_action( 'admin_notices', [ $this, 'display_notices' ] );
    }

    /**
     * Display admin notices.
     *
     * @since 3.1.0
     */
    public function display_notices() {
        $notices = $this->get_notices();

        if ( $notices && count( $notices ) ) {
            foreach ( $notices as $notice ) {
                $classes = 'notice' .
                    ( isset( $notice['type'] ) && $notice['type'] ? ' notice-' . $notice['type'] : '' ) .
                    ( isset( $notice['dismissible'] ) && $notice['dismissible'] ? ' is-dismissible' : '' )

                ?>
                <div class="<?= $classes ?>">
                    <p><?= isset( $notice['message'] ) ? esc_html( $notice['message'] ) : '' ?></p>
                </div>
                <?php
            }
        }
    }

    /**
     * Gets help tabs for the page.
     *
     * @since 3.0.0
     *
     * @return array Help tab contents.
     */
    protected function get_help_tabs() {
        return false;
    }

    /**
     * Gets admin notices for the page.
     *
     * @since 3.1.0
     *
     * @return array Notice contents.
     */
    protected function get_notices() {
        return false;
    }

}

/* end */