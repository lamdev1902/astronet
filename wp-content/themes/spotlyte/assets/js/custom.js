jQuery(function($){
	var regex = /^[0-9]+$/;

	$('.hd-box').hover(function(){
		$('#header .hd-box .menu-main li ul, .provider-disclaimer, #header .hd-bg').addClass('active');
	},function(){
		$('#header .hd-box .menu-main li ul, .provider-disclaimer, #header .hd-bg').removeClass('active');
	});
	$(window).scroll(function() {		
		var h = $('.top-logo').innerHeight();
		var hg_scroll = $(window).scrollTop();
		if(hg_scroll > h){
			$('#header .hd-box').addClass('logo-in');
			$('.provider-link').removeClass('inactive');
		}else{
			$('#header .hd-box').removeClass('logo-in');
			$('.provider-link').addClass('inactive');
			$('.provider-link #header-widget-area').addClass('inactive');
			$('.provider-link .header_right').removeClass('inactive');
			$('#header .menu-bg').removeClass('inactive');
		}
	});
	
	$('.menu-main > ul > li').each(function(){
		var wd = $(this).find('a').width();
		$(this).find('ul').css('width', wd);
	});


	$('.toogle-menu').click(function(){
		$(this).toggleClass('exit');
		$('#header').toggleClass('menu-open');
		return false;
	});
	$('.menu-main ul li.menu-item-has-children').append('<i class="fal fa-chevron-down"></i>');
	$('.menu-main ul li i').click(function(){
		$(this).parent().find('ul').slideToggle();
		$(this).parent().toggleClass('active');
		return false;
	});

	
	$('.searchIcon').click(function(){
		$(this).parents('.header_right').toggleClass('inactive');
		$(this).parents(".header_right").siblings('.custom-search').toggleClass("inactive");
		if($(this).closest('.provider-link'))
		{
			$(this).closest('.provider-link').siblings('.menu-bg').toggleClass('inactive');
		}
	});

	$('.close').click(function(){
		$(this).parents('#header-widget-area').toggleClass('inactive');
		$(this).parents("#header-widget-area").siblings('.header_right').toggleClass("inactive");

		if($(this).closest('.provider-link'))
		{
			$(this).closest('.provider-link').siblings('.menu-bg').toggleClass('inactive');
		}
	});

	$("#btnCalculator").on('click', function(){
		$('#spinner').show();

		var formDataArray = $('.form.calorie-calculate').serializeArray();
    
		var jsonData = handleData(formDataArray);

		var checkActivity = 0;

		var receipValue = 0;

		$.ajax({
			url: 'https://34.163.253.54///wp-json/api/v1/calorie-calculate/',
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify(jsonData),
			success: function(response) {
				// Xử lý phản hồi từ server nếu cần
				if(response['status'] === 200)
				{
					var result = response['result']['calorie'];

					var unit = response['unit'] == 2 ? 'kJ/day' : 'Calorie/day';

					var bmr = $('.result-bmr');
					var lose = $('.result-loss');
					var gain = $('.result-gain');

					if(result)
					{
						$('.result-none').addClass('inactive');
					}

					var refLose = 0;
					var refGain = 0;

					var goalLose = $('<div class="goals"></div>');
					var goalGain = $('<div class="goals"></div>');
					var noLose = $('<p class="no-loss">You probably do not need to lose weight!</p>')

					for(const key in result){
						var item = result[key];
						

						var divItem = $('<div class="goals-item"></div>');
						var divTitle = $('<div class="goals-item__title"></div>');
						var divValue = $('<div class="goals-item__value"></div>');
						var descriptionTitle = $('<span></span>');
						var descriptionValue = $('<span></span>');

						divTitle.text(item.name);
						divValue.text(item.calorie);
						descriptionTitle.text(item.description);

						divTitle.append(descriptionTitle);

						descriptionValue.text(unit);
						divValue.append(descriptionValue);
						divItem.append(divTitle,divValue);

						if(item.goal_type == 1)
						{
							bmr.removeClass('inactive');
							bmr.find('.value').text(item.calorie);
						}

						if(item.goal_type == 2)
						{
							refLose++;
							goalLose.append(divItem);
						}

						if(item.goal_type == 3)
						{
							refGain++;
							goalGain.append(divItem);
						}
					}

					if(refLose)
					{
						lose.removeClass('inactive');
						lose.find('.goals').replaceWith(goalLose);
					}else {
						if(receipValue > 1){
							lose.removeClass('inactive');
							goalLose.append(noLose);
							lose.find('.goals').replaceWith(goalLose);
						}else {
							lose.addClass('inactive');
							lose.find('.goals').empty();
						}
					}

					if(refGain)
					{
						gain.removeClass('inactive');
						gain.find('.goals').replaceWith(goalGain);
					}else {
						gain.addClass('inactive');
						gain.find('.goals').empty();
					}

					var divZigZag1 = $('.zigzag1');
						var divZigZag2 = $('.zigzag2')

						var tbody = divZigZag1.find('.zigzag tbody');
						var tbody2 = divZigZag2.find('.zigzag tbody');

						divZigZag1.addClass('inactive');
						divZigZag2.addClass('inactive');

						$('.zigzag tbody').empty();
						tbody.empty();
						tbody2.empty();
					if(result[1] && result[1]['goal_type'] == 2 && result[1]['calorie'] >= 1500)
					{
						var zigzag1 = response['result']['zigzag_schedule_1'];
						var zigzag2 = response['result']['zigzag_schedule_2'];

						divZigZag1.removeClass('inactive');
						divZigZag2.removeClass('inactive');

						var newRow = $('<tr>');
						newRow.append('<td>Days</td>');
						if(zigzag1['mild_weight'])
						{
							newRow.append('<td>Mild Weight Loss</td>');
						}
						if(zigzag1['weight_loss'])
						{
							newRow.append('<td>Weight Loss</td>');
						}
						if(zigzag1['extreme_loss'])
						{
							newRow.append('<td>Extreme Loss</td>');
						}
						$('.zigzag tbody').append(newRow);
						$.each(zigzag1["mild_weight"], function (index, item) {
							var row1 = $('<tr>');
							var row2 = $('<tr>');

							row1.append('<td>' + item.title + '</td>');
							row1.append('<td>' + item.calorie + ' Calories</td>');

							row2.append('<td>' + zigzag2['mild_weight'][index].title + '</td>');
							row2.append('<td>' + zigzag2['mild_weight'][index].calorie + ' Calories</td>');
							if(result[2].calorie >= 1500){
								row1.append('<td>' + zigzag1["weight_loss"][index].calorie + ' Calories</td>');
								row2.append('<td>' + zigzag2["weight_loss"][index].calorie + ' Calories</td>');
							}
							if(result[3].calorie >= 1500)
							{
								row1.append('<td>' + zigzag1["extreme_loss"][index].calorie + ' Calories</td>');
								row2.append('<td>' + zigzag2["extreme_loss"][index].calorie + ' Calories</td>');
							}
							tbody.append(row1);
							tbody2.append(row2);
						});
					}
				}else {
					$('.content-right').removeClass('inactive');
					$(".content-right .result-none").empty();

					var paragraph = $('<p>').text(response['message']);
					$(".content-right .result .result-none").append(paragraph);
				}
				$('#spinner').hide();
			},
			error: function(error) {
				// Xử lý lỗi nếu có
				console.error('Error:', error);
			}
		});
	});

	$("#btnClear").on('click', function(){
		$("input[name='info[age]").val('');
		$("input[name='info[weight]").val('');
		$("input[name='info[height][feet]").val('');
		$("input[name='info[height][inches]").val('');
		$('.btn-primary').attr('disabled', 'disabled');
	})


	$('[name=receip]').change(function () {
		if($(this).val() == 3){
			$(".body-fat").removeClass('inactive');
		}else {
			$(".body-fat").addClass('inactive');
		}

		validateCalculator($(this).val());
		
	});

		$('input[name="info[gender]"]').change(function(){

			var receip = $('[name=receip]').val();

            validateCalculator(receip);
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
			var receip = $('[name=receip]').val();
			
            validateCalculator(receip);

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

			var receip = $('[name=receip]').val();

            validateCalculator(receip);

        });

		$("input[name='info[body-fat]']").change(function () {
            if($(this).val().match(regex)){
                if($(this).val() < 1){
                    $('.fat-error').text('Positive numbers only');
                }else {
                    $('.fat-error').text('');
                }
            }else {
                $(this).val('');
                $('.fat-error').text('Must input numbers!');
            }

			var receip = $('[name=receip]').val();

            validateCalculator(receip);

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

			var receip = $('[name=receip]').val();
            
            validateCalculator(receip);

        });

        $("input[name='info[height][inches]']").change(function () {
            if(!$(this).val().match(regex)){
                $(this).val('');
                $('.height-error').text('Must input numbers!');
            }else {
                $('.height-error').text('');
            }            
			var receip = $('[name=receip]').val();

            validateCalculator(receip);
            
        });
})

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


function validateCalculator(receip)
{
  var age = $("input[name='info[age]").val();
  var weight = $("input[name='info[weight]").val();
  var height = $("input[name='info[height][feet]").val();
  var bodyFat = $("input[name='info[body-fat]']").val();

  var ageError = $(".age-error").text();
  var weightError = $(".weight-error").text();
  var heightError = $(".height-error").text();
  var bodyFatError = $(".fat-error").text();

	if(receip == 3)
	{
		if( (age && weight && height && bodyFat ) && (ageError == "" && weightError == "" && heightError == "" && bodyFatError == "") )        
		{
			$("#btnCalculator").prop('disabled', false);
		}else {
			$("#btnCalculator").prop('disabled', true);
		}

	}else {
		if( (age && weight && height ) && (ageError == "" && weightError == "" && heightError == "") )        
		{
			$("#btnCalculator").prop('disabled', false);
		}else {
			$("#btnCalculator").prop('disabled', true);
		}
	}
}