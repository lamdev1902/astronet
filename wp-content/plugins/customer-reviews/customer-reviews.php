<?php
/*
Plugin Name: Customer Reviews Plugin
Description: A plugin for customer reviews
Author: Astronet
*/

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Extending class
class Customer_Review_List extends WP_List_Table
{
      private $reviews_data;

      private function get_customer_reviews($search = "")
      {
            global $wpdb;

            if (!empty($search)) {
                return $wpdb->get_results(
                      "SELECT * from {$wpdb->prefix}customer_reviews WHERE post_id Like '%{$search}%'",
                      ARRAY_A
                );
            }else {
                return $wpdb->get_results(
                    "SELECT * From {$wpdb->prefix}customer_reviews",
                    ARRAY_A
              );
            }
            
      }

      // Define table columns
      function get_columns()
      {
            $columns = array(
                  'post_id' => 'POST',
                  'nickname' => 'User',
                  'title' => 'Title',
                  'detail'    => 'Detail',
                  'review_count' => 'Review Count',
                  'age' => 'Age',
                  'type' => 'Type',
                  'review_status' => 'Status',
                  'created_at' => 'Create At',
                  'actions' => 'Actions',
            );
            return $columns;
      }

      // Bind table with columns, data and all
      function prepare_items()
      {
            if (isset($_POST['page']) && isset($_POST['s'])) {
                $this->reviews_data = $this->get_customer_reviews($_POST['s']);
            } else {
                $this->reviews_data = $this->get_customer_reviews();
            }            
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);

            /* pagination */
            $per_page = 5;
            $current_page = $this->get_pagenum();
            $total_items = count($this->reviews_data);

            $this->reviews_data = array_slice($this->reviews_data, (($current_page - 1) * $per_page), $per_page);

            $this->set_pagination_args(array(
                  'total_items' => $total_items, // total number of items
                  'per_page'    => $per_page // items to show on a page
            ));

            usort($this->reviews_data, array(&$this, 'usort_reorder'));

            $this->items = $this->reviews_data;
      }

      // bind data with column
      function column_default($item, $column_name)
      {
            switch ($column_name) {
                case 'post_id':
                case 'nickname':
                case 'title':
                case 'detail':
                case 'review_count':
                case 'age':
                case 'type':
                    return $item[$column_name];
                case 'review_status':
                    $options = array(
                        '0' => 'Pending',
                        '1' => 'Approve'
                    );
                    $selected = isset($item['review_status']) ? $item['review_status'] : ''; // Lấy giá trị hiện tại của review_status
                    $select_html = '<select class="status-select">';
                    foreach ($options as $value => $text) {
                        $select_html .= sprintf('<option value="%s" %s>%s</option>',
                            $value,
                            selected($value, $selected, false), // Chọn tùy thuộc vào giá trị hiện tại
                            $text
                        );
                    }
                    $select_html .= '</select>';
                    return $select_html;
                case 'created_at':
                    return $item[$column_name];
                case 'actions':
                    return sprintf(
                        '<a class="updateReview" data-id="%s" href="#">%s</a> | <a class="deleteReview" data-id="%s" href="#">%s</a>',
                        $item['review_id'],
                        __('Update', 'textdomain'),
                        $item['review_id'],
                        __('Delete', 'textdomain')
                    );
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
      }

       // Add sorting to columns
       protected function get_sortable_columns()
       {
             $sortable_columns = array(
                   'post_id'  => array('post_id', false),
                   'nickname' => array('nickname', false),
                   'title'   => array('title', true),
                   'detail'  => array('detail', false),
                   'review_count' => array('review_count', false),
                   'age'   => array('age', true),
                   'type'  => array('type', false),
                   'review_status' => array('review_status', false),
                   'created_at'   => array('created_at', true)
             );
             return $sortable_columns;
       }

       // Sorting function
      function usort_reorder($a, $b)
      {
            // If no sort, default to user_login
            $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'post_id';
            // If no order, default to asc
            $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
            // Determine sort order
            $result = strcmp($a[$orderby], $b[$orderby]);
            // Send final sort direction to usort
            return ($order === 'asc') ? $result : -$result;
      }
}

function my_add_menu_items()
{
      add_menu_page('Customer Review Listing', 'Customer Review Listing', 'activate_plugins', 'customer_reviews', 'employees_list_init');
}
add_action('admin_menu', 'my_add_menu_items');

// Plugin menu callback function
function employees_list_init()
{
      // Creating an instance
      $empTable = new Customer_Review_List();

      echo '<div class="wrap"><h2>Customer Review Listing</h2>';
      // Prepare table
      $empTable->prepare_items();
      ?>
        <form method="post">
                <input type="hidden" name="page" value="customer_reviews" />
                <?php $empTable->search_box('search', 'search_id'); ?>
        </form>
    <?php
      $empTable->display();
      echo '</div>';
}
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

function enqueue_custom_script() {
    wp_enqueue_script('jquery');
    // Thêm file JS của bạn vào hàng đợi
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/review-action.js', array('jquery'), null, true);
    wp_localize_script('custom-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));

}

add_action('admin_enqueue_scripts', 'enqueue_custom_script');

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

        $remaining_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        $a = admin_url('admin.php?page=customer_reviews');
        if ($remaining_count <= 5) {
            $redirect_url = admin_url('admin.php?page=customer_reviews');
            wp_send_json_success(array('redirect_url' => $redirect_url));
        }

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