jQuery(function($){
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


		var formDataArray = $('.form').serializeArray();

		var jsonData = {
				'type': "1",
				'info': {
					'age': null,
					'gender': null,
					'weight': null,
					'height': {
						'feet': null,
						'inches': null
					},
					'activity': null,
					'body-fat': null
				},
				'unit': null,
				'receip': null
		};
		var checkActivity = 0;

		var receipValue = 0;
		// Lặp qua các trường form và gán giá trị vào đối tượng JSON
		formDataArray.forEach(function(e) {
			var fieldName = e.name;
			var fieldValue = e.value;


			if(fieldName === 'activity' && fieldValue > 0){
				checkActivity = 1;
			}
			// Kiểm tra và gán giá trị vào đối tượng JSON tương ứng
			if (fieldName === 'gender' || fieldName === 'activity') {
				jsonData['info'][fieldName] = fieldValue;
			} else if (fieldName === 'age' || fieldName === 'weight') {
				jsonData['info'][fieldName] = fieldValue !== '' ? fieldValue : null;
			} else if (fieldName === 'type') {
				jsonData[fieldName] = parseInt(fieldValue);
			}else if (fieldName === 'feet') {
				jsonData['info']['height']['feet'] = fieldValue !== '' ? fieldValue : null;
			} else if (fieldName === 'inches') {
				jsonData['info']['height']['inches'] = fieldValue !== '' ? fieldValue : null;
			}else if(fieldName === 'fat'){
				jsonData['info']['body-fat'] = fieldValue;
			}else if(fieldName === 'receip'){
				jsonData['receip'] = fieldValue;
				receipValue = fieldValue;
			}else if(fieldName === 'unit'){
				jsonData['unit'] = fieldValue;
			}
		});

		$.ajax({
			url: 'https://34.163.253.54/wp-json/api/v1/calculator/',
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify(jsonData),
			success: function(response) {
				// Xử lý phản hồi từ server nếu cần
				if(response['status'] === 200)
				{
					var result = response['result'];

					var unit = response['unit'] ? 'kJ/day' : 'Calorie/day';

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
						if(receipValue != 1){
							lose.removeClass('inactive');
							lose.find('.no-loss').removeClass('inactive');
							lose.find('.goals').empty();
						}
					}

					if(refGain)
					{
						gain.removeClass('inactive');
						gain.find('.goals').replaceWith(goalGain);
					}
				}
				$('#spinner').hide();
			},
			error: function(error) {
				// Xử lý lỗi nếu có
				console.error('Error:', error);
			}
		});
	});

	$('#btnClear').on('click', function() {
		$("[name=age]").val('');
		$("[name=weight]").val('');
		$("[name=feet]").val('');
		$("[name=inches]").val('');
	})

	$("[name=age]").change(function () {
		if($(this).val() < 18){
			$('.age-error').text('The minimum age that can be used in this calculator is 18. Please enter age again.');
		}else {
			$('.age-error').text('');
		}
		
	});

	$("[name=receip]").change(function () {
		if($(this).val() == 3){
			$(".body-fat").removeClass('inactive');
		}else {
			$(".body-fat").addClass('inactive');
		}
		
	});

	$("[name=weight]").change(function () {
		if($(this).val() < 40 || $(this).val() > 600){
			$('.weight-error').text('Please enter a weight between 40 and 600 pounds.');
		}else {
			$('.weight-error').text('');
		}

	});

	$("[name=fat]").change(function () {
		if($(this).val() < 3){
			$('.fat-error').text('Please provide a reasonable body fat percentage..');
		}else {
			$('.weight-error').text('');
		}

	});

	$("[name=feet]").change(function () {
		if($(this).val() < 4 || $(this).val() > 8)
		{
			$('.height-error').text('Height value must be between 4 and 8 feet.');
		}else {
			$('.height-error').text('');
		}
	});

	$("[name=inches]").change(function () {
		if(!$("[name=feet]").val()){
			$('.height-error').text('Height value must be between 4 and 8 feet.');
		}else {
			$('.height-error').text('');
		}
	});
})

function validateCalculator()
{
	var age = $("[name=age]").val();
	var weight = $("[name=weight]").val();
	var height = $("[name=feet]").val();
	var heightError = $('height-error').text();
	var weightError = $('weight-error').text();
	var ageError = $('age-error').text();

	if(age && weight && height) {
		if(height === '' && weight === '' && age === ''){
			console.log('successs');
		}
	}
}

