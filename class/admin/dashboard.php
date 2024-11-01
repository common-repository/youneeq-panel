<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * youneeq-panel: Yqr_Admin_Dashboard class
 *
 * Displays the Youneeq dashboard.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Admin_Dashboard extends Yqr_Admin_Base {

    /**
     * Creates a new dashboard object.
     *
     * @since 3.0.0
     */
    public function __construct() {
        parent::__construct( 'dashboard', esc_html_x( 'Youneeq Dashboard', 'Browser page title of Youneeq dashboard page', 'youneeq-panel' ), esc_html_x( 'Dashboard', 'Displayed name of Youneeq dashboard', 'youneeq-panel' ) );
    }

}

/* end */