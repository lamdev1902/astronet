jQuery(function($){

    $(document).ready( function() {

        var regex = /^[0-9]+$/;

        $('input[name="info[gender]"]').change(function(){
            if($(this).val() == 2) {
                $('.hip').removeClass('inactive')
            }else {
                $('.hip').addClass('inactive')
            }

            validateForm();
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

            validateForm();

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

            validateForm();

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
            
            validateForm();

        });

        $("input[name='info[height][inches]']").change(function () {
            if(!$(this).val().match(regex)){
                $(this).val('');
                $('.height-error').text('Must input numbers!');
            }else {
                $('.height-error').text('');
            }
            validateForm();
            
        });

        $("input[name='info[neck][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1)
                {
                    $('.neck-error').text('Neck value must be between 1.');
                }else {
                    $('.neck-error').text('');
                }
            }else {
                $(this).val('');
                $('.neck-error').text('Must input numbers!');
            }
            validateForm();
            
        });

        $("input[name='info[neck][inches]']").change(function () {
            if(!$(this).val().match(regex)){
                $(this).val('');
                $('.neck-error').text('Must input numbers!');
            }else {
                $('.neck-error').text('');
            }
            validateForm();
            
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
            validateForm();
            
        });

        $("input[name='info[waist][inches]']").change(function () {
            if(!$(this).val().match(regex)){
                $(this).val('');
                $('.waist-error').text('Must input numbers!');
            }else {
                $('.waist-error').text('');
            }
            validateForm();
            
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
            validateForm();
            
        });

        $("input[name='info[hip][inches]']").change(function () {
            if(!$(this).val().match(regex)){
                $(this).val('');
                $('.hip-error').text('Must input numbers!');
            }else {
                $('.hip-error').text('');
            }
            validateForm();
            
        });

        $("#btnClear").on('click', function(){
            $("input[name='info[age]").val('');
            $("input[name='info[weight]").val('');
            $("input[name='info[height][feet]").val('');
            $("input[name='info[height][inches]").val('');
            $("input[name='info[neck][feet]").val('');
            $("input[name='info[neck][inches]").val('');
            $("input[name='info[waist][feet]").val('');
            $("input[name='info[waist][inches]").val('');
            $("input[name='info[hip][feet]").val('');
            $("input[name='info[hip][inches]").val('');
        })

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

                                if(key == 'navy_method')
                                {
                                    $('.main-result').text('Body Fat: ' + value.percent + ' %');
                                }
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

function validateForm()
{
  var age = $("input[name='info[age]").val();
  var weight = $("input[name='info[weight]").val();
  var height = $("input[name='info[height][feet]").val();
  var neck = $("input[name='info[neck][feet]").val();
  var waist = $("input[name='info[waist][feet]").val();
  var hip = $("input[name='info[hip][inches]").val();

  var ageError = $(".age-error").text();
  var weightError = $(".weight-error").text();
  var heightError = $(".height-error").text();
  var neckError = $(".neck-error").text();
  var waistError = $(".waist-error").text();
  var hipError = $(".hip-error").text();

    if($('input[name="info[gender]"]').val() == 1){
        if( (age && weight && height && neck && waist ) && (ageError == "" && weightError == "" && heightError == "" && neckError == "" && waistError == "") )        
        {
            $("#btnBodyFat").prop('disabled', false);
        }else {
            $("#btnBodyFat").prop('disabled', true);
        }
    }else {
        if( (age && weight && height && neck && waist && hip ) && (ageError == "" &&  weightError == "" && heightError == "" && neckError == "" &&  waistError == "" && hipError == ""))        
        {
            $("#btnBodyFat").prop('disabled', false);
        }else {
            $("#btnBodyFat").prop('disabled', true);
        }
    }
}