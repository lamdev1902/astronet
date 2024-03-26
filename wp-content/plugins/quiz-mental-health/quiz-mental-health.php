<?php
/*
Plugin Name: Quiz Mental Health Plugin
Description: A plugin for quiz mental health
Author: Astronet
*/

require_once plugin_dir_path( __FILE__ ) . 'classes/quiz_list.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/quiz_type.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/quiz_answer.php';

require_once plugin_dir_path( __FILE__ ) . 'inc/quiz-manage.php';


function atn_enqueue_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('quiz-script', plugin_dir_url(__FILE__) . 'assets/js/quiz-script.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style( 'quiz-style', plugin_dir_url(__FILE__) . 'assets/css/quiz-style.css', array(), '1.0.0', 'all' );

}

add_action('admin_enqueue_scripts', 'atn_enqueue_script');


/**
 * add menu for quiz mental health plugin
 */
function quiz_plugin_menu()
{
    $parent_slug = 'quiz_slug';

    add_menu_page('Quiz', 'Quiz', '', $parent_slug, 'manage_options', '' );

    $quizHood = add_submenu_page($parent_slug, 'Quiz Listing', 'Quiz Listing', 'activate_plugins', 'quiz_listing', 'quiz_listing');
    $typeHood = add_submenu_page($parent_slug, 'Quiz Type Listing', 'Quiz Type Listing', 'activate_plugins', 'quiz_type', 'quiz_type');
    $answerHood = add_submenu_page($parent_slug, 'Quiz Answer Listing', 'Quiz Answer Listing', 'activate_plugins', 'quiz_answer', 'quiz_answer');
    $manageQuiz = add_submenu_page($parent_slug, 'Add/Edit Quiz', 'Add/Edit Quiz', 'activate_plugins', 'quiz_manage', 'quiz_manage');
    add_action("load-$quizHood", 'quiz_menu_listing');

    function quiz_menu_listing()
    {
        $option = 'per_page';

        $args = array(
                'label' => 'Quiz Listing',
                'default' => 5,
                'option' => 'quiz_list_per_page'
        );
        add_screen_option($option, $args);

        $empTable = new quiz_list();
    }

    add_action("load-$typeHood", 'quiz_type_menu_listing');

    function quiz_type_menu_listing()
    {
        $option = 'per_page';

        $args = array(
                'label' => 'Quiz Type Listing',
                'default' => 5,
                'option' => 'quiz_list_per_page'
        );
        add_screen_option($option, $args);

        $empTable = new quiz_type();
    }

    add_action("load-$answerHood", 'quiz_answer_menu_listing');

    function quiz_answer_menu_listing()
    {
        $option = 'per_page';

        $args = array(
                'label' => 'Quiz Answer Listing',
                'default' => 5,
                'option' => 'quiz_list_per_page'
        );
        add_screen_option($option, $args);

        $empTable = new quiz_type();
    }
}
add_action('admin_menu', 'quiz_plugin_menu');

// Plugin menu callback function
function quiz_listing()
{
    // Creating an instance
    $empTable = new quiz_list();

    echo '<div class="wrap"><h2>Quiz Listing</h2>';

    $message = get_transient('message');
    
    if($message)
    {
        if($message['count'] > 0)
        {
            echo '<div class="notice notice-success"><p style="color: #4F8A10;">' . $message['text'] . '</p></div>';
        }else {
            echo '<div class="notice notice-error"><p style="color: #D8000C;">' . $message['text'] . '</p></div>';
        }
        delete_transient('message'); 
    }
    // Prepare table
    $empTable->prepare_items();
    ?>
    <form method="post">
        <input type="hidden" name="page" value="customer_reviews" />
        <?php $empTable->search_box('search', 'search_id'); ?>
    </form>
    <form action="<?= admin_url('admin.php?page=quiz_listing')?>" method="POST">
    <?php 
    $empTable->display();
    ?>
    </form>
    <?php
    
    echo '</div>';
}

