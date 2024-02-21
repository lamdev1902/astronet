jQuery(function($) {
    console.log('abc');
    var ajaxurl = ajax_object.ajaxurl;
    $( document).ready(function() {
        $('.updateReview').on('click', function(e) {

            var id = $(this).data('id');
            var status = $(this).closest('tr').find('.status-select').val();

            
            $.ajax({
                url: ajaxurl,
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
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_review_action',
                    id: id,
                },
                success: function(response) {
                    if (typeof response !== "string") {
                        window.location.href = response.data.redirect_url;
                    } else {
                        location.reload();
                    }
                }
            });
        });
    });
})
