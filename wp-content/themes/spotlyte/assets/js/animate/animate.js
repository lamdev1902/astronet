jQuery(function() {
/*  [ Animate Elements ]
 - - - - - - - - - - - - - - - - - - - - */
var $animation_elements_in = $('.animated.fade-in');
var $animation_elements_left = $('.animated.fade-left');
var $animation_elements_right = $('.animated.fade-right');
var $animation_elements_opacity = $('.animated.opacity-in');
var $window = $(window);

function check_if_in_view() {
	var window_height = $window.height();
	var window_top_position = $window.scrollTop();
	var window_bottom_position = (window_top_position + window_height);

	$.each($animation_elements_in, function() {
		var $element = $(this);
		var element_height = $element.outerHeight();
		var element_top_position = $element.offset().top;
		var element_bottom_position = (element_top_position + element_height);

		//check to see if this current container is within viewport
		if ((element_bottom_position >= window_top_position+100) &&
			(element_top_position <= window_bottom_position)) {
			$element.addClass('animated fadeInUp');
		}
	});

	$.each($animation_elements_left, function() {
		var $element = $(this);
		var element_height = $element.outerHeight();
		var element_top_position = $element.offset().top;
		var element_bottom_position = (element_top_position + element_height);

		//check to see if this current container is within viewport
		if ((element_bottom_position >= window_top_position+100) &&
			(element_top_position <= window_bottom_position)) {
			$element.addClass('animated fadeInLeft');
		}
	});

	$.each($animation_elements_right, function() {
		var $element = $(this);
		var element_height = $element.outerHeight();
		var element_top_position = $element.offset().top;
		var element_bottom_position = (element_top_position + element_height);

		//check to see if this current container is within viewport
		if ((element_bottom_position >= window_top_position+100) &&
			(element_top_position <= window_bottom_position)) {
			$element.addClass('animated fadeInRight');
		}
	});
	$.each($animation_elements_opacity, function() {
		var $element = $(this);
		var element_height = $element.outerHeight();
		var element_top_position = $element.offset().top;
		var element_bottom_position = (element_top_position + element_height);

		//check to see if this current container is within viewport
		if ((element_bottom_position >= window_top_position+100) &&
			(element_top_position <= window_bottom_position)) {
			$element.addClass('animated fadeIn');
		}
	});
}
$window.on('scroll resize', check_if_in_view);
$window.trigger('scroll');
});