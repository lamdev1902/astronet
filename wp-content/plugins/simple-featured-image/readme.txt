=== Simple Featured Image ===
Contributors: jdegayojr
Donate link: https://www.paypal.me/jdegayojr
Tags: Simple Featured Image, Taxonomy Featured Image, Featured Image, Custom Featured Image, Dynamic Featured Image
Tested up to: 6.0
Stable tag: 1.3.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This is a simple plugin allows you to set Taxonomy featured image using media library. Also supports customs post types.

== Description ==

This plugin will allows you to add featured image to your custom and default post type Taxonomy.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/simple-featured-image` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Plugin Name screen to configure the plugin
4. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)


== Frequently Asked Questions ==

= How to activate featured image in Custom and Default post type Taxonomy? =

Login to your WordPress admin dashboard, Go to Settings > Simple Featured Image. Then, select your desired taxonomy to apply the featured image.

= What Custom Post Taxonomy this plugin don't apply? =

This plugin will not apply on Woocommerce, Woocommerce custom post type taxonomy has its own featured image.

= Do I really need this plugin? =

Not necessarily, But if you want to enhance the look of your taxonomy page, then this plugin is the right one for you.

= How to display taxonomy featured image in the front end using php code? =

You can display the Taxonomy Featured Image in the front end using php function.

- wpsfi_display_image( $termID, $size = "medium", $class = '', $width = '', $height ='' )

Parameters:
* $termID - (Integer) Taxonomy termID
* $size - (String) Image size
* $class - (String) Add custom class 
* $width - (Integer) Width in px.
* $height - (Integer) Height in px

= Does this plugin have available shortcode? =

Yes, It has available shortcodes.

- [wpsfi_image]

Parameters:
* term_id - (Integer) Taxonomy termID
* size - (String) Image size
* class - (String) Add custom class 
* width - (Integer) Width in px.
* height - (Integer) Height in px

- [wpsfi_slider]

Parameters:
* taxonomy - (String) Taxonomy Slug. Default "category"
* hide_empty - (String) Exclude in the list empty Taxonomy ( true, false ). Default "false"
* slideshow - (String) Animate slider automatically ( true, false ). Default "true"
* slideshow_speed - (Integer) Set the speed of the slideshow cycling, in milliseconds. Default 7000
* animation - (String)  Select your animation type, "fade" or "slide". Default "Slide"
* animation_speed - (Integer) Set the speed of animations, in milliseconds. Default 600
* animation_loop - (String) Should the animation loop ( true, false ). Default "false"
* animation_title - (String) Apply animation to Taxonomy name, User Animate It classes. Default "bounceInDown"
* item_width - (Integer) Box-model width of individual carousel items, including horizontal borders and padding. Default 600
* item_margin - (Integer) Margin between carousel items. Default 2
* min_items - (Integer) Minimum number of carousel items that should be visible. Default 1
* max_items - (Integer) Maxmimum number of carousel items that should be visible.
* direction - (String) Select the sliding direction, "horizontal" or "vertical". Default "horizontal"
* mousewheel - (String) Allows slider navigating via mousewheel ( true, false ). Default "false"
* control_nav - (String) Create navigation for paging control of each slide. ( true, false ). Default "True"
* direction_nav - (String) Create navigation for previous/next navigation. (true/false). Default "True"

= Does this plugin have available Widgets? =

Yes, This plugin provides two widgets.

* WPSFI Taxonomies - Display terms under selected taxonomy.
* WPSFI Slider - Display Taxonomy Slider

== Screenshots ==

1. Display Featured Image thubmnail in table column
2. Add Featured Image
3. Set taxonomy to display featured image
4. Add Simple Featured Image widget
5. Simple Featured Image widget output 
6. Widget Slider output
7. Shortcode Slider output
8. Slider Widget screenshot

== Changelog ==

= 1.3.1 =
- Add new setting option to enable Open Graph meta.
- Sanitize Open Graph meta.

= 1.3.0 =
- Fixed errors on settings on add a featured image to a post category.
- Add Open Graph image for single, category and archive page.

= 1.2.4 =
- Fixed update errors on settings not save.
- Fixed typo error

= 1.2.2 =
- Fixed update errors

= 1.2.1 =
- Fixed langauge translation error
- Display featured image for the post type table by default.
- Checked compatibility issue for wordpress version 5.1
- Allow custom post type to display featured image.

= 1.2.0 =
- Add new widget and shorcode for the Taxonomy Slider.

= 1.1.0 =
- Add widget to display taxonomy list.
- Fixed plugin tags

= 1.0 =
- Initial release