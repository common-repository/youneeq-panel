<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Lib class
 *
 * Contains common methods and values used across the plugin.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Lib {

    /**
     * Regular expression to recognize a date in ISO 8601 format.
     *
     * Expects yyyy-mm-dd.
     *
     * @since 3.0.0
     * @var   string
     */
    const REGEX_ISO_8601 = '^\d{4}-[01]\d-[0123]\d$';
    /**
     * Regular expression to recognize a web URL.
     *
     * @since 3.0.0
     * @var   string
     */
    const REGEX_URL = '^([a-z0-9]+\.)?[a-z0-9][a-z0-9-]*\.[a-z]{2,}( ([a-z0-9]+\.)?[a-z0-9][a-z0-9-]*\.[a-z]{2,})*$';

    /**
     * Clamps a number between a minimum and maximum value.
     *
     * @since 3.0.0
     *
     * @return int|float      Value of $val if it is between $min and $max. Otherwise, returns value of
     *                        $min if $val < $min, or value of $max if $val > $max.
     * @param  int|float $val Value to clamp.
     * @param  int|float $min Minimum value.
     * @param  int|float $max Maximum value.
     */
    public static function clamp( $val, $min = PHP_INT_MIN, $max = PHP_INT_MAX ) {
        if ( $min < $max ) {
            return min( $max, max( $min, $val ) );
        }
        else {
            return min( $min, max( $max, $val ) );
        }
    }

    /**
     * Swaps the values of two numeric variables.
     *
     * @since 3.0.0
     *
     * @param int|float &$a
     * @param int|float &$b
     */
    public static function swap( &$a, &$b ) {
        $a += $b;
        $b = $a - $b;
        $a -= $b;
    }

    /**
     * Compares two arrays to determine if they are identical.
     *
     * This checks to see if the two arrays have the same keys, with the same value for each key.
     *
     * @since 3.0.0
     *
     * @return bool
     * @param  array $array1 First array to compare.
     * @param  array $array2 Second array to compare.
     */
    public static function array_comp( $array1, $array2 ) {
        $same = true;

        foreach ( array_keys( array_merge( $array1, $array2 ) ) as $key ) {
            if ( !isset( $array1[ $key ] ) || !isset( $array2[ $key ] ) || $array1[ $key ] != $array2[ $key ] ) {
                $same = false;
                break;
            }
        }

        return $same;
    }

    /**
     * Creates a list of categories, with checkboxes.
     *
     * @since 3.0.0
     *
     * @return string                  Category list element.
     * @param  string          $id     Base element ID for the category list.
     * @param  string          $name   Base form name for the category list.
     * @param  int[]|null      $filter Array of category IDs that should be checked by default.
     * @param  \WP_Term[]|null $cats   Array of categories to display.
     *                                 If not included, all categories will be displayed.
     */
    public static function display_category_menu( $id, $name, $filter = null, $cats = null ) {
        $out_string = '';
        $first = !$cats;
        $filter = $filter ?: [];

        if ( $first ) {
            $cats = get_categories( [ 'hide_empty' => 0, 'parent' => 0 ] );
            $out_string = '<ul id="' . $id . '" class="yqr-options-box-list">';
        }
        else {
            $out_string = '<ul>';
        }

        foreach  ( $cats as $cat ) {
            $slug = $cat->slug;
            $val = $cat->term_id;
            $checked = in_array( $val, $filter );
            $children = get_categories( [ 'hide_empty' => 0, 'parent' => $cat->term_id ] );

            $out_string .= '<li><label for="' . $id . '_' . $slug . '"><input id="' . $id . '_' . $slug .
				'" name="' . $name . '[]" type="checkbox" value="' . $val . '" ' .
				( $checked ? 'checked ' : '' ) . '/> ' . $cat->name . '</label>';

            if ( count( $children ) > 0 ) {
                $out_string .= self::display_category_menu( $id, $name, $filter, $children );
            }

            $out_string .= '</li>';
        }

        return $out_string . '</ul>';
    }

    /**
     * Converts an associative array into a string representing HTML attributes.
     *
     * @since 3.0.0
     *
     * @return string[]        HTML attribute string.
     * @param  string   $attrs Array of attributes.
     */
    public static function build_attr_string( $attrs ) {
        $attr_string = '';

        if ( $attrs && count( $attrs ) > 0 ) {
            foreach ( $attrs as $key => $value ) {
                $attr_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
            }
        }

        return $attr_string;
    }

    /**
     * Gets jQuery UI datepicker localized strings.
     *
     * @since 3.0.0
     *
     * @return array
     */
    public static function get_date_fix_l10n() {
        return [
            'regex_iso_8601'    => self::REGEX_ISO_8601,
            'placeholder'       => _x( 'yyyy-mm-dd', 'Placeholder text for date input element', 'youneeq-panel' ),
            'first_day'         => '' . get_option( 'start_of_week', 0 ),
			'tz_offset'			=> '' . get_option( 'gmt_offset', 0 ),
            'day_names'         => [
                __( 'Sunday', 'youneeq-panel' ),
                __( 'Monday', 'youneeq-panel' ),
                __( 'Tuesday', 'youneeq-panel' ),
                __( 'Wednesday', 'youneeq-panel' ),
                __( 'Thursday', 'youneeq-panel' ),
                __( 'Friday', 'youneeq-panel' ),
                __( 'Saturday', 'youneeq-panel' )
            ],
            'day_names_min'     => [
                _x( 'S', 'Short form of Sunday', 'youneeq-panel' ),
                _x( 'M', 'Short form of Monday', 'youneeq-panel' ),
                _x( 'T', 'Short form of Tuesday', 'youneeq-panel' ),
                _x( 'W', 'Short form of Wednesday', 'youneeq-panel' ),
                _x( 'T', 'Short form of Thursday', 'youneeq-panel' ),
                _x( 'F', 'Short form of Friday', 'youneeq-panel' ),
                _x( 'S', 'Short form of Saturday', 'youneeq-panel' )
            ],
            'day_names_short'   => [
                _x( 'Sun', 'Abbreviation of Sunday', 'youneeq-panel' ),
                _x( 'Mon', 'Abbreviation of Monday', 'youneeq-panel' ),
                _x( 'Tue', 'Abbreviation of Tuesday', 'youneeq-panel' ),
                _x( 'Wed', 'Abbreviation of Wednesday', 'youneeq-panel' ),
                _x( 'Thu', 'Abbreviation of Thursday', 'youneeq-panel' ),
                _x( 'Fri', 'Abbreviation of Friday', 'youneeq-panel' ),
                _x( 'Sat', 'Abbreviation of Saturday', 'youneeq-panel' )
            ],
            'month_names'       => [
                __( 'January', 'youneeq-panel' ),
                __( 'February', 'youneeq-panel' ),
                __( 'March', 'youneeq-panel' ),
                __( 'April', 'youneeq-panel' ),
                __( 'May', 'youneeq-panel' ),
                __( 'June', 'youneeq-panel' ),
                __( 'July', 'youneeq-panel' ),
                __( 'August', 'youneeq-panel' ),
                __( 'September', 'youneeq-panel' ),
                __( 'October', 'youneeq-panel' ),
                __( 'November', 'youneeq-panel' ),
                __( 'December', 'youneeq-panel' )
            ],
            'month_names_short' => [
                _x( 'Jan', 'Abbreviation of January', 'youneeq-panel' ),
                _x( 'Feb', 'Abbreviation of February', 'youneeq-panel' ),
                _x( 'Mar', 'Abbreviation of March', 'youneeq-panel' ),
                _x( 'Apr', 'Abbreviation of April', 'youneeq-panel' ),
                _x( 'May', 'Abbreviation of May', 'youneeq-panel' ),
                _x( 'Jun', 'Abbreviation of June', 'youneeq-panel' ),
                _x( 'Jul', 'Abbreviation of July', 'youneeq-panel' ),
                _x( 'Aug', 'Abbreviation of August', 'youneeq-panel' ),
                _x( 'Sep', 'Abbreviation of September', 'youneeq-panel' ),
                _x( 'Oct', 'Abbreviation of October', 'youneeq-panel' ),
                _x( 'Nov', 'Abbreviation of November', 'youneeq-panel' ),
                _x( 'Dec', 'Abbreviation of December', 'youneeq-panel' )
            ]
        ];
    }

    /**
     * Gets the URL for a given image, using the site's observed image size setting.
     *
     * @since 3.0.0
     *
     * @param int $image_id ID of the image.
     */
	public static function get_image_src( $image_id ) {
		return wp_get_attachment_image_src( $image_id, Yqr_Main::settings( 'pic_observe' ) )[0];
	}

    /**
     * Attempts to identify a valid date value and format it for output.
     *
     * @since 3.0.0
     *
     * @return string|null                  Formatted date, or null if the date given is invalid.
     * @param  \DateTime|string\int $date   A date value.
     * @param  string               $format Format to use for displaying the date.
     */
    public static function format_date( $date, $format = 'c' ) {
        $out = null;

        if ( $date ) {
            if ( is_object( $date ) && 'DateTime' == get_class( $date ) ) {
                $out = $date->format( $format );
            }
            elseif ( is_string( $date ) ) {
                $strout = strtotime( $date );
                if ( $strout !== false ) {
                    $out = date( $format, $strout );
                }
            }
            elseif ( is_int( $date ) ) {
                $out = date( $format, $date );
            }
        }

        return $out;
    }

    /**
     * Outputs a post to be returned through ajax.
     *
     * Post ID is retrieved from POST args.
     *
     * @since 3.0.5
     */
    public static function ajax_post() {
        $story = get_post( intval( $_POST['post_id'] ) );

        if ( $story ) {
            global $post;
            $post = $story;
            setup_postdata();

            do_action( 'yqr_ajax_post', $story );

            wp_reset_postdata();
        }

        wp_die();
    }

    /**
     * Outputs Youneeq post ajax handler.
     *
     * @since 3.0.5
     */
    public static function ajax_handler() {
        if ( Yqr_Main::settings( 'scroll_enable' ) && Yqr_Observe::can_observe() ) {
            $scroll_attach = Yqr_Main::settings( 'scroll_attach' );
            $scroll_story = Yqr_Main::settings( 'scroll_story' );

            if ( $scroll_attach && $scroll_story ) {
                wp_enqueue_script( 'youneeq-scroll' );

                ?>
                <youneeq-section data-yq-display-function="yqr_cache_stories" data-yq-count="5" data-yq-suggest-options="paging_enabled" style="display: none;"></youneeq-section>
                <?php
            }
        }
    }

}

/* end */