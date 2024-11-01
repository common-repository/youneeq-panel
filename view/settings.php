<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Youneeq admin settings page template.
 *
 * Displays site-wide configuration options for the Youneeq Recommendations plugin.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5
 */
?>
<div class="wrap yqr-wrap">
    <script>
        jQuery( function( $ ) {
            $( '.yqr-domain-entry' ).each( function( i, self ) {
                var template = $( 'tfoot tr:last-child', self ).detach(),
                    base_name = $( self ).data( 'baseName' );

                // Delete selected sites
                $( 'thead button', self ).click( function( event ) {
                    $( 'tbody input:checked', self ).parents( 'tr' ).remove();
                    $( 'tfoot tr:first-child td:first-child input', self ).prop( 'checked', false ).prop( 'indeterminate', false );
                });

                // Set checked and indeterminate status of "select all" checkbox
                $( self ).on( 'click', 'tbody td:first-child input', function( event ) {
                    var total = $( 'tbody td:first-child input', self ).length,
                        checked = $( 'tbody td:first-child input:checked', self ).length,
                        master = $( 'tfoot tr:first-child td:first-child input', self );

                    if ( checked && checked < total ) {
                        master.prop( 'checked', false ).prop( 'indeterminate', true );
                    }
                    else {
                        master.prop( 'checked', checked ? true : false ).prop( 'indeterminate', false );
                    }
                });

                // Select or deselect all sites
                $( 'tfoot tr:first-child td:first-child input', self ).click( function( event ) {
                    $( 'tbody input', self ).prop( 'checked', $( event.target ).prop('checked') );
                });

                // Click "Add Sites" button when enter is pressed on input field
                $( 'tfoot tr:first-child td:nth-child(2) input', self ).keyup( function( event ) {
                    if ( event.which == 13 ) {
                        $( 'tfoot button:first-child', self ).click();
                    }
                });

                // Add sites from input field
                $( 'tfoot button:first-child', self ).click( function( event ) {
                    var urls = $( 'tfoot tr:first-child td:nth-child(2) input', self );

                    if ( urls.prop( 'value' ).match( new RegExp( urls.attr( 'pattern' ) ) ) ) {
                        var num_sites = $( 'tbody tr', self ).length;

                        urls.prop( 'value' ).split( ' ' ).forEach( function( url ) {
                            add_site( url, url );
                        });
                        urls.prop( 'value', '' );

                        $( 'tbody tr:nth-child(' + ( num_sites + 1 ) + ') td:last-child input', self ).select();
                    }
                });

                // Add current site
                $( 'tfoot button:nth-child(2)', self ).click( function( event ) {
                    add_site( window.location.hostname, '<?= esc_js( get_bloginfo( 'name' ) ) ?>' );
                });

                $( 'tfoot button:nth-child(3)', self ).click( function( event ) {
                    var old_data = null,
                        new_data = null;

                    old_data = $( 'tbody tr', self ).map( function( i, e ) {
                        return {
                            'url': $( 'td:nth-child(2) input', e ).val(),
                            'name': $( 'td:nth-child(3) input', e ).val()
                        };
                    }).get();

                    try {
                        new_data = JSON.parse( prompt( '<?= esc_js( __( 'You can export the current site lists to another Youneeq Recommender install by copying the data below, or import sites by pasting data instead.', 'youneeq-panel' ) ) ?>', JSON.stringify( old_data ) ) );
                    }
                    catch ( e ) {
                        new_data = null;
                    }

                    if ( new_data ) {
                        new_data.forEach( function(e) {
                            add_site( e.url, e.name );
                        });
                    }
                });

                // Adds a website with the given URL and name
                function add_site( url, name ) {
                    var existing = $( 'tbody td:nth-child(2) input', self ).map( function( i, e ) {
                        return $( e ).attr( 'value' );
                    }).toArray();

                    if ( existing.indexOf( url ) == -1 ) {
                        var new_entry = template.clone()
                            .find( 'td:nth-child(2) input' ).attr( 'value', url ).attr( 'name', base_name + '[' + url + '][url]' ).end()
                            .find( 'td:nth-child(2) a' ).attr( 'href', '//' + url ).text( url ).end()
                            .find( 'td:last-child input' ).attr( 'value', name ).attr( 'name', base_name + '[' + url + '][name]' ).end();

                        $( 'tbody', self ).append( new_entry );
                    }
                }
            });

            // Display script file selector.
            $( '#js_file-name-box, #js_file-add' ).click( function( event ) {
                event.preventDefault();

                if ( wp.media.frames.yqr_js_frame ) {
                    wp.media.frames.yqr_js_frame.open();
                    return;
                }

                var frame = wp.media.frames.yqr_js_frame = wp.media({
                    'title': '<?= esc_js( __( 'Select script file', 'youneeq-panel' ) ) ?>',
                    'button': { 'text': '<?= esc_js( __( 'Use selected script', 'youneeq-panel' ) ) ?>' },
                    'library': { 'type': 'application/javascript' },
                    'multiple': false
                });

                frame.on( 'select', function() {
                   js = frame.state().get( 'selection' ).first().toJSON();
                   $( '#js_file' ).val( js.id );
                   $( '#js_file-name-box' ).val( js.name );
                   $( '#js_file-add' ).hide();
                   $( '#js_file-remove' ).show();
                });

                frame.open();
            });

            $( '#js_file-remove' ).click( function( event ) {
                event.preventDefault();

                $( '#js_file' ).val( 0 );
                $( '#js_file-name-box' ).val( '' );
                $( '#js_file-add' ).show();
                $( '#js_file-remove' ).hide();
            });
        });
    </script>
    <?php $this->display_header(); ?>
    <form action="" method="POST">
        <input type="hidden" name="_yqr_nonce" value="<?= $this->get_nonce() ?>" required />
        <input type="hidden" name="plugin_version" value="<?= esc_attr( Yqr_Main::VERSION ) ?>" />
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="images-size"><?= esc_html__( 'Image Size', 'youneeq-panel' ) ?></label>
                    </th>
                    <td>
                        <select name="pic_observe">
                        <?php

                        // Outputs a list of all registered image sizes.

                        $pic_setting = Yqr_Main::settings( 'pic_observe' );
                        $pic_size_list = get_intermediate_image_sizes();

                        if ( 'full' != $pic_setting && !in_array( $pic_setting, $pic_size_list ) ) {
                            $pic_setting = Yqr_Main::defaults( 'pic_observe' );
                        }

                        $img_sizes = array_map( function( $size ) {
                           $params = isset( $GLOBALS[ '_wp_additional_image_sizes' ][ $size ] )
                                   ? $GLOBALS[ '_wp_additional_image_sizes' ][ $size ]
                                   : [ 'width' => get_option( $size . '_size_w' ), 'height' => get_option( $size . '_size_h' ), 'crop' => get_option( $size . '_crop' ) ];
                           $params[ 'name' ] = $size;
                           return $params;
                        }, $pic_size_list );

                        ?>
                        <option value="full"<?= $pic_setting == 'full' ? ' selected' : '' ?>>
                            <?=
                            /* translators: Full Size refers to an image size. */
                            esc_html__( 'Full Size', 'youneeq-panel' ) ?>
                        </option>
                        <?php

                        foreach ( $img_sizes as $img_size ) {
                            switch ( $img_size[ 'name' ] ) {
                                case 'thumbnail' :
                                    /* translators: Thumbnail is a WordPress image size. */
                                    $display_name = __( 'Thumbnail', 'youneeq-panel' );
                                    break;
                                case 'medium' :
                                    /* translators: Medium is a WordPress image size. */
                                    $display_name = __( 'Medium', 'youneeq-panel' );
                                    break;
                                case 'medium_large' :
                                    /* translators: Medium Large is a WordPress image size. */
                                    $display_name = __( 'Medium Large', 'youneeq-panel' );
                                    break;
                                case 'large' :
                                    /* translators: Large is a WordPress image size. */
                                    $display_name = __( 'Large', 'youneeq-panel' );
                                    break;
                                default :
                                    $display_name = $img_size[ 'name' ];
                            }

                            ?>
                            <option value="<?= esc_attr( $img_size[ 'name' ] ) ?>"<?= $pic_setting == $img_size[ 'name' ] ? ' selected' : '' ?>>
                                <?= esc_html( $display_name ) ?>
                            </option>
                            <?php
                        }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?= esc_html__( 'Ignored Categories', 'youneeq-panel' ) ?></label>
                    </th>
                    <td>
                        <?= Yqr_Lib::display_category_menu( 'filtered-cats', 'filtered_cats', Yqr_Main::settings( 'filtered_cats' ) ) ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?=
                        // translators: Refers to a file containing a script for displaying post data, not "display a script file."
                        esc_html__( 'Display Script File', 'youneeq-panel' )
                        ?></label>
                    </th>
                    <td>
                        <?php
                        $js_id = intval( Yqr_Main::settings( 'js_file' ) );
                        $js_id = $js_id < 0 ? 0 : $js_id;
                        ?>
                        <input id="js_file" name="js_file" type="hidden" value="<?= $js_id ?>" />
                        <input id="js_file-name-box" type="text" value="<?= $js_id ? esc_attr( get_the_title( $js_id ) ) : '' ?>" readonly />
                        <br />
                        <a id="js_file-add" href="#"<?= ( $js_id ? ' style="display:none;"' : '' ), '>', esc_html__( 'Add script file', 'youneeq-panel' ) ?></a>
                        <a id="js_file-remove" href="#"<?= ( $js_id ? '' : ' style="display:none;"' ), '>', esc_html__( 'Remove script file', 'youneeq-panel' ) ?></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= esc_html__( 'Youneeq Search Page', 'youneeq_panel' ) ?>
                    </th>
                    <td>
                        <?php
                        $page_id = intval( Yqr_Main::settings( 'search_page' ) );
                        $pages = get_pages([
                            'post_type'   => 'page'
                        ]);

                        $page_id = $page_id < 0 ? 0 : $page_id;

                        ?>
                        <select id="search_page" name="search_page">
                            <option value="0"<?= ( !$page_id ? ' selected' : '' ) ?>>
                                <?=esc_html__( '(none)', 'youneeq-panel' )?>
                            </option>
                        <?php
                        foreach ( $pages as $page ) {
                            ?>
                            <option value="<?= esc_attr( $page->ID ) ?>"<?= ( $page_id == $page->ID ? ' selected' : '' ) ?>>
                                <?= esc_html( $page->post_title ) ?>
                            </option>
                            <?php
                        }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="use_jqueryui_css"><?=
                            esc_html__( 'Include jQuery UI Styles', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="use_jqueryui_css" type="checkbox" name="use_jqueryui_css" value="1" <?=
                            Yqr_Main::settings( 'use_jqueryui_css' ) ? 'checked ' : ''
                        ?>/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="use_jqueryui_css"><?=
                            esc_html__( 'Include Explorer Styles', 'youneeq-panel' )
                        ?></label>
                    </th>
                    <td>
                        <input id="use_explorer_css" type="checkbox" name="use_explorer_css" value="1" <?=
                            Yqr_Main::settings( 'use_explorer_css' ) ? 'checked ' : ''
                        ?>/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="scroll_enable"><?php
                        esc_html_e( 'Infinite Scroll Posts', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="scroll_enable" type="checkbox" name="scroll_enable" value="1" <?=
                            Yqr_Main::settings( 'scroll_enable' ) ? 'checked ' : ''
                        ?>/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="scroll_offset"><?php
                        esc_html_e( 'Scroll Offset', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="scroll_offset" type="number" name="scroll_offset" value="<?= esc_attr( Yqr_Main::settings( 'scroll_offset' ) ) ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="scroll_cooldown"><?php
                        esc_html_e( 'Scroll Cooldown', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="scroll_cooldown" type="number" name="scroll_cooldown" value="<?= esc_attr( Yqr_Main::settings( 'scroll_cooldown' ) ) ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="scroll_attach"><?php
                        esc_html_e( 'Scroll Attach Selector', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="scroll_attach" type="text" name="scroll_attach" value="<?= esc_attr( Yqr_Main::settings( 'scroll_attach' ) ) ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="scroll_story"><?php
                        esc_html_e( 'Scroll Post Selector', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="scroll_story" type="text" name="scroll_story" value="<?= esc_attr( Yqr_Main::settings( 'scroll_story' ) ) ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ga_function"><?php
                        esc_html_e( 'Google Analytics Function', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="ga_function" type="text" name="ga_function" value="<?= esc_attr( Yqr_Main::settings( 'ga_function' ) ) ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ga_tracker"><?php
                        esc_html_e( 'Google Analytics Tracker', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="ga_tracker" type="text" name="ga_tracker" value="<?= esc_attr( Yqr_Main::settings( 'ga_tracker' ) ) ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ga_override"><?php
                        esc_html_e( 'Google Analytics Override', 'youneeq-panel' );
                        ?></label>
                    </th>
                    <td>
                        <input id="ga_override" type="text" name="ga_override" value="<?= esc_attr( Yqr_Main::settings( 'ga_override' ) ) ?>" />
                    </td>
                </tr>
            </tbody>
        </table>

        <h2 class="title"><?= esc_html__( 'Network Sites', 'youneeq-panel' ) ?></h2>
        <table class="yqr-domain-entry" data-base-name="domains">
            <thead>
                <td>
                    <button class="button-secondary" type="button">
                        X
                    </button>
                </td>
                <th>
                    <?php esc_html_e( 'Domain Name', 'youneeq-panel' ); ?>
                </th>
                <th>
                    <?php esc_html_e( 'Name of Site', 'youneeq-panel' ); ?>
                </th>
            </thead>
            <tbody>
            <?php
            $doms = Yqr_Main::settings( 'domains' );

            foreach ( $doms as $url => $desc ) {
                $url_esc = esc_attr( $url );
                $desc_esc = esc_attr( $desc );

                ?>
                <tr>
                    <td>
                        <input type="checkbox" />
                    </td>
                    <td>
                        <input name="domains<?= '[' . $url_esc . '][url]' ?>" value="<?= $url_esc ?>" type="hidden" />
                        <a href="//<?= $url_esc ?>" target="_blank"><?= esc_html( $url ) ?></a>
                    </td>
                    <td>
                        <input class="widefat" name="domains<?= '[' . $url_esc . '][name]' ?>" value="<?= $desc_esc ?>" type="text" />
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <input type="checkbox" form="" />
                    </td>
                    <td>
                        <input placeholder="example.com" type="text" pattern="<?= Yqr_Lib::REGEX_URL ?>" form="" />
                    </td>
                    <td>
                        <button class="button-secondary" type="button">
                            <?php esc_html_e( 'Add Sites', 'youneeq-panel' ); ?>
                        </button>
                        <button class="button-secondary" type="button">
                            <?php esc_html_e( 'Add This Site', 'youneeq-panel' ); ?>
                        </button>
                        <button class="button-secondary" type="button">
                            <?php esc_html_e( 'Import or Export', 'youneeq-panel' ); ?>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" />
                    </td>
                    <td>
                        <input type="hidden" />
                        <a target="_blank"></a>
                    </td>
                    <td>
                        <input class="widefat" type="text" />
                    </td>
                </tr>
            </tfoot>
        </table>
        <input name="action" type="hidden" value="submit" />
        <button class="button-primary" type="submit">
            <?= esc_html__('Save Changes', 'youneeq-panel') ?>
        </button>
    </form>
</div>
