<?php

class GoogleSitemapGeneratorStandardBuilder {

	/**
	 * Creates a new GoogleSitemapGeneratorStandardBuilder instance
	 */
	public function __construct() {
		add_action("sm_build_index", array($this, "Index"), 10, 1);
		add_action("sm_build_content", array($this, "Content"), 10, 3);

		add_filter("sm_sitemap_for_post", array($this, "GetSitemapUrlForPost"), 10, 3);
	}

	/**
	 * Generates the content of the requested sitemap
	 *
	 * @param $gsg GoogleSitemapGenerator
	 * @param $type String The type of the sitemap
	 * @param $params String Parameters for the sitemap
	 */
	public function Content($gsg, $type, $params) {
		switch($type) {
			case "post":
				$this->BuildPosts($gsg, $params);
				break;
			case "publication":
				$this->BuildPostsNews($gsg, $params);
				break;
			case "shop":
				$this->BuildShop($gsg, $params);
				break;
			case "archives":
				$this->BuildArchives($gsg);
				break;
			case "authors":
				$this->BuildAuthors($gsg);
				break;
			case "tax":
				$this->BuildTaxonomies($gsg, $params);
				break;
			case "externals":
				$this->BuildExternals($gsg);
				break;
			case "misc":
				$this->BuildMisc($gsg);
				break;
			case "stories":
				$this->BuildStories($gsg, $params);
		}
	}
	public function BuildShop($gsg, $params) {
		$terms = get_terms( array(
		    'taxonomy'   => 'shop',
		    'hide_empty' => false,
		));
		if($terms && count($terms) > 0) {
			foreach($terms as $term) {
				$args = array(
					'post_type' => 'shop_post',
					'suppress_filters' => false,
					'numberposts' => 1,
					'orderby' => 'modified',
		    		'order' => 'DESC',
		    		'tax_query' => array(
		    			array(
		    				'taxonomy' => 'shop',
		    				'field' => 'id',
		    				'terms' => $term->term_id
		    			)
		    		)
				);
				$posts = get_posts($args);
				$datet = $gsg->GetTimestampFromMySql($posts[0]->post_modified_gmt && $posts[0]->post_modified_gmt != '0000-00-00 00:00:00'? $posts[0]->post_modified_gmt : $posts[0]->post_date_gmt);
				$permalink = get_term_link($term, $term->taxonomy);
				//$gsg->AddUrl(get_term_link($term, $term->taxonomy), '0000-00-00 00:00:00', $gsg->GetOption("cf_tags"), $gsg->GetOption("pr_tags"));
				$gsg->AddUrl($permalink,$datet,'','', $term->term_id, 'article');
			}
		}
		$args = array(
			'post_type' => 'shop_post',
			'suppress_filters' => false,
			'numberposts' => -1,
			'orderby' => 'modified',
    		'order' => 'DESC',
		);
		$posts = get_posts($args);
		if(($postCount = count($posts)) > 0) {
			foreach($posts as $post) {
				$permalink = get_permalink($post);
				$gsg->AddUrl($permalink,$gsg->GetTimestampFromMySql($post->post_modified_gmt && $post->post_modified_gmt != '0000-00-00 00:00:00'? $post->post_modified_gmt : $post->post_date_gmt),'','', $post->ID, 'article');
				unset($post);
			}
		}

		unset($posts);
	}
	public function BuildPostsNews($gsg, $params) {
		$args = array(
			'post_type' => get_field('sitemap_cpt','option'),
			'cat' => get_field('sitemap_cat','option'),
			'suppress_filters' => false,
			'numberposts' => get_field('sitemap_number', 'option'),
			'orderby' => 'publish_date',
    		'order' => 'DESC',
		);
		$posts = get_posts($args);
		if(($postCount = count($posts)) > 0) {
			foreach($posts as $post) {
				$permalink = get_permalink($post);
				$gsg->AddUrl($permalink,$gsg->GetTimestampFromMySql($post->post_date),'','', $post->ID, 'google-news');
				unset($post);
			}
		}

		unset($posts);
	}

