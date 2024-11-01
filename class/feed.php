<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Feed class
 *
 * Handles Youneeq search feed logic. The search feed is used to collect and format post data as easily
 * readable JSON.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Feed {

    /**
     * Displays a JSON feed of site post data.
     *
     * Feed is output at /feed/yqdata and can accept parameters through GET or POST to modify output.
     * Accepts "count" or "items" parameters.
     *
     * @since 3.0.0
     */
    public static function display() {
        header( 'Content-Type: application/json' );

        $args = [
            'posts_per_page'    => 100,
            'offset'            => 0,
            'post_status'       => 'publish'
        ];
        if ( isset( $_REQUEST['count'] ) && gettype( $_REQUEST['count'] ) == 'integer' && $_REQUEST['count'] > 0 && $_REQUEST['count'] < 100 ) {
            $args['posts_per_page'] = $_REQUEST['count'];
        }
        if ( isset( $_REQUEST['items'] ) ) {
            $args['post__in'] = array_filter( array_map( function( $a ) {
                return (int) $a;
            }, explode( ',', $_REQUEST['items'] ) ) );
        }

        $data = array_map( 'Yqr_Feed::format_post', get_posts( $args ) );
        echo json_encode( $data, JSON_UNESCAPED_SLASHES );
    }

    /**
     * Formats post metadata into Youneeq Search's metadata format.
     *
     * @since 3.0.0
     *
     * @return array
     * @param  \WP_Post $raw_post Post to be formatted.
     */
    public static function format_post( $raw_post ) {
        $post = [
            'id'            => '' . $raw_post->ID,
            'title'         => $raw_post->post_title,
            'url'           => get_permalink( $raw_post ),
            'publish_date'  => $raw_post->post_date_gmt . 'Z',
            'article_text'  => $raw_post->post_content,
            'author'        => get_userdata( $raw_post->post_author )->display_name
        ];

        $images = get_posts([
            'post_type'         => 'attachment',
            'posts_per_page'    => -1,
            'post_status'       => null,
            'post_parent'       => $raw_post->ID
        ]);
        if ( count( $images ) > 0 ) {
            $post['images'] = array_map( function( $a ) {
                return [
                    'url'       => wp_get_attachment_url( $a->ID ),
                    'caption'   => has_excerpt( $a ) ? get_the_excerpt( $a ) : '',
                    'credit'    => get_userdata( $a->post_author )->display_name
                ];
            }, $images );
        }

        $tags = wp_get_post_tags( $raw_post->ID, [ 'fields' => 'names' ] );
        if ( count( $tags ) > 0 ) {
            $post['tags'] = $tags;
        }

        return $post;
    }

}

/* end */