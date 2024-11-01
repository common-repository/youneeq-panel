=== Youneeq Recommendations ===
Contributors: youneeq, yqalex, markpierceyq
Tags: widget, recommendations, youneeq
Requires at least: 4.4.0
Tested up to: 5.0.3
Requires PHP: 5.4
Stable tag: 3.0.7
License: MIT
License URI: http://opensource.org/licenses/mit-license.html

Machine learning algorithms that increase user engagement levels and revenue. Real time recommendations based on individual behaviors.

== Description ==

Integrates Youneeq's industry-leading recommendation and search engines into your site. With Youneeq's recommendations, your users will see links to the best articles your site or network has to offer.

Youneeq's machine-learning algorithms are designed to impact your site's 'stickiness' in the following ways:

* Engagement: Users who are presented with personalized content will be more engaged with your site, leading to a decrease in bounce rates.
* Dwell time: Engaged users tend to have higher CTR's and pageviews/user, meaning that their average time-on-site increases.
* Consumption: When viewing personalized content, users tend to consume two to three times more, meaning more exposure for your material.
* Retention: When you engage with your users, they are more likely to return to you for content, which will in turn increase the effectiveness of Youneeq.

If you are a Youneeq customer, all you have to do is customize the Youneeq recommender's behavior in the YQ Settings page and place the widget on your site. To activate the Youneeq Recommendations plugin on your site, please contact us at 1.866.515.0110 ext 101 or [wordpress@youneeq.ca](mailto:wordpress@youneeq.ca).

