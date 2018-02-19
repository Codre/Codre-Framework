$.fn.drawer = function(type){
	var el = $(this);
	var drawer = $(el.data('drawer'));

	switch (type){
		case undefined:
			drawer.data('drawer-css-position', drawer.css('position'));
			drawer.data('drawer-css-top', drawer.css('top'));
			drawer.data('drawer-css-right', drawer.css('right'));
			drawer.data('drawer-css-left', drawer.css('left'));
			el.on('click', function(){
				if (el.data('drawer-status') != undefined && el.data('drawer-status').length > 0)
					el.drawer('hide');
				else
					el.drawer('show');
			});
		break;
		case 'hide':
			el.data('drawer-status', '');
			drawer.stop(1,1).show();
			callback = function(){
				$('.drawer-background').remove();
				drawer.css({
					position : drawer.data('drawer-css-position'),
					top : drawer.data('drawer-css-top'),
					left : drawer.data('drawer-css-left'),
					right : drawer.data('drawer-css-right'),
				}).hide();
			};

			if (drawer.hasClass('right'))
				drawer.animate({ right : '-'+drawer.width() }, 200, callback);
			else
				drawer.animate({ left : '-'+drawer.width() }, 200, callback);
			if (el.data('drawer-class').length > 0)
				el.toggleClass(el.data('drawer-class'));
		break;
		case 'show':
			el.data('drawer-status', 'on');
			drawer.stop(1,1).hide();
			callback = function(){
				$('.drawer-background').remove();
				$(document.body).append($('<div />', {
					class : 'drawer-background'
				}).on('click', function(){
					el.drawer('hide');
				}));
			}
			drawer.attr('style','display: block !important').css({ position : 'fixed', top : 0 });
			if (drawer.hasClass('right'))
				drawer.css({ right : drawer.width()*-1 }).animate({ right : 0 }, 200, callback);
			else
				drawer.css({ left : drawer.width()*-1 }).animate({ left : 0 }, 200, callback);
			if (el.data('drawer-class').length > 0)
				el.toggleClass(el.data('drawer-class'));	
		break;
	}
};