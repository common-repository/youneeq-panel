<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Widget_Explorer class
 *
 * Displays a Youneeq Explorer widget on a page, which allows site users to customize recommendations.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.0
 */
class Yqr_Widget_Explorer extends Yqr_Widget_Base {

	/**
	 * Creates a new Youneeq explorer widget object.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		parent::__construct( 'youneeq_explorer', _x( 'Youneeq Explorer', 'Title of Youneeq explorer widget', 'youneeq-panel' ), [
			'description' => __( 'Allows users to customize Youneeq recommendation results.', 'youneeq-panel' )
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
		$attrs = [ 'id' => $this->get_field_id( 'form' ) ];

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
			apply_filters( 'yqr_widget_explorer', $attrs, $instance, $args )
		);
		echo $args[ 'after_widget' ];
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
			'title' => ''
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

    /**
     * Outputs an explorer element.
     *
     * @since 3.0.0
     *
     * @param string[] $attrs Associative array of HTML attributes to output.
     */
    public static function display( $attrs = null ) {
		self::enqueue_ui( Yqr_Main::settings( 'use_jqueryui_css' ) );
		$id = $attrs && isset( $attrs['id'] ) ? $attrs['id'] : 'yq-explorer';

		?>
		<div<?=Yqr_Lib::build_attr_string( $attrs )?>>
			<div class="yq-explorer-cats">
				<h3 class="yq-explorer-section-title"><?=esc_html__( 'Categories', 'youneeq-panel' )?></h3>
				<?=self::display_category_list( $id )?>
			</div>
			<div class="yq-explorer-doms">
				<h3 class="yq-explorer-section-title"><?=esc_html__( 'Sites', 'youneeq-panel' )?></h3>
				<?=self::display_domain_list( $id )?>
			</div>
			<div class="yq-explorer-dates">
				<?=self::display_date_picker()?>
			</div>
		</div>
		<?php
		self::display_script( $attrs );
	}

    /**
     * Enqueues explorer scripts and styles.
     *
     * @since 3.0.0
     *
     * @param bool $include_jqui_css     Determines if jQuery UI CSS files should be enqueued.
     * @param bool $include_explorer_css Determines if explorer CSS files should be enqueued.
     */
	public static function enqueue_ui( $include_jqui_css = true, $include_explorer_css = true ) {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'modernizr-inputtypes' );
        wp_enqueue_script( 'youneeq-date-fix' );