[youtube https://www.youtube.com/watch?v=hyEMFNtVxfA]
[youtube https://www.youtube.com/watch?v=xgTyEhz2v0g]

== Installation ==

1. In order to receive Youneeq recommendation results on your site, you must have an existing service agreement. Please contact us at 1.866.515.0110 ext 101 or [wordpress@youneeq.ca](mailto:wordpress@youneeq.ca).
2. Youneeq Recommendations requires a PHP installation of version 5.4 or higher.
3. Add the Youneeq Recommender widget on the Widgets admin page. Alternatively, you may add a recommendation panel directly into the page template (see FAQ).
4. Configure the plugin on the Youneeq Settings admin page.

== Frequently Asked Questions ==

= How do I add a recommendation panel to a page template? =

Add the following PHP snippet to the template where you want the panel to appear:

`Yqr_Widget_Rec::display();`

The recommendation panel's behavior can be customized with options passed as an associative array in the first parameter:

`Yqr_Widget_Rec::display( [ 'count' => 6, 'display_function' => 'my_yq_display' ] );`

HTML attributes can be passed to the display function as an associative array in the second parameter:

`Yqr_Widget_Rec::display( [ 'count' => 6, 'display_function' => 'my_yq_display' ], [ 'class' => 'col-4', 'title' => 'Recommended Stories' ] );`

= How do I change the appearance of a recommendation panel? =

The Display Function widget option allows a user-defined function to override recommendation output.

In order to use a user-defined function, a Javascript function with the entered name must exist within the window object. The display function can take two arguments: the Youneeq response object, and a list of tags (an array of strings) for advanced implementations.

= How do I use Youneeq Search? =

Youneeq Search leverages Youneeq's recommendation system to provide better search results for posts and images. In order to use Search, it must first be enabled by a Youneeq employee.

A search page must first be created to handle displaying search results. This can be a WordPress Page; the shortcodes [yqsearchform] and [yqsearchresults] will output a search form and results display. Alternatively, search can be integrated directly into site templates.

The Youneeq Search widget can be added to a sidebar to provide a search form anywhere on the site that will be linked to the search page.

== Changelog ==

= 3.0.7 =
*Release Date: June 28, 2019*

* Enhancements:
    * Added support for custom post types.

= 3.0.6 =
*Release Date: February 5, 2019*

* Enhancements:
    * Added widget execution priority option.

* Bug Fixes:
    * Fixed handler object script loading.

= 3.0.5 =
*Release Date: July 30, 2018*

* Enhancements:
    * Improved infinite scroll options.

= 3.0.4 =
*Release Date: July 9, 2018*

* Enhancements:
    * Added new Google Analytics tracker option.

= 3.0.3 =
*Release Date: July 4, 2018*

* Enhancements:
    * Added infinite scroll option.

= 3.0.2 =
*Release Date: June 8, 2018*

* Bug Fixes:
    * Google Analytics bug fix.

= 3.0.1 =
*Release Date: June 6, 2018*

* Enhancements:
    * Added option to specify Google Analytics tracker.

= 3.0.0 =
*Release Date: May 8, 2018*

* Enhancements:
    * The Youneeq Panel plugin (now named Youneeq Recommendations) has been rewritten.
    * Now requires a minimum PHP version of 5.4 and WordPress version of 4.4.
    * Incorporated Youneeq Search features, for advanced site search.
    * Category, site, and date filter options are now widget-specific rather than being set on the plugin settings page.
    * Old layout system has been removed. Layouts can now be implemented directly as a custom Javascript function.
    * Support for Ajax-based display function. Requires custom PHP scripting.

= 2.6.9 =
*Release Date: August 3, 2017*

* Bug Fixes:
    * Google Analytics bug fix.

= 2.6.5 =
*Release Date: June 14, 2017*

* Bug Fixes:
    * Fixed bug on SSL-secured sites.

= 2.6.4 =
*Release Date: May 23rd, 2017*

* Bug Fixes:
    * Fixed bug with multiple page hits being reported on sites with multiple recommendation widgets.

= 2.6.3 =
*Release Date: April 18th, 2017*

* Enhancements:
    * Support deprecated implementations of Google Analytics tracking.

= 2.6.2 =
*Release Date: December 15th, 2016*

* Enhancements:
    * Implemented Google Analytics tracking support.

= 2.6.1 =
*Release Date: April 8th, 2016*

* Enhancements:
    * Implemented full classified ad support. Classified ad metadata can either be input by the user into the Youneeq classified area, or can be linked to an existing classified plugin.

= 2.6.0 =
*Release Date: January 13th, 2016*

* Enhancements:
    * Fallback articles are now retrieved through an Ajax request.
    * Added support for sponsored content. Posts can be tagged as sponsored individually or by category. The number of sponsored posts to recommend can be set separately.
    * Added basic support for classified ads. Posts can be tagged as classifieds in the same manner as sponsored content. Currently, classified-specific metadata cannot be collected.
    * Implemented Gigya user data collection. Requires the Gigya WordPress plugin to be activated and configured.

* Bug Fixes:
    * Fallback articles now have proper category associations.

= 2.5.1 =
*Release Date: October 26th, 2015*

* Enhancements:
    * Infinite scrolling option added.
    * Fallback articles will now be displayed if the Youneeq server fails to respond.
    * Advanced layouts (can use Javascript and HTML to create panel layout).

* Bug Fixes:
    * Fixed script caching bug.

= 2.5.0 =
*Release Date: October 7th, 2015*

* Enhancements:
    * Layouts can now be assigned to each panel widget, allowing for different panel styles on a single site.
    * More than two layouts can be created in the options page.
    * Post image can now be assigned separately for Youneeq observe requests.
    * Image preloading option added. If images are enabled in a layout and removing articles is allowed, images will be preloaded to minimize the appearance of "image loading" placeholders.
    * Domains and categories can now be blacklisted for selection in the Youneeq Explorer menu.
    * Strict category filter option added. Previously, the recommendation engine would prioritize articles in matching categories, but other articles could still be recommended if they were relevant enough. With strict category filtering enabled, no non-matching articles will be recommended.
    * An advanced option to use the Youneeq staging (experimental) API has been added. Since the staging API is being updated often and may become unstable, this option should be left disabled unless advised otherwise by a Youneeq employee.

* Bug Fixes:
    * Existing updater functionality was removed. No longer needed since the plugin is now available on the WordPress repository.
    * Beta update option was removed. Developer versions of the plugin can be downloaded directly from the Youneeq Panel page on the WordPress plugin site.
    * Plugin should now display a warning and fail to run if the PHP version is out of date. PHP version must be 5.3 or newer.

= 2.4.1 =
*Release Date: August 26th, 2015*

* Enhancements:
    * Plugin is now available through the WordPress plugin repository.
    * Some script and image resources are now included with the plugin instead of being retrieved from the Youneeq API server (required to conform with WordPress hosting guidelines).
    * Some client-side scripts refactored to allow better caching performance.
