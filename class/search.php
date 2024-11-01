<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Search class
 *
 * Handles display of Youneeq Search elements.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Search {

    private static $form_count = 0;

    /**
     * Registers search shortcodes.
     *
     * @since 3.0.0
     */
    public static function add_shortcodes() {
        add_shortcode( 'yqsearchform', 'Yqr_Search::shortcode' );
        add_shortcode( 'yqsearchresults', 'Yqr_Search::shortcode' );
    }

    /**
     * Processes search shortcodes.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  array  $raw_args Parameters provided to the shortcode.
     * @param  string $content  Inner content of the shortcode, if used in enclosing form.
     * @param  string $tag      The shortcode tag.
     */
    public static function shortcode( $raw_args = [], $content = '', $tag = 'yqsearchform' ) {
        $is_form = $tag == 'yqsearchform' || $tag == 'yqsearch';
        $attrs = [];

        // Set default args.
        $args = shortcode_atts( $is_form ? [
            'id'           => 'yqsearchform',
            'class'        => 'search-form',
            'role'         => 'search',
            'method'       => 'get',
            'action'       => $_SERVER[ 'REQUEST_URI' ],
            'autocomplete' => 'on',
            'for'          => 'yqsearchresults',
            'headselector' => 'h1',
            'headtext'     => __( 'Search results for "%query%"', 'youneeq-panel' ),
            'advanced'     => false,
            'searchtype'   => 'article'
        ] : [
            'id'     => 'yqsearchresults',
            'formid' => 'yqsearchform'
        ], $raw_args, $tag );

        // Process args into HTML attributes.
        foreach ( $args as $key => $val ) {
            switch ( $key ) {
                case 'formid':
                    $attrs[ 'data-yq-search-function' ] = 'yqsearchform_' . esc_js( $val );
                    $attrs[ 'data-yq-search-form-id' ] = esc_attr( $val );
                    break;
                case 'headselector':
                case 'headtext':
                case 'for':
                case 'advanced':
                case 'searchtype':
                    break;
                default:
                    $attrs[ $key ] = $val;
            }
        }

        if ( $is_form ) {
            return self::display_form( $attrs, $args, $content );
        }
        else {
            return self::display_search( $attrs, $args, $content );
        }
    }

    /**
     * Displays a search form.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  string[] $attrs   HTML element attributes.
     * @param  array    $args    Parameters provided to the shortcode.
     * @param  string   $content Inner content of the shortcode, if used in enclosing form.
     */
    public static function display_form( $attrs = null, $args = [], $content = '' ) {
        self::enqueue_ui( Yqr_Main::settings( 'use_jqueryui_css' ) );

        $attr_string = Yqr_Lib::build_attr_string( $attrs );

        $id = isset( $args[ 'id' ] ) && $args[ 'id' ] ? $args[ 'id' ] : '';
        $val = isset( $_REQUEST[ 'q' ] ) ? 'value="' . $_REQUEST[ esc_attr( 'q' ) ] . '" ' : '';
        $advanced = isset( $args[ 'advanced' ] ) && $args[ 'advanced' ] && $args[ 'advanced' ] != 'false';
        $form_count = self::$form_count++;

        $reader_text = __( 'Search for:', 'youneeq-panel' );
        $placeholder = esc_attr__( 'Search &hellip;', 'youneeq-panel' );
        $search_but = _x( 'Search', 'Button label', 'youneeq-panel' );

        $out = $content ?: <<<FORM
<label for="$id-query-$form_count">
    <span class="screen-reader-text">
        $reader_text
    </span>
</label>
<input id="$id-query-$form_count" class="search-field" placeholder="$placeholder" name="q" type="search" $val/>
<button class="search-submit" type="submit">
    $search_but
</button>
FORM;

        if ( !$content && $advanced ) {
            $search_type = isset( $args[ 'searchtype' ] ) && $args[ 'searchtype' ] ?
                $args[ 'searchtype' ] : 'article';

            $out = '<div class="search-option"><input type="hidden" name="search_type" value="' .
				esc_attr( $search_type ) . '" />' . PHP_EOL . '<a id="' . $id .
                '-type-post" class="search-by' . ( $search_type == 'article' ? ' selected' : '' ) .
                '" href="#">' . _x( 'Posts', 'Search criterion', 'youneeq-panel' ) . '</a>' . PHP_EOL .
                '&#124;' . PHP_EOL . '<a id="' . $id . '-type-image" class="search-by' .
                ( $search_type == 'image' ? ' selected' : '' ) . '" href="#">' .
                _x( 'Images', 'Search criterion', 'youneeq-panel' ) . '</a>' . PHP_EOL . '</div>' .
                PHP_EOL . '<div class="search-bar">' . PHP_EOL . $out . PHP_EOL . '</div>' . PHP_EOL .
                '<div class="search-params">' . PHP_EOL . '<div class="search-params-date">' . PHP_EOL .
	            /* translators: 1: Starting date element. 2: Ending date element. */
                sprintf( esc_html__( '%1$s to %2$s', 'youneeq-panel' ),
					'<input type="date" name="startDate" />', '<input type="date" name="endDate" />' ) .
				PHP_EOL . '</div>' . PHP_EOL . '<div class="search-params-order">' . PHP_EOL .
				'<select name="orderBy">' . PHP_EOL . '<option value="relevance" selected>' .
				__( 'By Relevance', 'youneeq-panel' ) . '</option>' . PHP_EOL . '<option value="date">' .
				__( 'By Date', 'youneeq-panel' ) . '</option>' . PHP_EOL . '</select>' . PHP_EOL .
				'</div>' . PHP_EOL . '</div>';
        }

        $out = '<form' . $attr_string . '>' . PHP_EOL . $out . PHP_EOL . '</form>';

        if ( $id ) {
            $out .= '<script>' . self::display_form_script( $args, $id, $advanced ) . '</script>';
        }

        return $out;
    }

    /**
     * Displays a search results container.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  string[] $attrs   HTML element attributes.
     * @param  array    $args    Parameters provided to the shortcode.
     * @param  string   $content Inner content of the shortcode, if used in enclosing form.
     */
    public static function display_search( $attrs = null, $args = [], $content = '' ) {
        self::enqueue_script();
        return '<youneeq-search' . Yqr_Lib::build_attr_string( $attrs ) . '>' .
            do_shortcode( $content ) . '</youneeq-search>';
    }

    /**
     * Generates a script element for a search form.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  array  $args     Parameters provided to the shortcode.
     * @param  string $id       Form ID attribute.
     * @param  bool   $advanced Display advanced form with date and relevance options.
     */
    private static function display_form_script( $args, $id, $advanced ) {
        $for = isset( $args[ 'for' ] ) && $args[ 'for' ] ? $args[ 'for' ] : '';
        $selector = isset( $args[ 'headselector' ] ) && $args[ 'headselector' ] ?
            $args[ 'headselector' ] : '';
        $title = isset( $args[ 'headtext' ] ) && $args[ 'headtext' ] ?
            $args[ 'headtext' ] : '';

        $out = '';

        // Override submit behaviour and get results through ajax.
        if ( $for ) {
            $out .= <<<SUB
var target = $( '#$for' ),
    self = $( '#$id' );
SUB;

            if ( $advanced ) {
                $out .= <<<ADV

$( '#$id-type-post' ).click( function( event ) {
    event.preventDefault();

    var handler = target.data( 'youneeqSearchHandler' );
    handler.search_type = 'article';
	$( '#$id .search-option input' ).val( 'article' );
    handler.request( [ 'change_type' ] );
});

$( '#$id-type-image' ).click( function( event ) {
    event.preventDefault();

    var handler = target.data( 'youneeqSearchHandler' );
    handler.search_type = 'image';
	$( '#$id .search-option input' ).val( 'image' );
    handler.request( [ 'change_type' ] );
});

yq_date_fix( 'input[type="date"]', self );
ADV;
            }
        }

        // Replace page title text when search request is processed.
        if ( $selector && $title ) {
            $sep = apply_filters( 'document_title_separator', '-' );
            $head_title = apply_filters( 'document_title_parts', [
                'title' => $title,
                'site'  => get_bloginfo( 'name', 'display' )
            ]);
            $head_title = implode( " $sep ", array_filter( $head_title ) );
            $head_title = strip_tags( $head_title );
            $head_title = str_replace( "'", "\\'", $head_title );
            $head_title = capital_P_dangit( $head_title );

            $title = str_replace( "'", "\\'", $title );
            $title = capital_P_dangit( $title );

            $text_replace = <<<TRP
var search = jQuery( 'input[name="q"]', jQuery( '#$id' ) );
if ( search.length ) {
    search = search.val();
    jQuery( '$selector' ).text( ( '$title' ).replace( '%query%', search ) );
    jQuery( 'head title' ).text( ( '$head_title' ).replace( '%query%', search) );
}
TRP;
        }
        else {
            $text_replace = '';
        }

        // Put script pieces together.
        if ( $out ) {
            $out = "jQuery( function( $ ) {\n$out\n});\n";
        }
        $out .= <<<FORM
function yqsearchform_$id() {
    $text_replace
    return {};
}
FORM;

        return $out;
    }

    /**
     * Enqueues the search form scripts and styles.
     *
     * @since 3.0.0
     *
     * @param bool $include_css Determines if CSS files should be enqueued.
     */
    public static function enqueue_ui( $include_css = true ) {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'modernizr-inputtypes' );
        wp_enqueue_script( 'youneeq-date-fix' );

        if ( $include_css ) {
            wp_enqueue_style( 'jquery-ui-datepicker-yqstyle' );
        }
    }

    /**
     * Enqueues the search handler script.
     *
     * @since 3.0.0
     */
    public static function enqueue_script() {
        wp_enqueue_script( 'youneeq-search-lib' );
    }

}

/* end */