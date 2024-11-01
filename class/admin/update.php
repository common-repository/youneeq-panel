<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * youneeq-panel: Yqr_Admin_Update class
 *
 * Base admin settings page class for the Youneeq Recommendations plugin.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5
 */
abstract class Yqr_Admin_Update extends Yqr_Admin_Base {

    /**
     * Flag set if update is successful.
     *
     * @since 3.0.5
     * @var   int
     */
    const UPDATE_SUCCESS = 1;
    /**
     * Flag set if settings were changed on update.
     *
     * @since 3.0.5
     * @var   int
     */
    const UPDATE_CHANGED = 2;
    /**
     * Flag set if a database error occurred.
     *
     * @since 3.0.5
     * @var   int
     */
    const UPDATE_DB_ERROR = 4;
    /**
     * Flag set if an authentication error occurred.
     *
     * @since 3.0.5
     * @var   int
     */
    const UPDATE_AUTH_ERROR = 8;

    /**
     * Temporary array of new values used when updating settings.
     *
     * @since 3.0.5
     * @var   array
     */
    protected $new_options;
    /**
     * Temporary array of old values used when updating settings.
     *
     * @since 3.0.5
     * @var   array
     */
    protected $old_options;
    /**
     * Update result flags.
     *
     * @since 3.0.5
     * @var   int
     * @see   Yqr_Admin_Update::UPDATE_SUCCESS    Flag returned on successful update.
     * @see   Yqr_Admin_Update::UPDATE_CHANGED    Flag returned when settings were changed.
     * @see   Yqr_Admin_Update::UPDATE_DB_ERROR   Flag retured if a database error occurred.
     * @see   Yqr_Admin_Update::UPDATE_AUTH_ERROR Flag returned if an authentication error occurred.
     */
    private $update_result;

    /**
     * Update individual setting values in $new_options.
     *
     * @since 3.0.5
     */
    abstract protected function set_update_vals();

    /**
     * Base contructor.
     *
     * @since 3.0.5
     */
    protected function __construct( $page_slug, $page_title, $page_name ) {
        $this->update_result = 0;

        parent::__construct( $page_slug, $page_title, $page_name );
    }

    /**
     * Registers WordPress actions for this page.
     *
     * @since 3.0.5
     */
    public function set_actions() {
        parent::set_actions();

        // Check for updates on admin settings menu.
        if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'submit' ) {
            $this->update_result = $this->update();
        }
    }

    /**
     * Gets admin notices for the page.
     *
     * @since 3.0.5
     *
     * @return array Notice contents.
     */
    protected function get_notices() {
        if ( $this->update_result ) {
            if ( $this->update_result & self::UPDATE_SUCCESS ) {
                if ( $this->update_result & self::UPDATE_CHANGED ) {
                    return [[
                        'type'        => 'success',
                        'message'     => __( 'Settings saved.', 'youneeq-panel' ),
                        'dismissible' => true
                    ]];
                }
                else {
                    return [[
                        'type'        => 'info',
                        'message'     => __( 'Settings were not changed.', 'youneeq-panel' ),
                        'dismissible' => true
                    ]];
                }
            }
            else {
                if ( $this->update_result & self::UPDATE_DB_ERROR ) {
                    return [[
                        'type'        => 'error',
                        'message'     => __( 'Database error - insert could not be completed.', 'youneeq-panel' ),
                        'dismissible' => false
                    ]];
                }
                elseif ( $this->update_result & self::UPDATE_AUTH_ERROR ) {
                    return [[
                        'type'        => 'error',
                        'message'     => __( 'Authentication error - page timed out.', 'youneeq-panel' ),
                        'dismissible' => false
                    ]];
                }
            }
        }
        else {
            return false;
        }
    }

    /**
     * Retrieves, sanitizes, and sets a boolean value from POST.
     *
     * @since 3.0.5
     *
     * @param string $field Name of setting to update.
     */
    protected function set_bool( $field ) {
        $this->new[ $field ] = isset( $_POST[ $field ] ) && $_POST[ $field ] != false && $_POST[ $field ] != '0';
    }

    /**
     * Retrieves, sanitizes, and sets an integer value from POST.
     *
     * @since 3.0.5
     *
     * @param string $field Name of setting to update.
     */
    protected function set_int( $field, $min = PHP_INT_MIN, $max = PHP_INT_MAX ) {
        if ( isset( $_POST[ $field ] ) ) {
            $this->new[ $field ] = Yqr_Lib::clamp( intval( $_POST[ $field ] ), $min, $max );
        }
        else {
            $this->new[ $field ] = 0;
        }
    }

    /**
     * Retrieves, sanitizes, and sets a string value from POST.
     *
     * @since 3.0.5
     *
     * @param string $field Name of setting to update.
     */
    protected function set_string( $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            $this->new[ $field ] = sanitize_text_field( $_POST[ $field ] );
        }
        else {
            $this->new[ $field ] = '';
        }
    }

    /**
     * Attempts to update plugin settings.
     *
     * @since 3.0.5
     * @see   Yqr_Admin_Update::UPDATE_SUCCESS    Flag returned on successful update.
     * @see   Yqr_Admin_Update::UPDATE_CHANGED    Flag returned when settings were changed.
     * @see   Yqr_Admin_Update::UPDATE_DB_ERROR   Flag retured if a database error occurred.
     * @see   Yqr_Admin_Update::UPDATE_AUTH_ERROR Flag returned if an authentication error occurred.
     *
     * @return int Bit flags representing update result.
     */
    private function update() {
        $status = 0;

        if ( isset( $_POST[ '_yqr_nonce' ] ) && $this->verify_nonce( $_POST[ '_yqr_nonce'] ) ) {
            $this->new = get_option( Yqr_Main::OPTION_NAME );
            $this->new = $this->new ? $this->new : [];
            $this->old = $this->new;

            $this->set_update_vals();

            if ( Yqr_Lib::array_comp( $this->new, $this->old ) ) {
                $status = self::UPDATE_SUCCESS;
            }
            elseif ( !update_option( Yqr_Main::OPTION_NAME, $this->new ) ) {
                $status = self::UPDATE_DB_ERROR;
            }
            else {
                $status = self::UPDATE_SUCCESS + self::UPDATE_CHANGED;
            }
        }
        else {
            $status = self::UPDATE_AUTH_ERROR;
        }

        return $status;
    }

}

/* end */