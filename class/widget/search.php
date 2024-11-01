<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Widget_Search class
 *
 * Displays a Youneeq search form on a page, which can be configured on the widgets page.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 * @see     WP_Widget
 */
class Yqr_Widget_Search extends Yqr_Widget_Base {

    /**
     * Creates a new Youneeq search widget object.
     *
     * @since 3.0.0
     */
    public function __construct() {
        parent::__construct( 'youneeq_search', _x( 'Youneeq Search', 'Title of Youneeq search widget', 'youneeq-panel' ), [
            'description' => __( 'A simple Youneeq search form.', 'youneeq-panel' )
        ], [] );
    }

    /**
     * Updates settings for this widget instance.
     *
     * @since 3.0.0
     *
     * @return array      Updated settings.
     * @param  array $new New settings from the configuration form.
     * @param  array $old Old settings.
     */
    public function update( $new, $old ) {
        $this->new_vals = &$new;

        $this->set_string( 'title' );

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
        self::display(
            /**
             * Processes HTML attributes array for this widget.
             *
             * @since 3.0.0
             *
             * @param string[] $attrs    Key-value pairs of HTML attributes.
             * @param array    $instance Settings for this widget instance.
             * @param array    $args     Display arguments for this widget instance.
             */
            apply_filters( 'yqr_widget_search', $attrs, $instance, $args )
        );
        echo $args[ 'after_widget' ];
    }

    /**
     * Outputs a basic search form.
     *
     * @since 3.0.0
     *
     * @param string[] $attrs Associative array of HTML attributes to output.
     */
    public static function display( $attrs = null ) {
        echo Yqr_Search::display_form( $attrs );
    }

    /**
     * Adds filter hooks.
     *
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    public static function set_filters( $is_admin ) {
        $filter = 'yqr_widget_search';

        add_filter( $filter, 'Yqr_Widget_Base::add_js_file',        10 );
        add_filter( $filter, 'Yqr_Widget_Search::add_form_action',  10 );
        add_filter( $filter, 'Yqr_Widget_Search::add_form_classes', 10 );
    }

    /**
     * Sets form action (target URL) in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     */
    public static function add_form_action( $attrs ) {
        $page_id = Yqr_Main::settings( 'search_page' );
        $page_link = $page_id ? get_page_link( $page_id ) : null;

        if ( $page_link ) {
            $attrs[ 'action' ] = $page_link;
        }

        return $attrs;
    }

    /**
     * Adds default classes to the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     */
    public static function add_form_classes( $attrs ) {
        if ( !isset( $attrs[ 'class' ] ) ) {
            $attrs[ 'class' ] = 'search-form';
        }
        else {
            $attrs[ 'class' ] .= ' search-form';
        }

        return $attrs;
    }

    /**
     * Generates widget help tabs for the widgets page.
     *
     * @since 3.0.0
     */
    public static function admin_help() {
        $screen = get_current_screen();

        if ( !$screen ) {
            return;
        }

        $screen->add_help_tab([
            'id'      => 'yqr_widget_search_help',
            'title'   => _x( 'Youneeq Search', 'Label for help tab on widgets page', 'youneeq-panel' ),
            'content' => '<p>'
                . __( 'The Youneeq Search widget displays a simple Youneeq Search form. For more information on implementing Youneeq Search on your website, please see the <a href="https://wordpress.org/plugins/youneeq-panel/#faq" target="_blank">FAQ section</a> on the Youneeq Recommendations plugin page.', 'youneeq-panel' )
                . '</p>'
        ]);
    }

    /**
     * Returns default arguments array.
     *
     * @since 3.0.0
     *
     * @return array
     */
    protected function get_default_args() {
        return [
            'title'       => __( 'Search', 'youneeq-panel' )
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
        echo '<div class="yqr-widget-content"><p>',
            $this->get_menu_field( 'title', _x( 'Title', 'Widget title', 'youneeq-panel' ), $args[ 'title' ], 'text' ),
            '</p></div>';
    }

}

/* end */