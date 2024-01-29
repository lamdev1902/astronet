jQuery(function($){

    $(document).ready( function() {

        var regex = /^[0-9]+$/;

        var type = '';

        $('input[name="info[gender]"]').change(function(){
            if($(this).val() == 2) {
                $('.hip').removeClass('inactive')
            }else {
                $('.hip').addClass('inactive')
            }

            var form = $(this).parents('form');

            if(form.hasClass('bmr-calculate'))
            {
                type = 'bmr';
            }else if(form.hasClass('bmi-calculate'))
            {
                type = 'bmi'
            }else if(form.hasClass('ideal-weight-calculate'))
            {
                type = 'ideal-weight';
            }else if(form.hasClass('lean-body-calculate'))
            {
                type= 'lean-body';
            }

            validateForm(type);
        })

        $("input[name='info[age]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1){
                    $('.age-error').text('Positive numbers only');
                }else {
                    $('.age-error').text('');
                }
            }else {
                $(this).val('');
                $('.age-error').text('Must input numbers!');
            }

            var form = $(this).parents('form');

            if(form.hasClass('bmr-calculate'))
            {
                type = 'bmr';
            }else if(form.hasClass('bmi-calculate'))
            {
                type = 'bmi'
            }else if(form.hasClass('ideal-weight-calculate'))
            {
                type = 'ideal-weight';
            }else if(form.hasClass('lean-body-calculate'))
            {
                type= 'lean-body';
            }

            
            validateForm(type);

        });

        $("input[name='info[weight]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1){
                    $('.weight-error').text('Positive numbers only');
                }else {
                    $('.weight-error').text('');
                }
            }else {
                $(this).val('');
                $('.weight-error').text('Must input numbers!');
            }

            var form = $(this).parents('form');

            
            if(form.hasClass('bmr-calculate'))
            {
                type = 'bmr';
            }else if(form.hasClass('bmi-calculate'))
            {
                type = 'bmi'
            }else if(form.hasClass('lean-body-calculate'))
            {
                type= 'lean-body';
            }

            
            validateForm(type);

        });

        $("input[name='info[height][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1)
                {
                    $('.height-error').text('Positive numbers only');
                }else {
                    $('.height-error').text('');
                }
            }else {
                $(this).val('');
                $('.height-error').text('Must input numbers!');
            }
            
            var form = $(this).parents('form');

            var type = '';

            if(form.hasClass('bmr-calculate'))
            {
                type = 'bmr';
            }else if(form.hasClass('bmi-calculate'))
            {
                type = 'bmi'
            }else if(form.hasClass('ideal-weight-calculate'))
            {
                type = 'ideal-weight';
            }else if(form.hasClass('healthy-weight-calculate'))
            {
                type = 'healthy-weight';
            }else if(form.hasClass('lean-body-calculate'))
            {
                type= 'lean-body';
            }

            
            validateForm(type);

        });

        $("input[name='info[height][inches]']").change(function () {
            if(!$(this).val().match(regex)){
                $(this).val('');
                $('.height-error').text('Must input numbers!');
            }else {
                $('.height-error').text('');
            }
            var form = $(this).parents('form');

            var type = '';

            if(form.hasClass('bmr-calculate'))
            {
                type = 'bmr';
            }else if(form.hasClass('bmi-calculate'))
            {
                type = 'bmi'
            }else if(form.hasClass('ideal-weight-calculate'))
            {
                type = 'ideal-weight';
            }else if(form.hasClass('lean-body-calculate'))
            {
                type= 'lean-body';
            }

            
            validateForm(type);
            
        });

        $("input[name='info[neck][feet]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1)
                {
                    $('.neck-error').text('Positive numbers only');
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
                    $('.waist-error').text('Positive numbers only');
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

            $('.btn-primary').attr('disabled', 'disabled');
        })

        $("#btnBodyFat").on('click', function(){

            $('#spinner').show();

            var formDataArray = $('.form.bodyfat-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/bodyfat-calculate/',
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

        $('#btnBmr').on('click', function(){
            $('#spinner').show();

            var formDataArray = $('.form.bmr-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/bmr-calculate/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(jsonData),
                success: function(response) {
                    // Xử lý phản hồi từ server nếu cần
                    if(response['status'] === 200)
                    {
                        var result = response['result']['bmr'];


                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();
                        $.each(result, function (key, value) {
                            
                            $('.main-result').text('BMR: ' + value + ' Calories/day');
                            
                        });
                    }else {
                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();

                        var paragraph = $('<p>').text(response['message']);
                        $(".content-right .result").append(paragraph);
                    }
                    $('#spinner').hide();
                },
                error: function(error) {
                    // Xử lý lỗi nếu có
                    console.error('Error:', error.responseJSON.message);
                }
            });
        })

        $('#btnBmi').on('click', function(){
            $('#spinner').show();

            var formDataArray = $('.form.bmi-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/bmi-calculate/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(jsonData),
                success: function(response) {
                    // Xử lý phản hồi từ server nếu cần
                    if(response['status'] === 200)
                    {
                        var result = response['result']['bmi'];


                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();
                        
                        $('.main-result').text('BMI: ' + result.bmi + ' kg/m2 ' + '( ' + result.description + ' ) ');

                        var ul = $('<ul></ul');

                        var liRange = $('<li>').text(result.healthy_range);

                        var liIdeal = $('<li>').text(result.ideal_weight);

                        var liPrime = $('<li>').text("BMI Prime: " + result.prime);

                        var liPonderal = $('<li>').text("Ponderal Index: " + result.ponderal + ' kg/m3');

                        if(result.type != 4){
                            var liPropose = $('<li>').text(result.propose);
                            ul.append(liRange, liIdeal, liPropose ,liPrime, liPonderal);
                        }else {
                            ul.append(liRange, liIdeal ,liPrime, liPonderal);
                        }

                        $(".content-right .result").append(ul);
                    }else {
                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();

                        var paragraph = $('<p>').text(response['message']);
                        $(".content-right .result").append(paragraph);
                    }
                    $('#spinner').hide();
                },
                error: function(error) {
                    // Xử lý lỗi nếu có
                    console.error('Error:', error.responseJSON.message);
                }
            });
        })

        $('#btnIdealWeight').on('click', function(){
            $('#spinner').show();

            var formDataArray = $('.form.ideal-weight-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/idealweight-calculate/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(jsonData),
                success: function(response) {
                    // Xử lý phản hồi từ server nếu cần
                    if(response['status'] === 200)
                    {
                        var result = response['result']['ideal_weight'];

                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();
                        var div = $(`<div class="item"></div>`);
                        var pTitle = $("<p class='title' style='font-weight:600; background:#336699;border: 1px solid #114477;color: #fff;'>").text('Formula');
                        var pValue = $("<p class='value' style='font-weight:600; background:#336699;border: 1px solid #114477;color: #fff;'>").text('Ideal Weight');
                        div.append(pTitle,pValue);

                        // Thêm thẻ <p> vào container
                        $(".content-right .result").append(div);
                        var check = 0;
                        $.each(result, function (key, value) {

                            var div = $(`<div class="item"></div>`);
                            var pTitle = $("<p class='title'>").text(value.title);
                            var pValue = $("<p class='value'>").text(value.pounds+' lbs');
                            div.append(pTitle,pValue);

                            // Thêm thẻ <p> vào container
                            $(".content-right .result").append(div);
                            
                        });
                    }else {
                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();

                        var paragraph = $('<p>').text(response['message']);
                        $(".content-right .result").append(paragraph);
                    }
                    $('#spinner').hide();
                },
                error: function(error) {
                    // Xử lý lỗi nếu có
                    console.error('Error:', error.responseJSON.message);
                }
            });
        })

        $('#btnHealthyWeight').on('click', function(){
            $('#spinner').show();

            var formDataArray = $('.form.healthy-weight-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/healthyweight-calculate/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(jsonData),
                success: function(response) {
                    // Xử lý phản hồi từ server nếu cần
                    if(response['status'] === 200)
                    {
                        var result = response['result']['healthy_weight'];

                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();
                        var pTitle = $("<p class='title'>").text(result);

                        // Thêm thẻ <p> vào container
                        $(".content-right .result").append(pTitle);
                    }else {
                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();

                        var paragraph = $('<p>').text(response['message']);
                        $(".content-right .result").append(paragraph);
                    }
                    $('#spinner').hide();
                },
                error: function(error) {
                    // Xử lý lỗi nếu có
                    console.error('Error:', error.responseJSON.message);
                }
            });
        })
        $('#btnLeanBody').on('click', function(){
            $('#spinner').show();

            var formDataArray = $('.form.lean-body-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/leanbodymass-calculate/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(jsonData),
                success: function(response) {
                    var div = $('.lean-body-table');
                    var tbody = div.find('.lean-body tbody');
                    // Xử lý phản hồi từ server nếu cần
                    if(response['status'] === 200)
                    {
                        var result = response['result'];

                        div.removeClass('inactive');
                        $('.content-right').removeClass('inactive');
                        
						tbody.empty();

                        var newRow = $('<tr>');
						newRow.append('<td>Formular</td>');
						newRow.append('<td>Lean Body</td>');
						newRow.append('<td>Body Fat</td>');
                        tbody.append(newRow);

                        $.each(result, function (index, item) {
							var row1 = $('<tr>');

							row1.append('<td>' + item.title + '</td>');
							row1.append('<td>' + item.lean_body + ' ( ' + item.percent +'% )' + '</td>');
							row1.append('<td>' + item.body_fat + ' %</td>');

							tbody.append(row1);
						});

                    }else {
                        $('.content-right').removeClass('inactive');
                        tbody.empty();

                        var paragraph = $('<p>').text(response['message']);
                        $(".content-right .result").append(paragraph);
                    }
                    $('#spinner').hide();
                },
                error: function(error) {
                    // Xử lý lỗi nếu có
                    console.error('Error:', error.responseJSON.message);
                }
            });
        })

        $('#btnArmyBodyFat').on('click', function(){
            $('#spinner').show();

            var formDataArray = $('.form.army-body-fat-calculate').serializeArray();
    
            var jsonData = handleData(formDataArray);

            $.ajax({
                url: 'https://dev.ehproject.org//wp-json/api/v1/army-bodyfat/',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(jsonData),
                success: function(response) {
                    // Xử lý phản hồi từ server nếu cần
                    if(response['status'] === 200)
                    {
                        var result = response['result']['army_bodyfat'];

                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();
                        var pTitle = $("<p class='title'>").text(result);

                        // Thêm thẻ <p> vào container
                        $(".content-right .result").append(pTitle);
                    }else {
                        $('.content-right').removeClass('inactive');
                        $(".content-right .result").empty();

                        var paragraph = $('<p>').text(response['message']);
                        $(".content-right .result").append(paragraph);
                    }
                    $('#spinner').hide();
                },
                error: function(error) {
                    // Xử lý lỗi nếu có
                    console.error('Error:', error.responseJSON.message);
                }
            });
        })
    });
});