// Plugin menu callback function
function quiz_type()
{
    // Creating an instance
    $empTable = new quiz_type();

    echo '<div class="wrap"><h2>Quiz Type Listing</h2>';

    $message = get_transient('message');
    
    if($message)
    {
        if($message['count'] > 0)
        {
            echo '<div class="notice notice-success"><p style="color: #4F8A10;">' . $message['text'] . '</p></div>';
        }else {
            echo '<div class="notice notice-error"><p style="color: #D8000C;">' . $message['text'] . '</p></div>';
        }
        delete_transient('message'); 
    }
    // Prepare table
    $empTable->prepare_items();
    ?>
    <form method="post">
        <input type="hidden" name="page" value="" />
        <?php $empTable->search_box('search', 'search_id'); ?>
    </form>
    <form action="<?= admin_url('admin.php?page=quiz_type')?>" method="POST">
    <?php 
    $empTable->display();
    ?>
    </form>
    <?php
    
    echo '</div>';
}

// Plugin menu callback function
function quiz_answer()
{
    // Creating an instance
    $empTable = new quiz_answer();

    echo '<div class="wrap"><h2>Quiz Answer Listing</h2>';

    $message = get_transient('message');
    
    if($message)
    {
        if($message['count'] > 0)
        {
            echo '<div class="notice notice-success"><p style="color: #4F8A10;">' . $message['text'] . '</p></div>';
        }else {
            echo '<div class="notice notice-error"><p style="color: #D8000C;">' . $message['text'] . '</p></div>';
        }
        delete_transient('message'); 
    }
    // Prepare table
    $empTable->prepare_items();
    ?>
    <form method="post">
        <input type="hidden" name="page" value="" />
        <?php $empTable->search_box('search', 'search_id'); ?>
    </form>
    <form action="<?= admin_url('admin.php?page=quiz_answer')?>" method="POST">
    <?php 
    $empTable->display();
    ?>
    </form>
    <?php
    
    echo '</div>';
}
/**
 * create quiz answer table
 */
function create_quiz_answer_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'quiz_answer_mental_health';

    $sql = "CREATE TABLE " . $table_name . " (
	id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id)
    ) $charset_collate;";
 
    dbDelta($sql);
}

/**
 * create quiz type table
 */
function create_quiz_type_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'quiz_type_mental_health';

    $sql = "CREATE TABLE " . $table_name . " (
	id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id)
    ) $charset_collate;";
 
    dbDelta($sql);
}

/**
 * create quiz answer collection table
 */
function create_answer_collection_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'quiz_answer_collection_mental_health';

    $sql = "CREATE TABLE " . $table_name . " (
	id INT NOT NULL AUTO_INCREMENT,
    answer_id INT NOT NULL,
    content VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id),
    FOREIGN KEY (answer_id) REFERENCES {$wpdb->prefix}quiz_answer_mental_health(id)
    ) $charset_collate;";
 
    dbDelta($sql);
}

/**
 * create quiz table
 */
function create_quiz_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'quiz_mental_health';

    $sql = "CREATE TABLE " . $table_name . " (
	id INT NOT NULL AUTO_INCREMENT,
    type_id INT NOT NULL,
    answer_id INT NOT NULL,
	quiz_text VARCHAR(255) NOT NULL,
    position int default 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id),
    FOREIGN KEY (type_id) REFERENCES {$wpdb->prefix}quiz_type_mental_health(id),
    FOREIGN KEY (answer_id) REFERENCES {$wpdb->prefix}quiz_answer_mental_health(id)
    ) $charset_collate;";
 
    dbDelta($sql);
}


function quiz_mental_health_plugin_activation() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    create_quiz_type_table();
    create_quiz_answer_table();
    create_quiz_table();
    create_answer_collection_table();
}

register_activation_hook(__FILE__, 'quiz_mental_health_plugin_activation');
