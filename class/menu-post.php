<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Menu_Post class
 *
 * Creates a metabox for configuring post-specific settings.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Menu_Post {

    /**
     * Generator string for the metabox authentication nonce.
     *
     * @since 3.0.0
     * @var   string
     */
    private $nonce;

    /**
     * Creates a new post menu object.
     *
     * @since 3.0.0
     */
    public function __construct() {
        $this->nonce = 'yqr-post-update' . get_current_user_id();
    }

    /**
     * Display settings metabox for this post.
     *
     * @since 3.0.0
     *
     * @param \WP_Post $post Post to display metabox for.
     */
    public function display( $post ) {
        $args = get_post_meta( $post->ID );
        $metadata = [
            'disable_observe' => $this->get_arg( $args, 'disable_observe' ),
            'post_title'      => $this->get_arg( $args, 'post_title', '' ),
            'post_desc'       => $this->get_arg( $args, 'post_desc', '' ),
            'post_img'        => $this->get_arg( $args, 'post_img', 0 )
        ];

        $this->display_script( $metadata );
        $this->display_form( $metadata );
    }

    /**
     * Update settings for the given post ID.
     *
     * @since 3.0.0
     *
     * @param int $id Post ID.
     */
    public function update( $id ) {
        // make sure save is valid
        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || !current_user_can( 'edit_post' ) || !isset( $_POST[ '_yqr_nonce' ] ) || !wp_verify_nonce( $_POST[ '_yqr_nonce' ], $this->nonce ) ) {
            return;
        }

        if ( isset( $_POST['youneeq_disable_observe'] ) ) {
            update_post_meta( $id, '_yq_disable_observe', '1' == $_POST['youneeq_disable_observe'] );
        }
        if ( isset( $_POST['youneeq_title'] ) ) {
            update_post_meta( $id, '_yq_post_title', sanitize_text_field( $_POST['youneeq_title'] ) );
        }
        if ( isset( $_POST['youneeq_desc'] ) ) {
            update_post_meta( $id, '_yq_post_desc', sanitize_text_field( $_POST['youneeq_desc'] ) );
        }
        if ( isset( $_POST['youneeq_img'] ) ) {
            update_post_meta( $id, '_yq_post_img', intval( $_POST['youneeq_img'] ) );
        }
    }

    /**
     * Adds post edit meta box.
     *
     * @since 3.0.0
     */
    public function register() {
        add_meta_box( 'youneeq-post-options', esc_html_x( 'Youneeq Observe Settings', 'Displayed name of Youneeq meta box in post edit page', 'youneeq-panel' ), [ $this, 'display' ], 'post', 'advanced', 'high' );
    }

    /**
     * Display metabox script.
     *
     * @since 3.0.0
     *
     * @param array $metadata Post settings.
     */
    private function display_script( $metadata ) {
        ?>
        <script>
            jQuery( function( $ ) {
                $( '#youneeq-img-pic, #youneeq-img-add' ).click( function( event ) {
                    event.preventDefault();

                    if ( wp.media.frames.yqr_frame ) {
                        wp.media.frames.yqr_frame.open();
                        return;
                    }

                    var frame = wp.media.frames.yqr_frame = wp.media({
                        'title': '<?php echo esc_js( __( 'Select observed image', 'youneeq-panel' ) ); ?>',
                        'button': { 'text': '<?php echo esc_js( __( 'Use selected image', 'youneeq-panel' ) ); ?>' },
                        'library': { 'type': 'image' },
                        'multiple': false
                    });

                    frame.on( 'select', function() {
                       img = frame.state().get( 'selection' ).first().toJSON();
                       $( '#youneeq-img' ).val( img.id );
                       $( '#youneeq-img-pic' ).attr( 'src', img.sizes.thumbnail.url );
                       $( '#youneeq-img-add' ).hide();
                       $( '#youneeq-img-pic-box, #youneeq-img-howto, #youneeq-img-remove' ).show();
                    });

                    frame.open();
                });

                $( '#youneeq-img-remove' ).click( function( event ) {
                    event.preventDefault();

                    $( '#youneeq-img' ).val( '' );
                    $( '#youneeq-img-add' ).show();
                    $( '#youneeq-img-pic-box, #youneeq-img-howto, #youneeq-img-remove' ).hide();
                });
            });
        </script>
        <?php
    }

    /**
     * Display metabox form.
     *
     * @since 3.0.0
     *
     * @param array $metadata Post settings.
     */
    private function display_form( $metadata ) {
        $post_img_src = $metadata[ 'post_img' ] ? wp_get_attachment_image_src( $metadata[ 'post_img' ], 'thumbnail' )[0] : '';

        ?>
        <input type="hidden" name="_yqr_nonce" value="<?php echo wp_create_nonce( $this->nonce ); ?>" required />
        <table class="form-table yqr-meta-box">
            <tbody>
                <tr>
                    <td colspan="2">
                        <label class="selectit" for="youneeq-disable-observe">
                            <input name="youneeq_disable_observe" type="hidden" value="0" />
                            <input id="youneeq-disable-observe" name="youneeq_disable_observe" type="checkbox" value="1" <?php echo $metadata[ 'disable_observe' ] ? 'checked ' : ''; ?>/>
                            <?php esc_html_e( 'Prevent this post from being observed.', 'youneeq-panel' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="howto">
                            <?php esc_html_e( 'Title, description, and image will be sent as metadata when this post is observed. If left blank, the post title, excerpt, or featured image will be used instead.', 'youneeq-panel' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="youneeq-title"><?php echo esc_html_x( 'Title', 'Post title', 'youneeq-panel' ); ?></label>
                    </th>
                    <td>
                        <input id="youneeq-title" name="youneeq_title" class="widefat" type="text" value="<?php echo esc_attr( $metadata[ 'post_title' ] ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="youneeq-desc"><?php esc_html_e( 'Description', 'youneeq-panel' ); ?></label>
                    </th>
                    <td>
                        <textarea id="youneeq-desc" name="youneeq_desc" class="widefat" rows="1" cols="40"><?php echo esc_html( $metadata[ 'post_desc' ] ); ?></textarea>
                    </td>
                </tr>
                <tr class="hide-if-no-js">
                    <th scope="row">
                        <label for="youneeq-img-upload"><?php esc_html_e( 'Image', 'youneeq-panel' ); ?></label>
                    </th>
                    <td>
                        <input id="youneeq-img" name="youneeq_img" type="hidden" value="<?php echo esc_attr( $metadata[ 'post_img' ] ); ?>" />
                        <p id="youneeq-img-pic-box"<?php echo $post_img_src ? '' : ' style="display:none;"'; ?>>
                            <img id="youneeq-img-pic" src="<?php echo esc_attr( $post_img_src ); ?>" />
                        </p>
                        <p id="youneeq-img-howto" class="howto"<?php echo $post_img_src ? '' : ' style="display:none;"'; ?>>
                            <?php esc_html_e( 'Click the image to edit or update', 'youneeq-panel' ); ?>
                        </p>
                        <p>
                            <a id="youneeq-img-remove" href="#"<?php echo $post_img_src ? '' : ' style="display:none;"', '>', esc_html__( 'Remove image', 'youneeq-panel' ); ?></a>
                            <a id="youneeq-img-add" href="#"<?php echo $post_img_src ? ' style="display:none;"' : '', '>', esc_html__('Add image', 'youneeq-panel' ); ?></a>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Gets the value of the post setting with the given name.
     *
     * @since 3.0.0
     *
     * @return mixed
     * @param  array  $args Post arguments.
     * @param  string $name Setting name.
     * @param  mixed  $def  Default value.
     */
    private function get_arg( $args, $name, $def = false ) {
        return isset( $args[ '_yq_' . $name ] ) ? $args[ '_yq_' . $name ][0] : $def;
    }

}

/* end */