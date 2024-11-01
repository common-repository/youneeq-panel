<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * youneeq-panel: Yqr_Widget_Base class
 *
 * Base class for Youneeq widgets.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5 Renamed to Yqr_Widget_Base
 * @since   3.0.0
 */
abstract class Yqr_Widget_Base extends WP_Widget {

    /**
     * Temporary array of new values used when updating settings.
     *
     * @since 3.0.0
     * @var   array
     */
    protected $new_vals;

    /**
     * Retrieves widget settings and displays the configuration form.
     *
     * @since 3.0.0
     *
     * @return null
     * @param  array $instance Current settings.
     */
    public function form( $instance ) {
        $args = wp_parse_args( $instance, $this->get_default_args() );
        $this->display_form( $args );

        return null;
    }

    /**
     * Sets suggest JS function name in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_display_function( $attrs, $instance ) {
        if ( isset( $instance[ 'display_function' ] ) && $instance[ 'display_function' ] ) {
            $attrs[ 'data-yq-display-function' ] = $instance[ 'display_function' ];
        }

        return $attrs;
    }

    /**
     * Enqueues a user-selected Javascript file.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     */
    public static function add_js_file( $attrs ) {
        $js_id = Yqr_Main::settings( 'js_file' );

        if ( $js_id > 0 ) {
            if ( !wp_script_is( 'youneeq_widget_script_' . $js_id, 'registered' ) ) {
                $src = wp_get_attachment_url( $js_id );

                if ( $src ) {
                    wp_register_script( 'youneeq_widget_script_' . $js_id, $src );
                }
            }

            wp_enqueue_script( 'youneeq_widget_script_' . $js_id );
        }

        return $attrs;
    }

    /**
     * Sets recommendation in the element attribute array.
     *
     * @since 3.0.0
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_count( $attrs, $instance ) {
        if ( isset( $instance[ 'count' ] ) ) {
            $attrs[ 'data-yq-count' ] = $instance[ 'count' ];
        }

        return $attrs;
    }

    /**
     * Sets handler priority.
     *
     * @since 3.0.6
     *
     * @return string[]
     * @param  string[] $attrs    Array of HTML attributes.
     * @param  array    $instance Settings for this widget instance.
     */
    public static function add_priority( $attrs, $instance ) {
        if ( isset( $instance[ 'priority' ] ) ) {
            $attrs[ 'data-yq-priority' ] = $instance[ 'priority' ];
        }

        return $attrs;
    }

    /**
     * Returns default arguments array.
     *
     * @since 3.0.0
     *
     * @return array
     */
    abstract protected function get_default_args();

    /**
     * Displays the widget configuration form.
     *
     * @since 3.0.0
     *
     * @param array $args Widget settings.
     */
    abstract protected function display_form( $args );

    /**
     * Creates a single attribute form field.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  string         $name  Name of the attribute.
     * @param  string         $label User-friendly label for the field. Should be localized.
     * @param  mixed          $val   Existing value for this attribute.
     * @param  string         $type  Input type. Can be "checkbox", "radio", "text", "number", or "select".
     * @param  string[]|null  $data  Options used by select element.
     */
    protected function get_menu_field( $name, $label, $val = false, $type = 'checkbox', $data = null ) {
        $field_id = $this->get_field_id( $name );
        $field_name = $this->get_field_name( $name );
        $out_string = '';

        switch ( $type ) {
            case 'checkbox':
            case 'radio':
                $out_string .= '<input id="' . $field_id . ( $data ? '-' . esc_attr( $data ) : '' ) . '" type="' . $type . '" name="' . $field_name . '" value="' . ( $data ? esc_attr( $data ) : '1' ) . '"' . ( ( !$data && $val ) || ( $data && $val == $data ) ? ' checked' : '' ) . ' /> <label for="' . $field_id . ( $data ? '-' . esc_attr( $data ) : '' )  . '"> ' . esc_html( $label ) . ' </label>';
                break;
            case 'text':
                /* translators: %s is the label for a widget form field. */
                $out_string .= '<label for="' . $field_id . '"> ' . esc_html( sprintf( __( '%s:', 'youneeq-panel' ), $label ) ) . ' </label> <input id="' . $field_id . '" class="widefat" type="text" size="100" name="' . $field_name . '" value="' . esc_attr( $val ) . '" />';
                break;
            case 'number':
                /* translators: %s is the label for a widget form field. */
                $out_string .= '<label for="' . $field_id . '"> ' . esc_html( sprintf( __( '%s:', 'youneeq-panel' ), $label ) ) . ' </label> <input id="' . $field_id . '" class="widefat" type="number"' . ( $data && isset( $data[ 'min' ] ) ? ' min="' . intval( $data[ 'min' ] ) . '"' : '' ) . ( $data && isset( $data[ 'max' ] ) ? ' max="' . intval( $data[ 'max' ] ) . '"' : '' ) . ' name="' . $field_name . '" value="' . esc_attr( $val ) . '" />';
                break;
            case 'select':
                /* translators: %s is the label for a widget form field. */
                $out_string .= '<label for="' . $field_id . '"> ' . esc_html( sprintf( __( '%s:', 'youneeq-panel' ), $label ) ) . ' </label> <select id="' . $field_id . '" class="widefat" name="' . $field_name . '" size="1">';
                foreach ( $data as $option_id => $option_label ) {
                    $out_string .= '<option value="' . esc_attr( $option_id ) . '"' . ( $val == $option_id ? ' selected' : '' ) . '>' . esc_html( $option_label ) . '</option>';
                }
                $out_string .= '</select>';
                break;
        }

        return $out_string;
    }

