jQuery(function($) {
    console.log('abc');
    var ajaxurl = ajax_object.ajaxurl;
    $( document).ready(function() {

        $('#cb-select-all-1').attr('name', 'review[]');

        
        $("input[name='review[]']").change(function () {
            var checkedCheckboxes = $("input[name='review[]']:checked");
            var form = $('#replyForm').closest(".form");
            if(checkedCheckboxes.length > 0) {
                $('.update-multiple-reviews').css('display', 'block');
                $('.delete-multiple-reviews').css('display', 'block');
                form.css({
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'space-between'
                });

                $('#replyForm').css('display', 'block');
            }else {
                $('.update-multiple-reviews').css('display', 'none');
                $('.delete-multiple-reviews').css('display', 'none');
                $('#replyForm').css('display', 'none');
                form.removeAttr('style');
            }
        })

        $('#replyForm').submit(function(event) {
            event.preventDefault();

            var data = [];
            $("input[name='review[]']:checked").each(function() {
                if($(this).val() != 'on')
                {
                    data.push({ id:$(this).val()});
                }
            });

            var reply = $('input[name="reply"]').val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_review_action',
                    data: data,
                    reply: reply
                },
                success: function(response) {
                    location.reload();
                }
            });

            
        });
        $('.update-multiple-reviews').on('click', function(e) {
            var data = [];
            $("input[name='review[]']:checked").each(function() {
                if($(this).val() != 'on')
                {
                    var arr = [];
                    var $tr = $(this).closest("tr");
                    var reviewStatusValue = $tr.find(".review_status select").val();
                    arr.push({ id:$(this).val(), status:reviewStatusValue });
                    data.push(arr);
                }
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_review_action',
                    data: data,
                    multiple: true
                },
                success: function(response) {
                    location.reload();
                }
            });
        })

        $('.delete-multiple-reviews').on('click', function(e) {
            var data = [];
            $("input[name='review[]']:checked").each(function() {
                if($(this).val() != 'on')
                {
                    data.push({ id:$(this).val()});
                }
                
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_review_action',
                    data: data,
                    multiple: true
                },
                success: function(response) {
                    if (typeof response !== "string") {
                        window.location.href = response.data.redirect_url;
                    } else {
                        location.reload();
                    }
                }
            });
        })

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

function review_item_checkbox(){
    

    return data;
}