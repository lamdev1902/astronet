<?php

$action = ( isset($_GET['action']) ) ? sanitize_key( $_GET['action'] ) : '';
$id     = ( isset($_GET['quiz_category']) ) ? absint( sanitize_key( $_GET['quiz_category'] ) ) : null;

if( $action == 'duplicate' && !is_null($id) ){
    $this->quiz_categories_obj->duplicate_quiz_categories($id);
}

$plus_icon_svg = "<span class=''><img src='". AYS_QUIZ_ADMIN_URL ."/images/icons/plus=icon.svg'></span>";

?>
<div class="wrap ays-quiz-list-table ays_quiz_categories_list_table">
    <div class="ays-quiz-heading-box">
        <div class="ays-quiz-wordpress-user-manual-box">
            <a href="https://ays-pro.com/wordpress-quiz-maker-user-manual" target="_blank"><?php echo __("View Documentation", $this->plugin_name); ?></a>
        </div>
    </div>
    <h1 class="wp-heading-inline">
        <?php
            echo __(esc_html(get_admin_page_title()),$this->plugin_name);
            echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary ays-quiz-add-new-button ays-quiz-add-new-button-new-design"> %s ' . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg);
        ?>
    </h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <?php
                        $this->quiz_categories_obj->views();
                    ?>
                    <form method="post">
                        <?php
                            $this->quiz_categories_obj->prepare_items();
                            $search = __( "Search", $this->plugin_name );
                            $this->quiz_categories_obj->search_box($search, $this->plugin_name);
                            $this->quiz_categories_obj->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>

    <h1 class="wp-heading-inline">
        <?php
            echo __(esc_html(get_admin_page_title()),$this->plugin_name);
            echo sprintf( '<a href="?page=%s&action=%s" class="page-title-action button-primary ays-quiz-add-new-button ays-quiz-add-new-button-new-design"> %s ' . __('Add New', $this->plugin_name) . '</a>', esc_attr( $_REQUEST['page'] ), 'add', $plus_icon_svg);
        ?>
    </h1>
</div>