    /**
     * Creates a category checkbox list.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  string $name  Base element ID for the category list.
     * @param  string $label User-friendly label for the list. Should be localized.
     * @param  int[]  $val   Array of category IDs that should be checked by default.
     */
    protected function get_category_menu( $name, $label, $val = [] ) {
        $id = $this->get_field_id( $name );

        /* translators: %s is the label for a widget form field. */
        return '<p class="yqr-options-box-label"> <label for="' . $id . '">' . esc_html( sprintf( __( '%s:', 'youneeq-panel' ), $label ) ) . '</label> </p>' . Yqr_Lib::display_category_menu( $id, $this->get_field_name( $name ), $val );
    }

    /**
     * Creates a domain checkbox list.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  string   $name  Base element ID for the domain list.
     * @param  string   $label User-friendly label for the list. Should be localized.
     * @param  string[] $val   Array of domain URLs that should be checked by default.
     */
    protected function get_domain_menu( $name, $label, $val = [] ) {
        $field_id = $this->get_field_id( $name );
        $field_name = $this->get_field_name( $name );
        $doms = Yqr_Main::settings( 'domains' );

        /* translators: %s is the label for a widget form field. */
        $out_string = '<p class="yqr-options-box-label"> <label for="' . $field_id . '">' . esc_html( sprintf( __( '%s:', 'youneeq-panel' ), $label ) ) . '</label> </p><ul id="' . $field_id . '" class="yqr-options-box-list">';

        foreach ( $doms as $url => $desc ) {
            $slug = str_replace( '.', '_', esc_attr( $url ) );
            $desc = $desc ?: $url;
            $checked = in_array( $url, $val );

            $out_string .= '<li><label for="' . $field_id . '_' . $slug . '"><input id="' . $field_id . '_' . $slug . '" name="' . $field_name . '[]" type="checkbox" value="' . esc_attr( $url ) . '" ' . ( $checked ? 'checked ' : '' ) . '/> ' . esc_html( $desc ) . '</label></li>';
        }

        return $out_string . '</ul>';
    }

    /**
     * Creates a date range picker.
     *
     * @since 3.0.0
     *
     * @return string
     * @param  string $name      Base element ID for the date range picker.
     * @param  string $label     User-friendly label for the date range picker.
     * @param  string $val_start Default value for the starting date.
     * @param  string $val_end   Default value for the ending date.
     */
    protected function get_date_menu( $name, $label, $val_start = '', $val_end = '' ) {
        $field_id = esc_attr( $this->get_field_id( $name . '_start' ) );

        /* translators: %s is the label for a widget form field. */
        return '<label class="widefat" for="' . $field_id . '">' . esc_html( sprintf( __( '%s:', 'youneeq-panel' ), $label ) ) . '</label> '
            /* translators: 1: Starting date element. 2: Ending date element. */
            . sprintf( esc_html__( '%1$s to %2$s', 'youneeq-panel' ), '<input id="' . $field_id . '" class="yqr-datepicker" name="' . esc_attr( $this->get_field_name( $name . '_start' ) ) . '" type="date" value="' . esc_attr( $val_start ) . '" />', '<input id="' . esc_attr( $this->get_field_id( $name . '_end' ) ) . '" class="yqr-datepicker" name="' . esc_attr( $this->get_field_name( $name . '_end' ) ) . '" type="date" value="' . esc_attr( $val_end ) . '" />' );
    }

