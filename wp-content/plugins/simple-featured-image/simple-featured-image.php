<?php
/**
 * Plugin Name: Simple Featured Image
 * Plugin URI: http://jonathandegayojr.com/
 * Description: Adds featured image to Post and Custom Post Type taxonomies.
 * Version: 1.3.1
 * Author: <a href="http://jonathandegayojr.com/">Jonathan Degayo Jr.</a>
 * Text Domain: wp-simple-featured-image
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages/
 */
/*
Simple Featured Image is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Simple Featured Image is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Simple Featured Image. If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
//* Defined constant
define( 'WPSFI_TEXTDOMAIN', 'wp-simple-featured-image' );
define( 'WPSFI_VERSION', '1.3.1' );
define( 'WPSFI_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSFI_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSFI_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'WPSFI_FILE', plugin_basename(__FILE__) );

require_once(WPSFI_PATH.'admin/includes/functions.php');
require_once(WPSFI_PATH.'admin/classes/class-simple-featured-image-scripts.php');
require_once(WPSFI_PATH.'admin/classes/class-simple-featured-image.php');
require_once(WPSFI_PATH.'admin/classes/class-taxonomy-widgets.php');
require_once(WPSFI_PATH.'admin/classes/class-open-graph-public.php');
require_once(WPSFI_PATH.'admin/classes/class-taxonomy-slider-widgets.php');

new WP_Simple_Featured_Image;
new WP_Simple_Featured_Open_Graph;
new WPSFI_Taxonomy_Widget;
new WPSFI_Taxonomy_Slider_Widget;
