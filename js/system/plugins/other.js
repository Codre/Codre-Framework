/**
* Плагин фиксации елемента при прокрутке
* @param uID - id елемента
* @param params - параметры которые будут установлены
* @comment Требует доработки
*/

function elementFix(uID, params){
	var defaultParamFix = {};
	var element = $(uID);
	$(window).scroll(function(){
		if (!defaultParamFix[uID]){
			defaultParamFix[uID] = {
				'position':element.css('position'),
				'left':element.css('lift'),
				'right':element.css('right'),
				'top':element.css('top'),
				'bottom':element.css('bottom'),
				'z-index':element.css('z-index'),
				'offsetTop':element.offset().top-element.height(),				
			};
		}
		if ($(window).scrollTop() > defaultParamFix[uID].offsetTop){
			var params = {'position' : 'fixed', 'z-index': 100};
			if (top != null)
				params['top'] = top;
			if (left != null)
				params['left'] = left;
			if (right != null)
				params['right'] = right;
			if (bottom != null)
				params['bottom'] = bottom;		
			if (zIndex != null)
				params['z-index'] = zIndex;			
			element.css(params);
		}else{
			element.css(defaultParamFix[uID]);
		}
	});
}