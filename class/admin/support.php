<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * youneeq-panel: Yqr_Admin_Support class
 *
 * Displays the plugin support page.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5
 */
class Yqr_Admin_Support extends Yqr_Admin_Base {

    const SUBMIT_SUCCESS          = 1;
    const SUBMIT_FORMAT_ERROR     = 2;
    const SUBMIT_DELIVERY_ERROR   = 4;
    const SUBMIT_MAILSERVER_ERROR = 8;
    const SUPPORT_EMAIL_SENDER    = 'noreply@youneeq.ca';
    const SUPPORT_EMAIL_RECIPIENT = 'support@youneeq.ca';
    private $submit_result;

    /**
     * Creates a new support screen object.
     *
     * @since 3.0.5
     */
    public function __construct() {
        $this->submit_result = 0;

        parent::__construct( 'support', esc_html_x( 'Support', 'Browser page title of Youneeq support page', 'youneeq-panel' ), esc_html_x( 'Support', 'Displayed name of Youneeq support page', 'youneeq-panel' ) );
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
            $this->submit_result = $this->submit_request();
        }
    }

    /**
     * Gets Support help tabs.
     *
     * @since 3.0.5
     *
     * @return array Help tab contents.
     */
    protected function get_help_tabs() {
        return [[
            'id'      => 'yqr_support_help',
            'title'   => __( 'Help Tabs', 'youneeq-panel' ),
            'content' => '<p>'
                . __( 'Help topics are available on all Youneeq Panel admin pages, as well as the Widgets page.', 'youneeq-panel' )
                . '</p>'
        ]];
    }

    /**
     * Gets admin notices for the page.
     *
     * @since 3.0.5
     *
     * @return array Notice contents.
     */
    protected function get_notices() {
        if ( $this->submit_result ) {
            if ( $this->submit_result & self::SUBMIT_SUCCESS ) {
                return [[
                    'type'        => 'success',
                    'message'     => __( 'Support request sent.', 'youneeq-panel' ),
                    'dismissible' => true
                ]];
            }
            else {
                if ( $this->submit_result & self::SUBMIT_DELIVERY_ERROR ) {
                    if ( $this->submit_result & self::SUBMIT_MAILSERVER_ERROR ) {
                        return [[
                            'type'        => 'error',
                            'message'     => __( 'Delivery error - could not connect to the mailserver. Please verify that "SMTP" and "smtp_port" are set in php.ini and that the mailserver is running.', 'youneeq-panel' ),
                            'dismissible' => false
                        ]];
                    }
                    else {
                        return [[
                            'type'        => 'error',
                            'message'     => __( 'Delivery error - request could not be sent.', 'youneeq-panel' ),
                            'dismissible' => false
                        ]];
                    }
                }
                elseif ( $this->submit_result & self::SUBMIT_FORMAT_ERROR ) {
                    return [[
                        'type'        => 'error',
                        'message'     => __( 'Formatting error - please ensure all fields are filled out.', 'youneeq-panel' ),
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
     * Send a support request to Youneeq support staff.
     *
     * @since 3.0.5
     * @see   Yqr_Admin_Support::SUBMIT_SUCCESS          Flag returned on successful submit.
     * @see   Yqr_Admin_Support::SUBMIT_FORMAT_ERROR     Flag returned when incorrect formatting detected.
     * @see   Yqr_Admin_Support::SUBMIT_DELIVERY_ERROR   Flag retured if a delivery error occurred.
     * @see   Yqr_Admin_Support::SUBMIT_MAILSERVER_ERROR Flag returned if the mail server is not configured properly.
     *
     * @return int Bit flags representing submit result.
     */
    private function submit_request() {
        if ( isset( $_POST['subject'] ) && $_POST['subject'] &&
            isset( $_POST['body'] ) && $_POST['body'] ) {
            $subject = '[WordPress] ' . sanitize_text_field( $_POST['subject'] );
            $message = '(This support request was received from ' . get_bloginfo( 'wpurl' ) . ')' .
                PHP_EOL . PHP_EOL . sanitize_text_field( $_POST['body'] );
            $headers = 'From: ' . self::SUPPORT_EMAIL_SENDER;

            if ( @mail( self::SUPPORT_EMAIL_RECIPIENT, $subject, $message, $headers ) ) {
                return self::SUBMIT_SUCCESS;
            }
            else {
                $error = error_get_last()['message'];

                if ( strpos( $error, 'Failed to connect to mailserver' ) !== false ) {
                    $code = self::SUBMIT_DELIVERY_ERROR + self::SUBMIT_MAILSERVER_ERROR;
                }
                else {
                    $code = self::SUBMIT_DELIVERY_ERROR;
                }

                return $code;
            }
        }
        else {
            return self::SUBMIT_FORMAT_ERROR;
        }
    }

}

/* end */