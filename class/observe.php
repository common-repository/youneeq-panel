<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Observe class
 *
 * Handles all post observe logic.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Observe {

    /**
     * Whether or not the observe script has been generated.
     *
     * @since 3.0.0
     * @var   bool
     */
    private static $observe_done = false;
    /**
     * Whether or not the page has been checked to determine if it is observable.
     *
     * @since 3.0.0
     * @var   bool
     */
    private static $observe_checked = false;
    /**
     * Whether or not the page is observable.
     *
     * @since 3.0.0
     * @var   bool
     */
    private static $allow_observe = false;

    /**
     * Checks to determine if the current page is an observable post.
     *
     * @since 3.0.0
     *
     * @return bool
     */
    public static function can_observe() {
        $post_id = get_the_ID();

        if ( !self::$observe_checked ) {
            self::$allow_observe = is_single() && 'post' == get_post_type( $post_id ) && 'publish' == get_post_status( $post_id ) && !get_post_meta( $post_id, '_yq_disable_observe', true ) && ( !Yqr_Main::settings( 'filtered_cats' ) || !array_intersect( array_map( function( $cat ) {
                return $cat->term_id;
            }, get_the_category() ), Yqr_Main::settings( 'filtered_cats' ) ) );
            self::$observe_checked = true;
        }

        return self::$allow_observe;
    }

    /**
     * Gets post observe JS function if the current page can be observed.
     *
     * @since 3.0.0
     *
     * @return string
     */
    public static function get_observe() {
        $post_id = get_the_ID();

        if ( !self::$observe_done && self::can_observe() ) {
            self::$observe_done = true;
            self::$allow_observe = true;
            return self::generate( $post_id, 'yqr_observe' );
        }
        else {
            return '';
        }
    }

    /**
     * Adds filter hooks.
     *
     * @since 3.0.0
     *
     * @param bool $is_admin Should be true if called on admin-facing pages.
     */
    public static function set_filters( $is_admin ) {
        $filter = 'yqr_observe';

        add_filter( $filter, 'Yqr_Observe::remove_tags' );
        add_filter( $filter, 'Yqr_Observe::set_image_src' );
        add_filter( $filter, 'Yqr_Observe::format_post_cats' );
        add_filter( $filter, 'Yqr_Observe::format_post_tags' );
    }

    /**
     * Strips HTML tags from title and description in post observe metadata.
     *
     * @since 3.0.0
     *
     * @return array
     * @param  array $data Array of post observe metadata.
     */
    public static function remove_tags( $data ) {
        $data['title'] = strip_tags( $data['title'] );
        $data['description'] = strip_tags( $data['description'] );

        return $data;
    }

    /**
     * Converts image ID into image url in post observe metadata.
     *
     * @since 3.0.0
     *
     * @return array
     * @param  array $data Array of post observe metadata.
     */
    public static function set_image_src( $data ) {
        $data['image'] = Yqr_Lib::get_image_src( $data['image'] );

        return $data;
    }

    /**
     * Formats category array into category name pairs in post observe metadata.
     *
     * @since 3.0.0
     *
     * @return array
     * @param  array $data Array of post observe metadata.
     */
    public static function format_post_cats( $data ) {
        $data['categories'] = array_map( function( $cat ) {
            if ( $cat->parent ) {
                return [ $cat->name, get_category( $cat->parent )->name ];
            }
            else {
                return [ $cat->name ];
            }
        }, $data['categories'] );

        return $data;
    }

    /**
     * Formats tag array into name entries in post observe metadata.
     *
     * @since 3.0.0
     *
     * @return array
     * @param  array $data Array of post observe metadata.
     */
    public static function format_post_tags( $data ) {
        if ( isset( $data['tags'] ) && $data['tags'] ) {
			$data['tags'] = array_map( function( $tag ) {
	            return $tag->name;
	        }, $data['tags'] );
		}

        return $data;
    }

    /**
     * Gets post observe JS function.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  int    $post_id   ID of the post to get metadata for.
     * @param  string $func_name Name to give to the JS function.
     *                           Must follow Javascript function naming conventions.
     */
    private static function generate( $post_id, $func_name ) {
        return 'function ' . $func_name . '() { return ' .
                json_encode( self::get_post_data( $post_id ), JSON_UNESCAPED_SLASHES ) . '; }';
    }

    /**
     * Extracts post metadata and formats it for display as a JSON object.
     *
     * @since 3.0.0
     *
     * @return array
     * @param  int   $post_id ID of the post to get metadata for.
     */
    private static function get_post_data( $post_id ) {
        $post_title = get_post_meta( $post_id, '_yq_post_title', true );
        $post_desc  = get_post_meta( $post_id, '_yq_post_desc', true );
        $post_img   = get_post_meta( $post_id, '_yq_post_img', true );
        $metadata   = [
            'name'         => '' . $post_id,
            'title'        => $post_title ?: get_the_title(),
            'description'  => $post_desc ?: get_the_excerpt(),
            'create_date'  => get_the_date( 'c' ),
            'image'        => $post_img ?: get_post_thumbnail_id(),
            'categories'   => get_the_category(),
            'tags'         => get_the_tags(),
            'content_type' => 'content'
        ];

        /**
         * Processes post observe metadata.
         *
         * @since 3.0.0
         *
         * @param array $metadata Post metadata to observe.
         * @param int   $post_id  ID of the post to get metadata for.
         */
        $post_data = apply_filters( 'yqr_observe', $metadata, $post_id );

        return array_filter( $post_data );
    }

}

/* end */