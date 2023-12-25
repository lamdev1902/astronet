<?php
/*
	Plugin Name:	Remove CPT base
	Plugin URI:		https://www.paypal.me/jakubnovaksl
	Description:	Remove custom post type base slug from url
	Version:		6.3
	Author:			KubiQ
	Author URI:		https://kubiq.sk
	Text Domain:	remove_cpt_base
	Domain Path:	/languages
*/

class remove_cpt_base{

	var $rcptb_selected, $rcptb_selected_keys;

	static $instance = null;

	static public function init(){
		if( self::$instance == null ){
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct(){
		// load translations
		add_action( 'plugins_loaded', function(){
			load_plugin_textdomain( 'remove_cpt_base', FALSE, basename( __DIR__ ) . '/languages/' );
		});
		
		// load user settgins from database
		$this->rcptb_selected = get_option( 'rcptb_selected', array() );
		$this->rcptb_selected_keys = array_keys( $this->rcptb_selected );
		
		// render menu item
		add_action( 'admin_menu', function(){
			add_submenu_page(
				'options-general.php',
				__( 'Remove CPT base', 'remove_cpt_base' ),
				__( 'Remove CPT base', 'remove_cpt_base' ),
				'manage_options',
				basename( __FILE__ ),
				array( $this, 'admin_options_page' )
			);
		});
		
		// render Settings link in plugins listing
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links, $file ){
			array_unshift( $links, '<a href="options-general.php?page=' . basename( __FILE__ ) . '">' . __('Settings') . '</a>' );
			return $links;
		}, 10, 2 );
		
		// remove CPT base slug from URLs
		add_filter( 'post_type_link', array( $this, 'remove_slug' ), 10, 3 );
		
		// auto redirect old URLs to non-base versions
		add_action( 'template_redirect', function(){
			global $post;
			if( ! is_preview() && is_single() && is_object( $post ) && isset( $this->rcptb_selected[ $post->post_type ] ) ){
				$new_url = get_permalink();
				$real_url = $this->get_current_url();
				if( substr_count( $new_url, '/' ) != substr_count( $real_url, '/' ) && strstr( $real_url, $new_url ) == false ){
					remove_filter( 'post_type_link', array( $this, 'remove_slug' ), 10 );
					$old_url = get_permalink();
					add_filter( 'post_type_link', array( $this, 'remove_slug' ), 10, 3 );
					$fixed_url = str_replace( $old_url, $new_url, $real_url );
					wp_redirect( $fixed_url, 301 );
				}
			}
		}, 1 );

		// here the magic was born
		add_filter( 'request', function( $query_vars ){
			// echo '<pre>' . print_r( $query_vars, 1 ) . '</pre>';
			if( ! is_admin() && ! isset( $query_vars['post_type'] ) && ( ( isset( $query_vars['error'] ) && $query_vars['error'] == 404 ) || isset( $query_vars['pagename'] ) || isset( $query_vars['attachment'] ) || isset( $query_vars['name'] ) || isset( $query_vars['category_name'] ) ) ){
				$web_roots = array();
				$web_roots[] = site_url();
				if( site_url() != home_url() ){
					$web_roots[] = home_url();
				}
				// polylang fix
				if( function_exists('pll_home_url') ){
					if( site_url() != pll_home_url() ){
						$web_roots[] = pll_home_url();
					}
				}

				foreach( $web_roots as $web_root ){
					// get clean current URL path
					$path = $this->get_current_url();
					$path = str_replace( $web_root, '', $path );
					$path = trim( $path, '/' );

					// clean custom rewrite endpoints
					$path = explode( '/', $path );
					foreach( $path as $i => $path_part ){
						if( isset( $query_vars[ $path_part ] ) ){
							$path = array_slice( $path, 0, $i );
							break;
						}
					}
					$path = implode( '/', $path );

					// test for posts
					$post_data = get_page_by_path( $path, OBJECT, 'post' );
					if( ! ( $post_data instanceof WP_Post ) ){
						// echo '#1<br>';
						// test for pages
						$post_data = get_page_by_path( $path );
						if( ! is_object( $post_data ) ){
							// echo '#2<br>';
							// test for selected CPTs
							$post_data = get_page_by_path( $path, OBJECT, $this->rcptb_selected_keys );
							if( is_object( $post_data ) ){
								// echo '#3<br>';
								// maybe name with ancestors is needed
								$post_name = $post_data->post_name;
								if( $this->rcptb_selected[ $post_data->post_type ] == 1 ){
									// echo '#4<br>';
									$ancestors = get_post_ancestors( $post_data->ID );
									foreach( $ancestors as $ancestor ){
										$post_name = get_post_field( 'post_name', $ancestor ) . '/' . $post_name;
									}
								}
								unset( $query_vars['error'] );
								unset( $query_vars['pagename'] );
								unset( $query_vars['attachment'] );
								unset( $query_vars['category_name'] );
								$query_vars['page'] = '';
								$query_vars['name'] = $path;
								$query_vars['post_type'] = $post_data->post_type;
								$query_vars[ $post_data->post_type ] = $path;
								break;
							}else{
								// echo '#5<br>';
								// deeper matching
								global $wp_rewrite;
								// test all selected CPTs
								foreach( $this->rcptb_selected_keys as $post_type ){
									// get CPT slug and its length
									$query_var = get_post_type_object( $post_type )->query_var;
									// test all rewrite rules
									foreach( $wp_rewrite->rules as $pattern => $rewrite ){
										// test only rules for this CPT
										if( strpos( $pattern, $query_var ) !== false ){
											// echo '#6<br>';
											if( strpos( $pattern, '(' . $query_var . ')' ) === false ){
												// echo '#7<br>';
												preg_match_all( '#' . $pattern . '#', '/' . $query_var . '/' . $path, $matches, PREG_SET_ORDER );
											}else{
												// echo '#8<br>';
												preg_match_all( '#' . $pattern . '#', $query_var . '/' . $path, $matches, PREG_SET_ORDER );
											}

											if( count( $matches ) !== 0 && isset( $matches[0] ) ){
												// echo '#9<br>';
												// build URL query array
												$rewrite = str_replace( 'index.php?', '', $rewrite );
												parse_str( $rewrite, $url_query );
												foreach( $url_query as $key => $value ){
													$value = (int)str_replace( array( '$matches[', ']' ), '', $value );
													if( isset( $matches[0][ $value ] ) ){
														$value = $matches[0][ $value ];
														$url_query[ $key ] = $value;
													}
												}

												// test new path for selected CPTs
												if( isset( $url_query[ $query_var ] ) ){
													// echo '#10<br>';
													$post_data = get_page_by_path( '/' . $url_query[ $query_var ], OBJECT, $this->rcptb_selected_keys );
													if( is_object( $post_data ) ){
														// echo '#11<br>';
														unset( $query_vars['error'] );
														unset( $query_vars['pagename'] );
														unset( $query_vars['attachment'] );
														unset( $query_vars['category_name'] );
														$query_vars['page'] = '';
														$query_vars['name'] = $path;
														$query_vars['post_type'] = $post_data->post_type;
														$query_vars[ $post_data->post_type ] = $path;
														// solve custom rewrites, pagination, etc.
														foreach( $url_query as $key => $value ){
															if( $key != 'post_type' && substr( $value, 0, 8 ) != '$matches' ){
																$query_vars[ $key ] = $value;
															}
														}
														break 3;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				// echo '<pre>' . print_r( $query_vars, 1 ) . '</pre>';
				// exit();
			}
			return $query_vars;
		});
	}

	// render admin settgins page
	function admin_options_page(){
		global $wp_post_types; ?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Remove base slug from url for these custom post types:', 'remove_cpt_base' ) ?></h2><?php
			if( isset( $_POST['settings_nonce'] ) && check_admin_referer( 'save_these_settings_' . get_current_user_id(), 'settings_nonce' ) ){
				if( ! isset( $_POST['rcptb_alternation'] ) || ! is_array( $_POST['rcptb_alternation'] ) ){
					$alternation = array();
				}else{
					$alternation = $_POST['rcptb_alternation'];
				}

				if( ! isset( $_POST['rcptb_selected'] ) || ! is_array( $_POST['rcptb_selected'] ) ){
					$this->rcptb_selected = array();
				}else{
					$this->rcptb_selected = $_POST['rcptb_selected'];
				}

				foreach( $this->rcptb_selected as $post_type => $active ){
					if( isset( $wp_post_types[ $post_type ] ) ){
						$this->rcptb_selected[ $post_type ] = isset( $alternation[ $post_type ] ) ? 1 : 0;
					}else{
						unset( $this->rcptb_selected[ $post_type ] );
					}
				}

				$this->rcptb_selected_keys = array_keys( $this->rcptb_selected );

				update_option( 'rcptb_selected', $this->rcptb_selected, 'no' );
				echo '<div class="below-h2 updated"><p>' . __( 'Settings saved.' ) . '</p></div>';
				flush_rewrite_rules();
			} ?>
			<br>
			<form method="POST" action="">
				<?php wp_nonce_field( 'save_these_settings_' . get_current_user_id(), 'settings_nonce' ) ?>
				<table class="widefat" style="width:auto">
					<tbody><?php
						foreach( $wp_post_types as $type => $custom_post ){
							if( $custom_post->_builtin == false ){ ?>
								<tr>
									<td>
										<label>
											<input type="checkbox" name="rcptb_selected[<?php echo esc_attr( $custom_post->name ) ?>]" value="1" <?php echo isset( $this->rcptb_selected[ $custom_post->name ] ) ? 'checked' : '' ?>>
											<?php echo esc_html( $custom_post->label ) ?> (<?php echo esc_html( $custom_post->name ) ?>)
										</label>
									</td>
									<td>
										<label>
											<input type="checkbox" name="rcptb_alternation[<?php echo esc_attr( $custom_post->name ) ?>]" value="1" <?php echo isset( $this->rcptb_selected[ $custom_post->name ] ) && $this->rcptb_selected[ $custom_post->name ] == 1 ? 'checked' : '' ?>>
											<?php esc_html_e( 'alternation', 'remove_cpt_base' ) ?>
										</label>
									</td>
								</tr><?php
							}
						} ?>
					</tbody>
				</table>

				<p><?php esc_html_e( '* if your custom post type children return error 404, then try alternation mode', 'remove_cpt_base' ) ?></p>
				<hr>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save') ?>">
				</p>
			</form>
		</div><?php
	}

	function remove_slug( $permalink, $post, $leavename ){
		global $wp_post_types;
		foreach( $wp_post_types as $type => $custom_post ){
			if( $custom_post->_builtin == false && $type == $post->post_type && isset( $this->rcptb_selected[ $custom_post->name ] ) ){
				$custom_post->rewrite['slug'] = trim( $custom_post->rewrite['slug'], '/' );
				$permalink = str_replace( '/' . $custom_post->rewrite['slug'] . '/', '/', $permalink );
			}
		}
		return $permalink;
	}

	function get_current_url(){
		$REQUEST_URI = strtok( $_SERVER['REQUEST_URI'], '?' );
		$real_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';
		$real_url .= $_SERVER['SERVER_NAME'] . $REQUEST_URI;
		return $real_url;
	}
}

// clean database
function rcptb_remove_plugin_options(){
	delete_option('rcptb_selected');
}

// load as soon as possible
add_action( 'init', array( 'remove_cpt_base', 'init' ), 99 );
// resave permalinks on activation
register_activation_hook( __FILE__, 'flush_rewrite_rules' );
// resave permalinks on deactivation
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
// remove plugin database records on uninstall
register_uninstall_hook( __FILE__, 'rcptb_remove_plugin_options' );