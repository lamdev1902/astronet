<?php
function review_list() {
    ?>
    <style>
        table {
            border-collapse: collapse;
        }
        table, td, th {
            border: 1px solid black;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
    <div class="wrap">
        <table>
            <thead>
            <tr>
                <th>POST</th>
                <th>Nickname</th>
                <th>Title</th>
                <th>Detail</th>
                <th>Review Count</th>
                <th>Age</th>
                <th>Type</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'customer_reviews';
            $reviews = $wpdb->get_results("SELECT * from $table_name");
            foreach ($reviews as $review) { 
                    $time = date('d.m.y', strtotime($review->created_at));
                ?>
                <tr>
                    <td><?= $review->post_id; ?></td>
                    <td><?= $review->nickname; ?></td>
                    <td><?= $review->title; ?></td>
                    <td><?= $review->detail; ?></td>
                    <td><?= $review->review_count; ?></td>
                    <td><?= $review->age; ?></td>
                    <td><?= $review->type; ?></td>
                    <td><?= $time;?></td>
                    <td>
                        <select class="status-select" name="status">
                            <option value="0" <?= $review->review_status == 0 ? "selected" : ""; ?>>Pending</option>
                            <option value="1" <?= $review->review_status == 1 ? "selected" : ""; ?>>Approve</option>
                        </select>
                    </td>
                    <td><a href="#" class="updateReview" data-id="<?php echo $review->review_id; ?>">Update</a> </td>
                    <td><a href="#" class="deleteReview" data-id="<?php echo $review->review_id; ?>">Delete</a></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php
}
add_shortcode('short_review_list', 'review_list');
?>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        jQuery(document).ready(function($) {
        $('.updateReview').on('click', function(e) {

            var id = $(this).data('id');
            var status = $(this).closest('tr').find('.status-select').val();

            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'update_review_action',
                    id: id,
                    status: status
                },
                success: function(response) {
                    location.reload();
                }
            });
        });

        $('.deleteReview').on('click', function() {

            var id = $(this).data('id');

            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'delete_review_action',
                    id: id,
                },
                success: function(response) {
                    location.reload();
                }
            });
        });
    });
});
    
</script>