jQuery(function($){

    $(document).ready( function() {

        var regex = /^[0-9]+$/;

        $('input[name="info[gender]"]').change(function(){
            if($(this).val() == 2) {
                $('.hip').removeClass('inactive')
            }else {
                $('.hip').addClass('inactive')
            }
        })

        $("input[name='info[age]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 25 || $(this).val() > 80){
                    $('.age-error').text('Please enter a weight between 25 and 80 pounds.');
                }else {
                    $('.age-error').text('');
                }
            }else {
                $(this).val('');
                $('.age-error').text('Must input numbers!');
            }
        });

        $("input[name='info[weight]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 40 || $(this).val() > 600){
                    $('.weight-error').text('Please enter a weight between 40 and 600 pounds.');
                }else {
                    $('.weight-error').text('');
                }
            }else {
                $(this).val('');
                $('.weight-error').text('Must input numbers!');
            }
        });

        $("input[name='info[height][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 4 || $(this).val() > 8)
                {
                    $('.height-error').text('Height value must be between 4 and 8 feet.');
                }else {
                    $('.height-error').text('');
                }
            }else {
                $(this).val('');
                $('.height-error').text('Must input numbers!');
            }
            
        });

        $("input[name='info[neck][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1)
                {
                    $('.neck-error').text('Neck value must be between 1');
                }else {
                    $('.neck-error').text('');
                }
            }else {
                $(this).val('');
                $('.neck-error').text('Must input numbers!');
            }
            
        });

        $("input[name='info[waist][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1)
                {
                    $('.waist-error').text('Height value must be between 1.');
                }else {
                    $('.waist-error').text('');
                }
            }else {
                $(this).val('');
                $('.waist-error').text('Must input numbers!');
            }
            
        });

        $("input[name='info[hip][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1)
                {
                    $('.hip-error').text('Hip value must be between 1.');
                }else {
                    $('.hip-error').text('');
                }
            }else {
                $(this).val('');
                $('.hip-error').text('Must input numbers!');
            }
            
        });

        $("#btnBodyFat").on('click', function(){

            $('#spinner').show();

            var formDataArray = $('.form.bodyfat-calculate').serializeArray();
    
            var jsonData = {}
            var dateValue = '';

            $.each(formDataArray, function(i, field) {
                var parts = field.name.split('[');
                var currentObj = jsonData;
    
                for (var j = 0; j < parts.length; j++) {
                    var key = parts[j].replace(']', '');
    
                    if (j === parts.length - 1) {
                        currentObj[key] = field.value;
                    } else {
                        currentObj[key] = currentObj[key] || {};
                        currentObj = currentObj[key];
                    }
                }
            });

                $.ajax({
                    url: 'https://34.163.253.54/wp-json/api/v1/body-fat/',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(jsonData),
                    success: function(response) {
                        // Xử lý phản hồi từ server nếu cần
                        if(response['status'] === 200)
                        {
                            var result = response['result']['bfp'];
    
    
                            $('.content-right').removeClass('inactive');
                            $(".content-right .result").empty();
                            var item = 1;
                            $.each(result, function (key, value) {
                                
                                var classDiv = (item % 2 == 0) ? 'item-1' : 'item-2';

                                var div = $(`<div class="item ${classDiv}"></div>`);

                                var text = '';
                                if(value.percent)
                                {
                                    text = value.percent + ' %';
                                }else if(value.pounds)
                                {
                                    text = value.pounds + ' lbs' 
                                }else 
                                {
                                    text = value.type;
                                }
                                var pTitle = $("<p class='title'>").text(value.title);
                                var pValue = $("<p class='value'>").text(text);
                                div.append(pTitle,pValue);

                                // Thêm thẻ <p> vào container
                                $(".content-right .result").append(div);
                                item++;
                            });
                        }
                        $('#spinner').hide();
                    },
                    error: function(error) {
                        // Xử lý lỗi nếu có
                        console.error('Error:', error);
                    }
                });
        });

    });
})