    /**
     * Updates a boolean setting.
     *
     * @since 3.0.0
     *
     * @param string $name Name of the setting to evaluate.
     */
    protected function set_bool( $name ) {
        $this->new_vals[ $name ] = !empty( $this->new_vals[ $name ] );
    }

    /**
     * Updates an integer setting. Can also validate that the new integer fits within a number range.
     *
     * @since 3.0.0
     *
     * @param string $name Name of the setting to evaluate.
     * @param int    $def  Default setting value.
     * @param int    $min  Minimum possible value.
     * @param int    $max  Maximum possible value.
     */
    protected function set_int( $name, $def = 0, $min = null, $max = null ) {
        if ( empty( $this->new_vals[ $name ] ) ) {
            $this->new_vals[ $name ] = $def;
        }
        elseif ( $min != null && $max != null ) {
            $this->new_vals[ $name ] = Yqr_Lib::clamp( intval( $this->new_vals[ $name ] ), $min, $max );
        }
        elseif ( $min != null ) {
            $this->new_vals[ $name ] = max( intval( $this->new_vals[ $name ] ), $min );
        }
        elseif ( $max != null ) {
            $this->new_vals[ $name ] = min( intval( $this->new_vals[ $name ] ), $max );
        }
        else {
            $this->new_vals[ $name ] = intval( $this->new_vals[ $name ] );
        }
    }

    /**
     * Updates a string setting. New string will be sanitized before being saved.
     *
     * @since 3.0.0
     *
     * @param string $name Name of the setting to evaluate.
     * @param string $def  Default setting value.
     */
    protected function set_string( $name, $def = '' ) {
        $this->new_vals[ $name ] = !empty( $this->new_vals[ $name ] ) ? sanitize_text_field( $this->new_vals[ $name ] ) : $def;
    }

    /**
     * Updates a category list setting.
     *
     * @since 3.0.0
     *
     * @param string $name Name of the setting to evaluate.
     * @param int[]  $def  Default setting values.
     */
    protected function set_cats( $name, $def = [] ) {
        $this->new_vals[ $name ] = !empty( $this->new_vals[ $name ] ) ? array_map( 'intval', $this->new_vals[ $name ] ) : $def;
    }

    /**
     * Updates a domain list setting.
     *
     * @since 3.0.0
     *
     * @param string   $name Name of the setting to evaluate.
     * @param string[] $def  Default setting values.
     */
    protected function set_doms( $name, $def = [] ) {
        $this->new_vals[ $name ] = !empty( $this->new_vals[ $name ] ) ? array_map( 'sanitize_text_field', $this->new_vals[ $name ] ) : $def;
    }

    /**
     * Updates a date range setting.
     *
     * @since 3.0.0
     *
     * @param string $name      Base name of the settings to evaluate.
     * @param string $def_start Default starting date setting.
     * @param string $def_end   Default ending date setting.
     */
    protected function set_dates( $name, $def_start = '', $def_end = '' ) {
        $name_start = $name . '_start';
        $name_end = $name . '_end';

        $date_start = !empty( $this->new_vals[ $name_start ] ) && preg_match( '/' . Yqr_Lib::REGEX_ISO_8601 . '/', $this->new_vals[ $name_start ] ) ?: $def_start;
        $date_end = !empty( $this->new_vals[ $name_end ] ) && preg_match( '/' . Yqr_Lib::REGEX_ISO_8601 . '/', $this->new_vals[ $name_end ] ) ?: $def_end;

        if ( $date_start && $date_end && intval( str_replace( '-', '', $date_start ) ) > intval( str_replace( '-', '', $date_end ) ) ) {
            $date_end = $def_end;
        }

        $this->new_vals[ $name_start ] = $date_start;
        $this->new_vals[ $name_end ] = $date_end;
    }

}

/* end */