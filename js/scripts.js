$(document).ready(function() {
	function showHiddenParagraphs() {
		$("p.hidden").fadeIn(500);
	}
	setTimeout(showHiddenParagraphs, 1000);

	$('.content_tab').eq('0').show();
	$('.left-menu').find('li').bind('click', function() {
		$('.content_tab').fadeOut('normal');
		$('.left-menu').find('li').removeClass('active');
		var index = $(this).index();
		$(this).addClass('active');
		$(".content_tab").eq(index).fadeIn('slow');
	});

	$('.operandsName').each(function() {
		$(this).before('<ul class="parsing_tab_nav">' +
					   '<li class="parsing_tab active">Идентификаторы</li>' +
					   '<li class="parsing_tab">Операторы</li>' +
						'</ul>');
	});

	$('.parsing_tab_nav').find('li').on("click", function() {
		$(this).parent().find('li').removeClass('active');
		$(this).addClass('active');

		$index = $(this).index();
		$(this).parents('div.parsing-code-element').find('ul:not(.parsing_tab_nav)').hide();
		$(this).parents('div.parsing-code-element').find('ul').eq($index + 1).show();
	});

	$('.parsing-code-element').each(function () {
		$(this).find('ul').eq('2').hide();
	});
});