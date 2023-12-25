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
})