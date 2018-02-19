(function($){
	var sysmsg_count = 0;
	var timers = {};
	jQuery.sysmsg = function(options){
		var options = $.extend({
		 	duration : 400,	
		 	time : 3, 	 	
		 	class : '',
		 	html : '',
		 	always : false,
		 	onclick : '',
	    }, options);	    

		var show = function(){
			var sid = 'sysmsg'+sysmsg_count;
			sysmsg_count += 1;
			timers[sysmsg_count] = null;
      		options.onclick += ' $(this).fadeOut('+options.duration+', function(){$(this).remove();});';

      		if (options.always === false){
      			timers[sid] = setInterval(function () {
      				$('#'+sid).fadeOut(options.duration, function(){$('#'+sid).remove();});
      				clearInterval(timers[sid]);
      			}, options.time*1000);
      		}

			return $('<div/>',{
				class : options.class,
				onclick : options.onclick,
				html : options.html,
				id : sid,
			}).prepend($('<button/>', {
				class : 'close',
				onclick : '$(this).parent.fadeOut('+options.duration+', function(){$(this).remove();});',
				html : '&times;', 

			}));
    	};
 
    	return this.each(show); 

	};
})(jQuery);