<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class quiz_type extends WP_List_Table
{
      private $type_data;

      private function get_quiz($search = "")
      {
            global $wpdb;

            if (!empty($search)) {
                return $wpdb->get_results(
                      "SELECT * from {$wpdb->prefix}quiz_type_mental_health WHERE id Like '%{$search}%' OR name LIKE '%{$search}%' OR code LIKE '%{$search}%'",
                      ARRAY_A
                );
            }else {
                return $wpdb->get_results(
                    "SELECT * From {$wpdb->prefix}quiz_type_mental_health",
                    ARRAY_A
              );
            }
            
      }
      // Define table columns
      function get_columns()
      {
        $columns = array(
            'cb'            => '<input type="checkbox"/>',
            'id'     => 'ID',
            'name'       => 'Name',
            'code'       => 'Code',
            'created_at'    => 'Create At',
            'actions'       => 'Actions',
        );

        return $columns;
      }

      // Bind table with columns, data and all
      function prepare_items()
      {
            if (isset($_POST['page']) && isset($_POST['s'])) {
                $this->type_data = $this->get_quiz($_POST['s']);
            } else {
                $this->type_data = $this->get_quiz();
            }            
            $columns = $this->get_columns();
            $hidden = array();

            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($columns, $hidden, $sortable);

            $this->process_bulk_action();

            /* pagination */
            $per_page = $this->get_items_per_page('quiz_list_per_page', 5);
            $current_page = $this->get_pagenum();
            $total_items = count($this->type_data);

            $this->type_data = array_slice($this->type_data, (($current_page - 1) * $per_page), $per_page);

            $this->set_pagination_args(array(
                  'total_items' => $total_items, // total number of items
                  'per_page'    => $per_page // items to show on a page
            ));

            // usort($this->type_data, array(&$this, 'usort_reorder'));

            $this->items = $this->type_data;
      }

      // bind data with column
      function column_default($item, $column_name)
      {
            switch ($column_name) {
                case 'id';
                case 'name':
                case 'code':
                case 'created_at':
                    return $item[$column_name];
                case 'actions':
                    return sprintf(
                        '<a class="update" data-id="%s" href="%s">%s</a> | <a class="deleteReview" data-id="%s" href="%s">%s</a>',
                        $item['id'],
                        admin_url('admin.php?page=quiz_manage&action=edit&type=type&id='.$item['id']),
                        __('Edit', 'textdomain'),
                        $item['id'],
                        admin_url('admin.php?page=quiz_manage&action=delete&type=type&id='.$item['id']),
                        __('Delete', 'textdomain'),
                    );
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
      }

      // To show checkbox with each row
      function column_cb($item)
      {
            return sprintf(
                  '<input type="checkbox" name="type_id[]" value="%s" />',
                  $item['id']
            );
      }

    function get_bulk_actions()
    {
        $actions = array(
                'delete_multiple'    => __('Delete Multiple', 'supporthost-admin-table'),
        );
        return $actions;
    }

    public function process_bulk_action() { 
        $action = $this->current_action();

        if($action == 'delete_multiple') {
            delete_data_by_id('type', $_POST['type_id']);
        }

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
                   'id'  => array('id', false),
                   'name'   => array('name', true),
                   'code'   => array('code', true),
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