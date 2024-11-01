<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Youneeq admin support page template.
 *
 * Displays the plugin support page.
 *
 * @package Youneeq\WP_Recs
 * @since   3.0.5
 */
?>
<div class="wrap yqr-wrap">
    <?php $this->display_header(); ?>
    <p>
        <?= esc_html__( 'Help topics for each page can be viewed by clicking the Help tab in the top right corner of the page.', 'youneeq-panel' ) ?>
    </p>

    <h2 class="title">
        <?= esc_html__( 'Frequently Asked Questions', 'youneeq-panel' ) ?>
    </h2>
    <h4>
        <?= esc_html__( 'How do I add a recommendation panel to a page template?', 'youneeq-panel' ) ?>
    </h4>
    <p>
        <?= esc_html__( 'Add the following PHP snippet to the template where you want the panel to appear:', 'youneeq-panel' ) ?>
    </p>
    <p>
        <code>Yqr_Widget_Rec::display();</code>
    </p>
    <p>
        <?= esc_html__( 'The recommendation panel\'s behavior can be customized with options passed as an associative array in the first parameter:', 'youneeq-panel' ) ?>
    </p>
    <p>
        <code>Yqr_Widget_Rec::display( [ 'count' => 6, 'display_function' => 'my_yq_display' ] );</code>
    </p>
    <p>
        <?= esc_html__( 'HTML attributes can be passed to the display function as an associative array in the second parameter:', 'youneeq-panel' ) ?>
    </p>
    <p>
        <code>Yqr_Widget_Rec::display( [ 'count' => 6, 'display_function' => 'my_yq_display' ], [ 'class' => 'col-4', 'title' => '<?= esc_js( __( 'Recommended Stories', 'youneeq-panel' ) ) ?>' ] );</code>
    </p>
    <p>
        <?= esc_html__( 'Options that may be passed to the recommendation panel include:', 'youneeq-panel' ) ?>
    </p>
    <ul>
        <li>
            count
        </li>
        <li>
            display_function
        </li>
        <li>
            categories
        </li>
        <li>
            cross_site
        </li>
        <li>
            domains
        </li>
    </ul>

    <h4>
        <?= esc_html__( 'How do I change the appearance of a recommendation panel?', 'youneeq-panel' ) ?>
    </h4>
    <p>
        <?= esc_html__( 'The Display Function widget option allows a user-defined function to override recommendation output.', 'youneeq-panel' ) ?>
    </p>
    <p>
        <?= esc_html__( 'In order to use a user-defined function, a Javascript function with the entered name must exist within the window object. The display function can take two arguments: the Youneeq response object, and a list of tags (an array of strings) for advanced implementations.', 'youneeq-panel' ) ?>
    </p>

    <h4>
        <?= esc_html__( 'How do I use Youneeq Search?', 'youneeq-panel' ) ?>
    </h4>
    <p>
        <?= esc_html__( 'Youneeq Search leverages Youneeq\'s recommendation system to provide better search results for posts and images. In order to use Search, it must first be enabled by a Youneeq employee.', 'youneeq-panel' ) ?>
    </p>
    <p>
        <?= esc_html__( 'A search page must first be created to handle displaying search results. This can be a WordPress Page; the shortcodes [yqsearchform] and [yqsearchresults] will output a search form and results display. Alternatively, search can be integrated directly into site templates.', 'youneeq-panel' ) ?>
    </p>
    <p>
        <?= esc_html__( 'The Youneeq Search widget can be added to a sidebar to provide a search form anywhere on the site that will be linked to the search page.', 'youneeq-panel' ) ?>
    </p>

    <form action="" method="POST">
        <input type="hidden" name="_yqr_nonce" value="<?= $this->get_nonce() ?>" required />
        <h2>
            <?= esc_html__( 'Contact Us', 'youneeq-panel' ) ?>
        </h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="support_subject">
                            <?= esc_html__( 'Subject', 'youneeq-panel' ) ?>
                        </label>
                    </th>
                    <td>
                        <input id="support_subject" class="regular-text" name="subject" type="text" autocomplete="off" required />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="support_message">
                            <?= esc_html__( 'Message', 'youneeq-panel' ) ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="support_message" class="large-text" name="body" autocomplete="off" rows="10" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <button class="button-secondary" type="submit" name="action" value="submit">
                            <?= esc_html__( 'Submit Support Request', 'youneeq-panel' ) ?>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
