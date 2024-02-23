<?php
/*
Plugin Name: Customer Reviews Plugin
Description: A plugin for customer reviews
Author: Astronet
*/

session_start();
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
                      "SELECT * from {$wpdb->prefix}customer_reviews WHERE post_id Like '%{$search}%' OR detail LIKE '%{$search}%'",
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
            'cb'            => '<input type="checkbox"/>',
            'review_id'     => 'ID',
            'post_id'       => 'POST',
            'nickname'      => 'User',
            'title'         => 'Title',
            'detail'        => 'Detail',
            'review_count'  => 'Review Count',
            'age'           => 'Age',
            'type'          => 'Type',
            'review_status' => 'Status',
            'created_at'    => 'Create At',
            'actions'       => 'Actions',
            'reply'         => 'Reply Detail',
            ''              => 'Reply'
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
                case 'review_id';
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
                case '':
                    if ($item['reply'] == null) {
                        return '';
                    } else {
                        return '<a href="#reply-'.$item['review_id'].'" rel="modal:open" class="view-reply">View</a>';
                    };
                case 'reply':
                    return '<div id="reply-'.$item['review_id'].'" class="modal">
                    <textarea id="replyReview" name="replyModel-'.$item['review_id'].'" rows="4" cols="50">'.$item['reply'].'</textarea>
                    <a data-id="'.$item['review_id'].'" type="button" class="updateReply button button-primary">Edit Reply</a>
                  </div>';
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
      }

      // To show checkbox with each row
      function column_cb($item)
      {
            return sprintf(
                  '<input type="checkbox" name="review[]" value="%s" />',
                  $item['review_id']
            );
      }

      function extra_tablenav($which)
        {
            if ($which == "top") {
                echo '<div class="alignleft actions bulkactions update-multiple" style="display:flex;">
                        <button class="button update-multiple-reviews" style="display: none">Update Multiple</button>
                        <button class="button delete-multiple-reviews" style="margin-left: 15px;display: none">Delete Multiple</button>
                    </div>';
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

class Customer_Review_Keyword_List extends WP_List_Table
{
      private $keyword_data;

      private function get_customer_reviews_keyword($search = "")
      {
            global $wpdb;

            if (!empty($search)) {
                return $wpdb->get_results(
                      "SELECT * from {$wpdb->prefix}customer_reviews_keyword WHERE keyword Like '%{$search}%'",
                      ARRAY_A
                );
            }else {
                return $wpdb->get_results(
                    "SELECT * From {$wpdb->prefix}customer_reviews_keyword",
                    ARRAY_A
              );
            }
      }

      function get_columns()
      {
            $columns = array(
                'cb'            => '<input type="checkbox"/>',
                'id' => 'ID',
                'keyword' => 'Keyword',
                'actions' => 'Actions',
            );
            return $columns;
      }

      function prepare_items()
      {
            if (isset($_POST['page']) && isset($_POST['s'])) {
                $this->keyword_data = $this->get_customer_reviews_keyword($_POST['s']);
            } else {
                $this->keyword_data = $this->get_customer_reviews_keyword();
            }            
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);

            /* pagination */
            $per_page = 5;
            $current_page = $this->get_pagenum();
            $total_items = count($this->keyword_data);

            $this->keyword_data = array_slice($this->keyword_data, (($current_page - 1) * $per_page), $per_page);

            $this->set_pagination_args(array(
                  'total_items' => $total_items, // total number of items
                  'per_page'    => $per_page // items to show on a page
            ));

            usort($this->keyword_data, array(&$this, 'usort_reorder'));

            $this->items = $this->keyword_data;
      }

      // bind data with column
      function column_default($item, $column_name)
      {
            switch ($column_name) {
                case 'id':
                case 'keyword':
                    return $item[$column_name];
                case 'actions':
                    return sprintf(
                        '<a class="deleteKeyword" data-id="%s" href="#">%s</a>',
                        $item['id'],
                        __('Delete', 'textdomain')
                    );
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
      }

      function column_cb($item)
      {
            return sprintf(
                  '<input type="checkbox" name="keyword[]" value="%s" />',
                  $item['id']
            );
      }

      function extra_tablenav($which)
        {
            if ($which == "top") {
                echo '<div class="alignleft actions bulkactions update-multiple" style="display:flex;">
                        <button class="button update-multiple-keyword" style="display: none">Update Multiple</button>
                        <button class="button delete-multiple-keyword" style="margin-left: 15px;display: none">Delete Multiple</button>
                    </div>';
            }
        }

       // Add sorting to columns
       protected function get_sortable_columns()
       {
             $sortable_columns = array(
                   'id'  => array('id', false),
                   'keyword' => array('keyword', false),
             );
             return $sortable_columns;
       }

       // Sorting function
      function usort_reorder($a, $b)
      {
            // If no sort, default to user_login
            $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';
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
      add_menu_page('Customer Review Listing', 'Customer Review Listing', 'activate_plugins', 'customer_reviews', 'customer_reviews');
      add_menu_page('Customer Review Keyword Listing', 'Customer Review Keyword Listing', 'activate_plugins', 'customer_reviews_keyword', 'customer_reviews_keyword');
}
add_action('admin_menu', 'my_add_menu_items');

// Plugin menu callback function
function customer_reviews()
{
    // Creating an instance
    $empTable = new Customer_Review_List();

    echo '<div class="wrap"><h2>Customer Review Listing</h2>';

    $message = get_transient('message');
    
    if($message)
    {
        if($message['status'] == 1)
        {
            echo '<div class="notice notice-success"><p style="color: #4F8A10;">' . $message['description'] . '</p></div>';
        }else {
            echo '<div class="notice notice-error"><p style="color: #color: #D8000C;">' . $message['description'] . '</p></div>';
        }
        delete_transient('message'); 
    }
    // Prepare table
    $empTable->prepare_items();
    ?>

    <div class="form" style="">
        <form style="display:none" method="post" id="replyForm" action="<?php echo esc_attr( admin_url('admin-post.php') ); ?>">
            <label for="keyword">Reply:</label>
            <input type="hidden" name="action" value="update_review_action">
            <input type="hidden" name="link" value="<?= admin_url('admin.php?page=customer_reviews') ?>">
            <input type="text" id="reply" name="reply" required>
            <button id="replyBtn" type="submit" name="submit" value="" class=" button button-primary">Reply</button>
        </form>
        <form method="post">
                <input type="hidden" name="page" value="customer_reviews" />
                <?php $empTable->search_box('search', 'search_id'); ?>
        </form>
    </div>
    <?php
    $empTable->display();
    echo '</div>';
}

function customer_reviews_keyword()
{
    // Creating an instance
    $empTable = new Customer_Review_Keyword_List();
    
    echo '<div class="wrap"><h2>Customer Review Keyword Listing</h2>';

    $message = get_transient('message');
    
    if($message)
    {
        if($message['status'] == 1)
        {
            echo '<div class="notice notice-success"><p style="color: #4F8A10;">' . $message['description'] . '</p></div>';
        }else {
            echo '<div class="notice notice-error"><p style="color: #D8000C;">' . $message['description'] . '</p></div>';
        }
        delete_transient('message'); 
    }
    
    $empTable->prepare_items();

    ?>
    <div class="form" style="display: flex; align-items:center; justify-content: space-between;">
        <form method="post" id="keywordReview" action="<?php echo esc_attr( admin_url('admin-post.php') ); ?>">
            <label for="keyword">Keyword:</label>
            <input type="hidden" name="action" value="customer_reviews">
            <input type="hidden" name="key" value="true">
            <input type="hidden" name="link" value="<?= admin_url('admin.php?page=customer_reviews_keyword') ?>">
            <input type="text" id="keyword" name="keyword" required>
            <button id="addKeyword" type="submit" name="submit" value="" class=" button button-primary">Add Keyword</button>
        </form>
        <form method="post">
            <input type="hidden" name="page" value="customer_reviews_keyword" />
            <?php $empTable->search_box('search', 'search_id'); ?>
        </form>
    </div>
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

function create_the_key_text_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
	
    $table_name = $wpdb->prefix . 'customer_reviews_keyword';

    $sql = "CREATE TABLE " . $table_name . " (
	id bigint NOT NULL AUTO_INCREMENT,
	keyword VARCHAR(255) NOT NULL,
	PRIMARY KEY  (id)
    ) $charset_collate;";
 
    dbDelta($sql);
}

function alter_review_reply_table() {
    global $wpdb;
	
    $table_name = $wpdb->prefix . 'customer_reviews';

    $sql = "ALTER TABLE $table_name ADD COLUMN reply TEXT";
 
    $wpdb->query($sql);
}

register_activation_hook(__FILE__, 'my_plugin_review_activation');

function my_plugin_review_activation() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    alter_review_reply_table();
    create_the_key_text_table();
}

function handle_customer_reviews() {
    
    global $wpdb;
    $link = isset($_POST['link']) ? $_POST['link'] : get_home_url();

    $message = [];

    if(isset($_POST['keyword']))
    {
        $table_name = $wpdb->prefix . 'customer_reviews_keyword';

        $result = $wpdb->insert(
            $table_name,
            array(
                'keyword' => $_POST['keyword'],
            )
        );

        if($result == true)
        {
            $message['status'] = 1;
            $message['description'] = 'Keyword added successfully';
        }else {
            $messagem['status'] = 0;
            $message['description'] = 'Keyword added failed.';
        }
        set_transient('message', $message, 60*60*12);

    }else {
        
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

        if($_POST['g-recaptcha-response']) {
            $privatekey = '6LeRDn0pAAAAAOApSyPFQ29h4t5vqmYEuLcA_uEw';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_HEADER, 'Content-Type: application/json');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                    'secret' => $privatekey,
                    'response' => $_POST['g-recaptcha-response'],
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                )
            );

            $resp = json_decode(curl_exec($ch));
            curl_close($ch);
            if ($resp->success) {
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
            }
        }
    }

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
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/review-action.js', array('jquery'), null, true);
    wp_localize_script('custom-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('keyword-script', plugin_dir_url(__FILE__) . 'assets/js/keyword-action.js', array('jquery'), null, true);
    wp_localize_script('keyword-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));

    wp_enqueue_script('jquery-modal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', array('jquery'), null, true);
    wp_enqueue_style('jquery-modal-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
}

add_action('admin_enqueue_scripts', 'enqueue_custom_script');

add_action('wp_ajax_update_review_action', 'update_review_callback');
function update_review_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'customer_reviews';
    
    if (isset($_POST['id']) && isset($_POST['status'])) {

        $message = [];

        $id = (int)$_POST['id'];
        $status = (int)$_POST['status'];
        $result = $wpdb->update(
            $table_name,
            array('review_status' => $status),
            array('review_id' => $id),
            array('%d'), 
            array('%d')
        );

        if($result == true)
        {
            $message['status'] = 1;
            $message['description'] = 'Review updated successfully';
        }else {
            $message['status'] = 0;
            $message['description'] = "Please check status's reivew";
        }

    }elseif(isset($_POST['data']) && isset($_POST['multiple'])) {
        $data = $_POST['data'];
        $i = 0;
        foreach($data as $item)
        {   
            $id = $item[0]['id'];
            $status = $item[0]['status'];

            $result = $wpdb->update(
                $table_name,
                array('review_status' => $status),
                array('review_id' => $id),
                array('%d'), 
                array('%d')
            );
            if($result == true)
            {
                $i++;
                $message['status'] = 1;
                $message['description'] = 'Review ( ' .$i. ' ) updated successfully';
            }else {
                if(!isset($message['status']))
                {
                    $message['status'] = 0;
                    $message['description'] = "Please check status's reivew";
                }
            }
        }
    }elseif(isset($_POST['data']) && isset($_POST['reply'])) {
        $data = $_POST['data'];
        $reply = $_POST['reply'];

        $i = 0;
        foreach($data as $item)
        {   
            $id = $item['id'];

            $result = $wpdb->update(
                $table_name,
                array('reply' => $reply),
                array('review_id' => $id),
                array('%s'), 
                array('%d')
            );
            if($result == true)
            {
                $i++;
                $message['status'] = 1;
                $message['description'] = 'Review ( ' .$i. ' ) updated successfully';
            }else {
                if(!isset($message['status']))
                {
                    $message['status'] = 0;
                    $message['description'] = "Please check status's reivew";
                }
            }
        }
    }
    set_transient('message', $message, 60*60*12);

    wp_die();
}

add_action('wp_ajax_delete_review_action', 'delete_review_callback');
function delete_review_callback() {
    global $wpdb;

    $message = [];

    if (isset($_POST['id']) && !isset($_POST['keyword'])) {
        $id = $_POST['id'];
        $table_name = $wpdb->prefix . 'customer_reviews';
        $result = $wpdb->delete($table_name, array('review_id' => $id), array('%d'));

        if($result == true)
        {
            $message['status'] = 1;
            $message['description'] = 'Review deleted successfully';
        }else {
            $message['description'] = 'Review deleted failed.';
        }

        set_transient('message', $message, 10);
        $remaining_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($remaining_count <= 5) {
            $redirect_url = admin_url('admin.php?page=customer_reviews');
            wp_send_json_success(array('redirect_url' => $redirect_url));
        }

    }elseif(isset($_POST['data']) && isset($_POST['multiple']) && !isset($_POST['keyword'])) {
        $table_name = $wpdb->prefix . 'customer_reviews';

        $data = $_POST['data'];
        $i = 0;
        foreach($data as $item)
        {   
            $id = $item['id'];

            $result = $wpdb->delete($table_name, array('review_id' => $id), array('%d'));

            if($result == true)
            {
                $i++;
                $message['status'] = 1;
                $message['description'] = 'Review ( ' . $i .' ) deleted successfully';
            }else {
                if(!isset($message['status']))
                {
                    $message['status'] = 0;
                    $message['description'] = "Review deleted failed.";
                }
            }
        }
        set_transient('message', $message, 10);
        $remaining_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($remaining_count <= 5) {
            $redirect_url = admin_url('admin.php?page=customer_reviews');
            wp_send_json_success(array('redirect_url' => $redirect_url));
        }
    }elseif(isset($_POST['data']) && isset($_POST['multiple']) && isset($_POST['keyword'])) {
        $table_name = $wpdb->prefix . 'customer_reviews_keyword';

        $data = $_POST['data'];
        $i = 0;
        foreach($data as $item)
        {   
            $id = $item['id'];

            $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));

            if($result == true)
            {
                $i++;
                $message['status'] = 1;
                $message['description'] = 'Keyword ( ' . $i .' ) deleted successfully';
            }else {
                if(!isset($message['status']))
                {
                    $message['status'] = 0;
                    $message['description'] = "Keyword deleted failed.";
                }
            }
        }
        set_transient('message', $message, 10);
        $remaining_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($remaining_count <= 5) {
            $redirect_url = admin_url('admin.php?page=customer_reviews_keyword');
            wp_send_json_success(array('redirect_url' => $redirect_url));
        }
    }
    else {
        $id = $_POST['id'];
        $table_name = $wpdb->prefix . 'customer_reviews_keyword';
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));

        if($result == true)
        {
            $message['status'] = 1;
            $message['description'] = 'Keyword deleted successfully';
        }else {
            $message['description'] = 'Keyword deleted failed.';
        }
        set_transient('message', $message, 10);
        $remaining_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($remaining_count <= 5) {
            $redirect_url = admin_url('admin.php?page=customer_reviews_keyword');
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