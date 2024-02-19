<?php
/*
Plugin Name: Customer Reviews Plugin
Description: A plugin for customer reviews
Author: Astronet
*/

function create_the_reviews_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
	
    $table_name = $wpdb->prefix . 'customer_reviews';

    $sql = "CREATE TABLE " . $table_name . " (
	review_id bigint NOT NULL AUTO_INCREMENT,
	post_id bigint UNSIGNED NOT NULL,
	title VARCHAR(255) NOT NULL,
	detail text NOT NULL,
	nickname varchar(250) NOT NULL,
    age varchar(50),
    type varchar(250),
    review_count smallint NOT NULL,
    review_status smallint NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (review_id),
    FOREIGN KEY (post_id) REFERENCES {$wpdb->posts} (ID)
    ) $charset_collate;";
 
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_the_reviews_table');
require_once('review_list.php');

add_action('admin_menu', 'at_try_menu');
function at_try_menu() {
    add_menu_page('review_list', 
        'Review Listing', 
        'manage_options',
        'review_Listing', 
        'review_list' 
    );
}


function handle_customer_reviews() {
    global $wpdb;

    $link = isset($_POST['link']) ? $_POST['link'] : get_home_url();

    if(!isset($_POST['post_id']) || !isset($_POST['nickname']) || !isset($_POST['title']) || !isset($_POST['feedback']) || !isset($_POST['rate']))
    {
        wp_redirect($link);
        exit;
    }  

    if(!validateText($_POST['nickname']) || !validateText($_POST['title']) || !validateText($_POST['feedback']))
    {
        wp_redirect($link);
        exit;
    }

    $postId =  $_POST['post_id'];
    $nickname =  $_POST['nickname'];
    $title =  $_POST['title'];
    $feedback = $_POST['feedback'];
    $age = isset($_POST['age']) ? $_POST['age'] : "";
    $type = isset($_POST['type']) ? implode(', ' , $_POST['type']) : "";
    $rate = $_POST['rate'];

    $table_name = $wpdb->prefix . 'customer_reviews';


    $result = $wpdb->insert(
        $table_name,
        array(
            'post_id' => $postId,
            'title' => $title,
            'detail' => $feedback,
            'nickname' => $nickname,
            'age' => $age,
            'type' => $type,
            'review_count' => $rate,
            'review_status' => 0
        )
    );

    wp_redirect($link);
    exit;
}
add_action( 'admin_post_nopriv_customer_reviews', 'handle_customer_reviews' );  // for anonymous users
add_action( 'admin_post_customer_reviews', 'handle_customer_reviews' );  // for logged in users

function validateText($input)
{

    $pattern = '/(sex|porn)/i';

    if (preg_match($pattern, $input)) {
        return false; 
    }

    if (filter_var($input, FILTER_VALIDATE_URL)) {
        return false; 
    }

    return true;
}

function load_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'load_jquery');

add_action('wp_ajax_update_review_action', 'update_review_callback');
function update_review_callback() {
    global $wpdb;

    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = (int)$_POST['id'];
        $status = (int)$_POST['status'];

        $table_name = $wpdb->prefix . 'customer_reviews';

        $result = $wpdb->update(
            $table_name,
            array('review_status' => $status),
            array('review_id' => $id),
            array('%d'), // Format cho 'review_status'
            array('%d')  // Format cho 'id'
        );
    }
    wp_die();
}

add_action('wp_ajax_delete_review_action', 'delete_review_callback');
function delete_review_callback() {
    global $wpdb;

    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $table_name = $wpdb->prefix . 'customer_reviews';
        $result = $wpdb->delete($table_name, array('review_id' => $id), array('%d'));
        
    }
    wp_die();
}

add_action('wp_ajax_load_more_reviews', 'load_more_reviews_callback');
function load_more_reviews_callback() {

    global $wpdb;
        $table_name = $wpdb->prefix . 'customer_reviews'; 
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $query_result = $wpdb->get_results("SELECT * FROM $table_name LIMIT $offset, 5");
    
        ob_start();
        foreach ($query_result as $review) {
        ?>
        <li class="review-item">
            <div class="review-item-header">
                <div class="review-header-rating">
                    <div class="review-ratings">
                    <?php 
                        $average = (int)$review->review_count;
                        $whole = floor($average);   
                        $fraction = $average - $whole; 
                        $averagefloat = abs($average); 
                        $percent = $fraction * 100;
                        $ratingClassFull = '';
                        $ratingClassEmpty = '';
                        $ratingClassPercent = '';
                        for ($i = 1; $i <= 5; $i++) { 
                            if ($i <= $averagefloat) {
                                $ratingClassFull .= "
                                <span class='star'>
                                    <svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 18 18' increment='0.01'>
                                        <polygon points='8.94 0 11.05 6.49 17.88 6.49 12.35 10.51 14.46 17 8.94 12.99 3.41 17 5.52 10.51 0 6.49 6.83 6.49 8.94 0' fill='#FF5757'></polygon>
                                    </svg>
                                </span>";
                            } else {
                                $ratingClassPercent .= "
                                <span class='star'>
                                    <svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 18 18' increment='0.01'>
                                        <linearGradient id='floatStar-" . $i . "' x1='0' x2='100%' y1='0' y2='0'>
                                            <stop offset='0' stop-color='#FF5757'></stop>
                                            <stop offset='" . $percent . "%' stop-color='#FF5757'></stop>
                                            <stop offset='" . $percent . "%' stop-color='#bdbdbd'></stop>
                                        </linearGradient>
                                        <polygon points='8.94 0 11.05 6.49 17.88 6.49 12.35 10.51 14.46 17 8.94 12.99 3.41 17 5.52 10.51 0 6.49 6.83 6.49 8.94 0' fill='url(#floatStar-" . $i . ")'></polygon>
                                    </svg>
                                </span>";
                            }
                        }
                        $ratings = $ratingClassFull . $ratingClassPercent;
                        echo $ratings;
                    ?>
                    </div>
                    <div class="review-author">
                        <?= $review->nickname?>
                    </div>
                </div>
                <div class="review-date">
                    <span class="review-value"><?= date('d.m.y', strtotime($review->created_at)) ?></span>
                </div>
            </div>
            <div class="review-item-content">
                <div class="review-additional-details">
                    <div class="review-age">
                        <span class="review-label">Age: </span>
                        <span class="review-value"><?= $review->age ?></span>
                    </div>
                    <div class="review-type">
                        <span class="review-label">Reason for purchase:</span>
                        <span class="review-value"><?= $review->type ?></span>
                    </div>
                </div>
                <div class="review-label">
                    <?= $review->title ?>
                </div>
                <div class="review-value">
                    <?= $review->detail; ?>
                </div>
            </div>
        </li>
        <?php
        }
		$result_get = ob_get_clean();
		echo $result_get;
		exit;
}