	public function BuildStories($gsg, $params) {

		$webstories_plugin = WP_PLUGIN_DIR . '/web-stories';
		if(file_exists($webstories_plugin . '/includes/Story_Query.php')){
			require_once($webstories_plugin . '/includes/Story_Query.php');

			$level = 1;
			$pagen = 0;
			if(!$params) {
				$postType = $params;
			} else {
				$level = 2;
				$pagen = $params - 1;
			}
			$numpagi = 1000;
			$offset = $numpagi * $pagen;


			$stories = ( new Google\Web_Stories\Story_Query( [], []) )->get_stories();

			if(($storiesCount = count($stories)) > 0) {
				if($level == 1) {
					$tb = ceil($storiesCount/$numpagi);
					for($t=1; $t<= $tb; $t++) {
						$gsg->AddSitemap("stories", $t);
					}
				} else {
					if(file_exists($webstories_plugin . '/includes/Story_Post_Type.php')){
						require_once($webstories_plugin . '/includes/Story_Post_Type.php');

						$default_query_args = [
							'post_type'        => Google\Web_Stories\Story_Post_Type::POST_TYPE_SLUG,
							'posts_per_page'   => $numpagi,
							'post_status'      => 'publish',
							'suppress_filters' => false,
							'no_found_rows'    => true,
							'offset' 		   => $offset
						];
	
						$stories = ( new Google\Web_Stories\Story_Query( [], $default_query_args) )->get_stories();
						foreach($stories AS $story) {
							$permalink = get_permalink($story);
							if(
								!empty($permalink)
							) {
								//ADdd the URL to the sitemap
								$gsg->AddUrl(
									$permalink,
									$gsg->GetTimestampFromMySql($story->post_modified_gmt && $story->post_modified_gmt != '0000-00-00 00:00:00'? $story->post_modified_gmt : $story->post_date_gmt),'','', $story->ID, 'article');
							}
							unset($story);
						}
					}
					
				}
			}
		}

	}

	public function BuildPosts($gsg, $params) {
		$level = 1;
		$pagen = 0;
		if(!$params) {
			$postType = $params;
		} else {
			$level = 2;
			$pagen = $params - 1;
		}
		$numpagi = 1000;
		$offset = $numpagi * $pagen;
		$args = array(
			'post_type' => array('post','page','news','comparison','single_reviews','round_up','informational_posts','coupon','interactive-post','tool_post'),
			'suppress_filters' => false,
			'posts_per_page' => -1,
		);
		$posts = get_posts($args);
		$homePid = 0;
		$home = get_home_url();
		if('page' == get_option('show_on_front') && get_option('page_on_front')) {
			$pageOnFront = get_option('page_on_front');
			$p = get_post($pageOnFront);
			if($p) $homePid = $p->ID;
		}
		if(($postCount = count($posts)) > 0) {
			if($level == 1) {
				$tb = ceil($postCount/$numpagi);
				for($t=1; $t<= $tb; $t++) {
					$gsg->AddSitemap("post", $t);
				}
			} else {
				$args = array(
					'post_type' => array('post','page','news','comparison','single_reviews','round_up','informational_posts','coupon','interactive-post','tool_post'),
					'suppress_filters' => false,
					'posts_per_page' => $numpagi,
					'offset' => $offset
				);
				$posts = get_posts($args);
				foreach($posts AS $post) {
					$permalink = get_permalink($post);
					if(
						!empty($permalink)
						&& $permalink != $home
						&& $post->ID != $homePid
						&& strpos( $permalink, $home) !== false
					) {

						//Default Priority if auto calc is disabled
						$priority = ($postType == 'page' ? $defaultPriorityForPages : $defaultPriorityForPosts);

						//If priority calc. is enabled, calculate (but only for posts, not pages)!
						if($priorityProvider !== null && $postType == 'post') {
							$priority = $priorityProvider->GetPostPriority($post->ID, $post->comment_count, $post);
						}

						//Ensure the minimum priority
						if($postType == 'post' && $minimumPriority > 0 && $priority < $minimumPriority) $priority = $minimumPriority;

						//ADdd the URL to the sitemap
						$gsg->AddUrl(
							$permalink,
							$gsg->GetTimestampFromMySql($post->post_modified_gmt && $post->post_modified_gmt != '0000-00-00 00:00:00'? $post->post_modified_gmt : $post->post_date_gmt),'','', $post->ID, 'article');
					}
					unset($post);
				}
			}
		}

		unset($posts);
	}

