jQuery(function($) {
    var ajaxurl = ajax_object.ajaxurl;
    $( document).ready(function() {
        $('#cb-select-all-1').attr('name', 'keyword[]');


        $("input[name='keyword[]']").change(function () {
            var checkedCheckboxes = $("input[name='keyword[]']:checked");
            if(checkedCheckboxes.length > 0) {
                $('.update-multiple-keyword').css('display', 'block');
                $('.delete-multiple-keyword').css('display', 'block');


            }else {
                $('.update-multiple-keyword').css('display', 'none');
                $('.delete-multiple-keyword').css('display', 'none');
            }
        })

        $('.delete-multiple-keyword').on('click', function(e) {
            var data = [];
            $("input[name='keyword[]']:checked").each(function() {
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
                    multiple: true,
                    keyword: true,
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

        $('.addKeyword').on('click', function() {

            var formData = $('#keywordReview').serializeArray();
			var jsonData = {};

			$.each(formData, function(i, field) {
				var parts = field.name.split('[');
				var currentObj = jsonData;

				for (var j = 0; j < parts.length; j++) {
					var key = parts[j].replace(']', '');

					if (j === parts.length - 1) {
					if(field.value)
					{
						currentObj[key] = field.value;
					}
					} else {
						currentObj[key] = currentObj[key] || {};
						currentObj = currentObj[key];
					}
				}
			});

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_review_action',
                    id: id,
                    keyword: true
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

        $('.deleteKeyword').on('click', function() {

            var id = $(this).data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_review_action',
                    id: id,
                    keyword: true
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