        if ( $include_jqui_css ) {
            wp_enqueue_style( 'jquery-ui-datepicker-yqstyle' );
        }
        if ( $include_explorer_css ) {
            wp_enqueue_style( 'youneeq-explorer-style' );
        }
	}

    /**
     * Displays explorer control script.
     *
     * @since 3.0.0
     *
     * @param array $attrs HTML attributes for the main explorer element.
     */
	private static function display_script( $attrs ) {
		$id = $attrs && isset( $attrs['id'] ) ? $attrs['id'] : '';
		$classes = $attrs && isset( $attrs['class'] ) ? explode( ' ', trim( $attrs['class'] ) ) : [];
		$selector = esc_js( $id ? '#' . $id : ( count( $classes ) ? '.' . $classes[0] : '' ) );

		if ( $selector ) {
			?>
			<script type="text/javascript">
				jQuery( function( $ ) {
					var $form = $( '<?=$selector?>' ),
						timer = 0;

					$form.on( 'change', '.yq-explorer-cats input, .yq-explorer-doms input', function( event ) {
						event.preventDefault();
						start_refresh( 2000 );
					})
					.on( 'input change', '.yq-explorer-dates input', function( event ) {
						event.preventDefault();
						start_refresh( 0 );
					});

					function start_refresh( delay ) {
						window.clearTimeout( timer );

						if ( delay ) {
							timer = window.setTimeout( do_refresh, delay );
						}
						else {
							do_refresh();
						}
					}

					function do_refresh() {
						for ( var i = 0, count = YouneeqHandler.instances.length; i < count; i++ ) {
							YouneeqHandler.instances[ i ].refresh( [ 'explorer' ] );
						}
					}

					yq_date_fix();
				});

				function yq_explorer_options( handler ) {
					var options = {},
						cats = jQuery( '<?=$selector?> .yq-explorer-cats input:checked' )
							.map( function( i, e ) {
								return jQuery( e ).val();
							}).get(),
						doms = jQuery( '<?=$selector?> .yq-explorer-doms input:checked' )
							.map( function( i, e ) {
								return jQuery( e ).val();
							}).get(),
						date_start = jQuery( '<?=$selector?> .yq-explorer-date-start' ).val(),
						date_end = jQuery( '<?=$selector?> .yq-explorer-date-end' ).val(),
						suggest_options = jQuery( handler.box ).data( 'yqSuggestOptions' ),
						added_strict = false;

					if ( cats.length ) {
						options.categories = cats;
					}
					if ( doms.length ) {
						options.domains = doms;
					}
					if ( date_start || date_end ) {
						var date_suffix = 'T00:00:00',
							offset = yq_date_fix_l10n.tz_offset,
							offset_hour = 0;

						if ( offset < 0 ) {
							date_suffix += '-';
							offset *= -1;
						}
						else {
							date_suffix += '+';
						}

						offset_hour = Math.floor( offset );
						offset = ( offset - offset_hour ) * 60;
						date_suffix += ( offset_hour < 10 ? '0' : '' ) + offset_hour + ':' +
							( offset < 10 ? '0' : '' ) + offset;

						if ( date_start ) {
							try {
								options.date_start = new Date( date_start + date_suffix ).toISOString();
							}
							catch ( e ) {}
						}
						if ( date_end ) {
							try {
								options.date_end = new Date( date_end + date_suffix ).toISOString();
							}
							catch ( e ) {}
						}
					}

					suggest_options = suggest_options ? suggest_options.split( ',' ) : [];
					for ( var i = 0, count = suggest_options.length; i < count; i++ ) {
						if ( !suggest_options[ i ] ) {
							added_strict = true;
							suggest_options[ i ] = 'strict_categories';
							break;
						}
						else if ( suggest_options[ i ] == 'strict_categories' ) {
							added_strict = true;
							break;
						}
					}
					if ( !added_strict ) {
						suggest_options[ count ] = 'strict_categories';
					}
					options.options = suggest_options;

					return options;
				}
			</script>
			<?php
		}
	}

    /**
     * Creates a list of categories, with checkboxes.
     *
     * @since 3.0.0
     *
     * @return string                  Category list element.
     * @param  string          $id     Base element ID for the category list.
     * @param  int[]|null      $filter Array of category IDs that should be checked by default.
     * @param  \WP_Term[]|null $cats   Array of categories to display.
     *                                 If not included, all categories will be displayed.
     */
	private static function display_category_list( $id, $filter = null, $cats = null ) {
		$out_string = '<ul>';
		$filter = $filter ?: [];

		if ( $cats === null ) {
			$cats = get_categories( [ 'hide_empty' => 0, 'parent' => 0 ] );
		}

		foreach ( $cats as $cat ) {
			$checked = in_array( $cat->term_id, $filter );
            $children = get_categories( [ 'hide_empty' => 0, 'parent' => $cat->term_id ] );
			$cat_id = esc_attr( $id . '_cat-' . $cat->term_id );

			$out_string .= '<li><label for="' . $cat_id . '"><input id="' . $cat_id .
				'" type="checkbox" value="' . esc_attr( $cat->name ) . '" ' .
				( $checked ? 'checked ' : '' ) . '/> ' . esc_html( $cat->name ) . '</label>';

			if ( count( $children ) > 0 ) {
				$out_string .= self::display_category_list( $id, $filter, $children );
			}

			$out_string .= '</li>';
		}

		return $out_string . '</ul>';
	}

    /**
     * Creates a list of networked sites, with checkboxes.
     *
     * @since 3.0.0
     *
     * @return string                Site list element.
     * @param  string        $id     Base element ID for the site list.
     * @param  string[]|null $filter Array of domain names that should be checked by default.
     * @param  string[]      $doms   Array of sites to display.
     *                                 If not included, all sites will be displayed.
     */
	private static function display_domain_list( $id, $filter = null, $doms = null ) {
		$out_string = '<ul>';
		$filter = $filter ?: [];

		if ( $doms === null ) {
			$doms = Yqr_Main::settings( 'domains' );
		}

		foreach ( $doms as $dom_url => $dom_name ) {
			$checked = in_array( $dom_url, $filter );
			$dom_dname = $dom_name ?: $dom_url;
			$dom_id = esc_attr( $id . '_' . str_replace( '.', '_', $dom_url ) );

			$out_string .= '<li><label for="' . $dom_id . '"><input id="' . $dom_id .
				'" type="checkbox" value="' . esc_attr( $dom_url ) . '" ' .
				( $checked ? 'checked ' : '' ) . '/> ' . esc_html( $dom_dname ) . '</label></li>';
		}

		return $out_string . '</ul>';
	}

	/**
	 * Creates a date range picker.
	 *
	 * @since 3.0.0
	 *
	 * @return string                           Date range picker elements.
	 * @param  \DateTime|string|int $date_start Starting date.
	 * @param  \DateTime|string|int $date_end   Ending date.
	 */
	private static function display_date_picker( $date_start = null, $date_end = null) {
		$date_start = Yqr_Lib::format_date( $date_start, 'Y-m-d' );
		$date_end   = Yqr_Lib::format_date( $date_end,   'Y-m-d' );

		return '<h3 class="yq-explorer-section-title">' . esc_html__( 'Start Date', 'youneeq-panel' ) .
			'</h3><input type="date" class="yq-explorer-date-start" ' .
				( $date_start ? 'value="' . $date_start . '" ' : '' ) .
			'/><h3 class="yq-explorer-section-title">' . esc_html__( 'End Date', 'youneeq-panel' ) .
			'</h3><input type="date" class="yq-explorer-date-end" ' .
				( $date_end ? 'value="' . $date_end . '" ' : '' ) . '/>';
	}

}

/* end */