	/**
	 * Generates the content for the archives sitemap
	 *
	 * @param $gsg GoogleSitemapGenerator
	 */
	public function BuildArchives($gsg) {
		/** @var $wpdb wpdb */
		global $wpdb;
		$now = current_time('mysql', true);

		$archives = $wpdb->get_results("
			SELECT DISTINCT
				YEAR(post_date_gmt) AS `year`,
				MONTH(post_date_gmt) AS `month`,
				MAX(post_date_gmt) AS last_mod,
				count(ID) AS posts
			FROM
				$wpdb->posts
			WHERE
				post_date_gmt < '$now'
				AND post_status = 'publish'
				AND post_type = 'post'
			GROUP BY
				YEAR(post_date_gmt),
				MONTH(post_date_gmt)
			ORDER BY
				post_date_gmt DESC
		");

		if($archives) {
			foreach($archives as $archive) {

				$url = get_month_link($archive->year, $archive->month);

				//Archive is the current one
				if($archive->month == date("n") && $archive->year == date("Y")) {
					$changeFreq = $gsg->GetOption("cf_arch_curr");
				} else { // Archive is older
					$changeFreq = $gsg->GetOption("cf_arch_old");
				}

				$gsg->AddUrl($url, $gsg->GetTimestampFromMySql($archive->last_mod), $changeFreq, $gsg->GetOption("pr_arch"));
			}
		}
	}

	/**
	 * Generates the misc sitemap
	 *
	 * @param $gsg GoogleSitemapGenerator
	 */
	public function BuildMisc($gsg) {

		$lm = get_lastpostmodified('gmt');

		if($gsg->GetOption("in_home")) {
			$home = get_bloginfo('url');

			//Add the home page (WITH a slash!)
			if($gsg->GetOption("in_home")) {
				if('page' == get_option('show_on_front') && get_option('page_on_front')) {
					$pageOnFront = get_option('page_on_front');
					$p = get_post($pageOnFront);
					if($p) {
						$gsg->AddUrl(trailingslashit($home), $gsg->GetTimestampFromMySql(($p->post_modified_gmt && $p->post_modified_gmt != '0000-00-00 00:00:00'
								? $p->post_modified_gmt
								: $p->post_date_gmt)), $gsg->GetOption("cf_home"), $gsg->GetOption("pr_home"));
					}
				} else {
					$gsg->AddUrl(trailingslashit($home), ($lm ? $gsg->GetTimestampFromMySql($lm)
							: time()), $gsg->GetOption("cf_home"), $gsg->GetOption("pr_home"));
				}
			}
		}

		// if($gsg->IsXslEnabled() && $gsg->GetOption("b_html") === true) {
		// 	$gsg->AddUrl($gsg->GetXmlUrl("", "", array("html" => true)), ($lm ? $gsg->GetTimestampFromMySql($lm)
		// 			: time()));
		// }

		do_action('sm_buildmap');
	}

	/**
	 * Generates the author sitemap
	 *
	 * @param $gsg GoogleSitemapGenerator
	 */
	public function BuildAuthors($gsg) {
		/** @var $wpdb wpdb */
		global $wpdb;

		//Unfortunately there is no API function to get all authors, so we have to do it the dirty way...
		//We retrieve only users with published and not password protected enabled post types

		$enabledPostTypes = $gsg->GetActivePostTypes();

		//Ensure we count at least the posts...
		if(count($enabledPostTypes) == 0) $enabledPostTypes[] = "post";

		$sql = "SELECT DISTINCT
					u.ID,
					u.user_nicename,
					MAX(p.post_modified_gmt) AS last_post
				FROM
					{$wpdb->users} u,
					{$wpdb->posts} p
				WHERE
					p.post_author = u.ID
					AND p.post_status = 'publish'
					AND p.post_type IN('" . implode("','", array_map('esc_sql', $enabledPostTypes)) . "')
					AND p.post_password = ''
				GROUP BY
					u.ID,
					u.user_nicename";

		$authors = $wpdb->get_results($sql);

		if($authors && is_array($authors)) {
			foreach($authors as $author) {
				$url = get_author_posts_url($author->ID, $author->user_nicename);
				$gsg->AddUrl($url, $gsg->GetTimestampFromMySql($author->last_post), $gsg->GetOption("cf_auth"), $gsg->GetOption("pr_auth"));
			}
		}
	}

	/**
	 * Filters the terms query to only include published posts
	 *
	 * @param $selects string[]
	 * @return string[]
	 */
	public function FilterTermsQuery($selects) {
		/** @var $wpdb wpdb */
		global $wpdb;
		$selects[] = "
		( /* ADDED BY XML SITEMAPS */
			SELECT
				UNIX_TIMESTAMP(MAX(p.post_date_gmt)) as _mod_date
			FROM
				{$wpdb->posts} p,
				{$wpdb->term_relationships} r
			WHERE
				p.ID = r.object_id
				AND p.post_status = 'publish'
				AND p.post_password = ''
				AND r.term_taxonomy_id = tt.term_taxonomy_id
		) as _mod_date
		 /* END ADDED BY XML SITEMAPS */
		";

		return $selects;
	}

	/**
	 * Generates the taxonomies sitemap
	 *
	 * @param $gsg GoogleSitemapGenerator
	 * @param $taxonomy string The Taxonomy
	 */
	public function BuildTaxonomies($gsg, $taxonomy) {

		$enabledTaxonomies = $this->GetEnabledTaxonomies($gsg);
		if(in_array($taxonomy, $enabledTaxonomies)) {

			$excludes = array();

			if($taxonomy == "category") {
				$exclCats = $gsg->GetOption("b_exclude_cats"); // Excluded cats
				if($exclCats) $excludes = $exclCats;
			}

			add_filter("get_terms_fields", array($this, "FilterTermsQuery"), 20, 2);
			$terms = get_terms($taxonomy, array("hide_empty" => true, "hierarchical" => false, "exclude" => $excludes));
			remove_filter("get_terms_fields", array($this, "FilterTermsQuery"), 20, 2);

			foreach($terms AS $term) {
				$gsg->AddUrl(get_term_link($term, $term->taxonomy), $term->_mod_date, $gsg->GetOption("cf_tags"), $gsg->GetOption("pr_tags"));
			}
		}
	}

	/**
	 * Returns the enabled taxonomies. Only taxonomies with posts are returned.
	 *
	 * @param GoogleSitemapGenerator $gsg
	 * @return array
	 */
	public function GetEnabledTaxonomies(GoogleSitemapGenerator $gsg) {

		$enabledTaxonomies = $gsg->GetOption("in_tax");
		if($gsg->GetOption("in_tags")) $enabledTaxonomies[] = "post_tag";
		if($gsg->GetOption("in_cats")) $enabledTaxonomies[] = "category";

		$taxList = array();
		foreach($enabledTaxonomies as $taxName) {
			$taxonomy = get_taxonomy($taxName);
			if($taxonomy && wp_count_terms($taxonomy->name, array('hide_empty' => true)) > 0) $taxList[] = $taxonomy->name;
		}
		return $taxList;
	}

	/**
	 * Generates the external sitemap
	 *
	 * @param $gsg GoogleSitemapGenerator
	 */
	public function BuildExternals($gsg) {
		$pages = $gsg->GetPages();
		if($pages && is_array($pages) && count($pages) > 0) {
			foreach($pages AS $page) {
				/** @var $page GoogleSitemapGeneratorPage */
				$gsg->AddUrl($page->GetUrl(), $page->getLastMod(), $page->getChangeFreq(), $page->getPriority());
			}
		}
	}

	/**
	 * Generates the sitemap index
	 *
	 * @param $gsg GoogleSitemapGenerator
	 */
	public function Index($gsg) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;
		$blogUpdate = strtotime(get_lastpostmodified('gmt'));

		$gsg->AddSitemap("misc", null);


		$taxonomies = $this->GetEnabledTaxonomies($gsg);
		foreach($taxonomies AS $tax) {
			$gsg->AddSitemap("tax", $tax, $blogUpdate);
		}

		$pages = $gsg->GetPages();
		if(count($pages) > 0) {
			foreach($pages AS $page) {
				if($page instanceof GoogleSitemapGeneratorPage && $page->GetUrl()) {
					$gsg->AddSitemap("externals", null, $blogUpdate);
					break;
				}
			}
		}

		$enabledPostTypes = $gsg->GetActivePostTypes();

		$hasEnabledPostTypesPosts = false;
		$hasPosts = false;
		if(count($enabledPostTypes) > 0) {

			$excludedPostIDs = $gsg->GetExcludedPostIDs($gsg);
			$exPostSQL = "";
			if(count($excludedPostIDs) > 0) {
				$exPostSQL = "AND p.ID NOT IN (" . implode(",", $excludedPostIDs) . ")";
			}

			$excludedCategoryIDs = $gsg->GetExcludedCategoryIDs($gsg);
			$exCatSQL = "";
			if(count($excludedCategoryIDs) > 0) {
				$exCatSQL = "AND ( p.ID NOT IN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode(",", $excludedCategoryIDs) . ")))";
			}
			$numpagi = 1000;
			$args = array(
				'post_type' => array('post','page','news','comparison','single_reviews','round_up','informational_posts','coupon','tool_post'),
				'suppress_filters' => false,
				'posts_per_page' => -1,
			);
			$posts = get_posts($args);
			if(($postCount = count($posts)) > 0) {
				$tb = ceil($postCount/$numpagi);
				for($t=1; $t<= $tb; $t++) {
					$gsg->AddSitemap("post", $t);
				}
			}
			$numpagi = 1000;
			$args = array(
				'post_type' => 'informational_posts',
				'suppress_filters' => false,
				'posts_per_page' => 10,
			);
			$posts = get_posts($args);
			if(($postCount = count($posts)) > 0) {
				$gsg->AddSitemap("publication", 'news');
			}
			$args = array(
				'post_type' => 'shop_post',
				'suppress_filters' => false,
				'posts_per_page' => 10,
			);
			$posts = get_posts($args);
			if(($postCount = count($posts)) > 0) {
				$gsg->AddSitemap("shop",null);
			}
		}

		$stories = ( new Google\Web_Stories\Story_Query( [], []) )->get_stories();
		$numpagi = 1000;

		if(($storiesCount = count($stories)) > 0) {
			$tb = ceil($storiesCount/$numpagi);
			for($t=1; $t<= $tb; $t++) {
				$gsg->AddSitemap("stories", $t);
			}
		}
		//Only include authors if there is a public post with a enabled post type
		if($gsg->GetOption("in_auth") && $hasEnabledPostTypesPosts) $gsg->AddSitemap("authors", null, $blogUpdate);

		//Only include archived if there are posts with postType post
		if($gsg->GetOption("in_arch") && $hasPosts) $gsg->AddSitemap("archives", null, $blogUpdate);
	}

	/**
	 * Return the URL to the sitemap related to a specific post
	 *
	 * @param array $urls
	 * @param $gsg GoogleSitemapGenerator
	 * @param $postID int The post ID
	 *
	 * @return string[]
	 */
	public function GetSitemapUrlForPost(array $urls, $gsg, $postID) {
		$post = get_post($postID);
		if($post) {
			$lastModified = $gsg->GetTimestampFromMySql($post->post_modified_gmt);

			$url = $gsg->GetXmlUrl("pt", $post->post_type . "-" . date("Y-m", $lastModified));
			$urls[] = $url;
		}

		return $urls;
	}

	/**
	 * Return the URL to the sitemap related to a specific post
	 *
	 * @param array $urls
	 * @param $gsg GoogleSitemapGenerator
	 *
	 * @return string[]
	 */
	public function GetSitemapUrlForWebStories(array $urls, $gsg, $postID) {
		$post = get_post($postID);
		if($post) {
			$lastModified = $gsg->GetTimestampFromMySql($post->post_modified_gmt);

			$url = $gsg->GetXmlUrl("pt", $post->post_type . "-" . date("Y-m", $lastModified));
			$urls[] = $url;
		}

		return $urls;
	}
}

if(defined("WPINC")) new GoogleSitemapGeneratorStandardBuilder();