function handleData($form)
{
    var jsonData = {};

    $.each($form, function(i, field) {
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

    return jsonData;
}

function validateForm(type = '')
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

    if( type === 'bmr')
    {
        if( (age && weight && height ) && (ageError == "" && weightError == "" && heightError == "") )        
        {
            $("#btnBmr").prop('disabled', false);
        }else {
            $("#btnBmr").prop('disabled', true);
        }
    }else if(type === 'bmi') {
        if( (age && weight && height ) && (ageError == "" && weightError == "" && heightError == "") )        
        {
            $("#btnBmi").prop('disabled', false);
        }else {
            $("#btnBmi").prop('disabled', true);
        }
    }else if(type === 'ideal-weight')
    {
        if( (age && height ) && (ageError == "" && heightError == "") )        
        {
            $("#btnIdealWeight").prop('disabled', false);
        }else {
            $("#btnIdealWeight").prop('disabled', true);
        }
    }else if(type === 'healthy-weight')
    {
        if( (height ) && (heightError == "") )        
        {
            $("#btnHealthyWeight").prop('disabled', false);
        }else {
            $("#btnHealthyWeight").prop('disabled', true);
        }
    }else if(type === 'lean-body')
    {
        if( (weight && height) && (weightError == "" && heightError == "") )        
        {
            $("#btnLeanBody").prop('disabled', false);
        }else {
            $("#btnLeanBody").prop('disabled', true);
        }
    }
    else {
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
}