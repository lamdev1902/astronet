=== Remove CPT base ===
Contributors: kubiq
Donate link: https://www.paypal.me/jakubnovaksl
Tags: permalink, custom post type, base, slug, remove
Requires at least: 3.0
Requires PHP: 5.6
Tested up to: 6.2
Stable tag: 6.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Remove custom post type base slug from url

== Description ==

Remove custom post type base slug from url

<ul>
	<li>possibility to select specific custom post type(s)</li>
	<li>auto redirect old slugs to no-base slugs</li>
</ul>

### Yoast SEO specifics

If you're using Yoast SEO plugin, after you change something in the Remove CPT base plugin, you should deactivate Yoast SEO and activate it back again to refresh its yoast_indexable database table, so it will generate correct og:url, canonical url and JSON-LD urls.

== Installation ==

1. Upload `remove-cpt-base` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 6.3 =
* fix deprecated notice for PHP 8.2

= 6.2 =
* tested on WP 6.2
* added Yoast SEO warning in the description

= 6.1 =
* tested on WP 6.1
* use request hook instead of pre_get_posts to fix 404 in console

= 6.0 =
* fix for WPML hierarchical translated posts

= 5.9 =
* added nonce and security checks

= 5.8 =
* tested on WP 5.9

= 5.7 =
* tested on WP 5.5
* minor fix

= 5.6 =
* tested again with WPML, Polylang and Custom Post Type Permalinks and fixed

= 5.5 =
* tested on WP 5.5
* another fix for Custom Post Type Permalinks plugin

= 5.4 =
* enable previews for CPTs without base

= 5.3 =
* make it works with WPML
* make it works with Polylang
* make it works with Custom Post Type Permalinks plugin

= 5.2 =
* tested on WP 5.4

= 5.1 =
* removed auto-prevent slug duplicates
* removed debug mode
* removed remove_cpt_base_skip filter
* use default WP function instead of custom
* make it works for custom rewrite slugs
* prioritize page and post like WP does

= 5.0 =
* YOU HAVE TO SAVE YOUR SETTINGS AGAIN, because:
* added alternation option for each post type separately
* added debug mode

= 4.8 =
* fix alternative CPT children solving for nested children

= 4.7 =
* alternative CPT children solving

= 4.6 =
* fix server port redirect

= 4.5 =
* make it works for WP installations in directory

= 4.4 =
* minor changes

= 4.3 =
* fix for some endpoints and make sure post is not interpreted as attachment

= 4.2 =
* fix for hierarchical CPTs on some servers

= 4.1 =
* make it works for posts interpreted like category by WP

= 4.0 =
* tested on WP 5.2
* make it works for hierarchical post types and different permalink structures
* going back to 'pre_get_posts'
* optimize generating slug for duplicate names

= 3.3 =
* change HTTP code from 404 to 200

= 3.2 =
* fix for query strings

= 3.1 =
* add custom endpoint rewrites support

= 3.0 =
* stop using complicated 'pre_get_posts' and handle 404 instead

= 2.3 =
* tested on WP 5.0

= 2.2 =
* fix 404

= 2.1 =
* fix redirect loop in WPML and WooCommerce

= 2.0 =
* stop using .htaccess rules

= 1.2 =
* auto init after permalinks updated

= 1.1 =
* add uninstall hook
* add duplicate slug check
* minor updates

= 1.0 =
* First version