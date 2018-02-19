/*

	Версия 0.0.5

	

	**События**

	$(codreEvents).on('initWindow') - происходит при domready и при открытие модельного окна

			@param string initiator - 'domready', 'modal' (если codreCommon.modal) или 'htmlmodal' (если codreCommon.htmlModel)

	$(codreEvents).on('ajax-form-result') - происходит при возвращение результат, после отправления формы .js-ajax-form

			@param json ajaxData - json данные полученые от сервера



	**Функции**

	codreCommon.version() - версия common

	codreCommon.editor($el, $params, $style) - подключает редактор summernote к элементу $el с параметрами $params. Параметр $style может принимать значение 'material'

	codreCommon.alert($text, $type, $timeClose) - выводить сообщение $text в блоке с классом "js-alert alert alert-$type". В $timeClose можно передать кол-во милисекунд, через сколько удалить блок

	codreCommon.modal($url, $title, $noclose) - открывает bootstrap modal вставляе в него содержимое полученое из адреса $url, подставляет заловок $title. Если указан пароаметр $noclose, то элементы для закрытия блока выводиться не будет

	codreCommon.htmlModel(html, title, noclose)- открывает bootstrap modal вставляе в него содержимое $html, подставляет заловок $title. Если указан пароаметр $noclose, то элементы для закрытия блока выводиться не будет.

	codreCommon.confirm(body, title, onConfirm)- открывает bootstrap modal вставляе в него содержимое $body, подставляет заловок $title и две кнопки отмена и подтвердить, нажатие на подтвердить вызывает функцию onConfirm.

	codreCommon.translitUrl($name) - делает транслит $name

	codreCommon.sklon(number, var1, var2_4, var5_0) - Функция склонения фразы в зависимости от числа  $number - число для склонения, $var1   - сколение если один предмет, 

															$var2_4 - склонение если от двух до четырёх предметов, $var5_0 - сколнение если пять предметов



	** События на классах **

	.js-ajax-form - при onSubmit отправляет данные при помощи $.post();

					Атрибуты: 

							data-process-name - название процесса, выводимое в jsalert

					Ответ должен быть в формате json. Если нет обработчика codreEvents.on('ajax-form-result') то проверяет два переданных параметра content, error и redirect, 

						если content = false и есть запись в error, то передаст содержимое error в .js-error внутри формы

						если content = true и есть redirect, то сделает window.location.href = redirect

						если content - строка, то выполнит jsalert(content, 'success', 3000)

						иначе выведет ошибку

	.js-ctrl-send - устанавливается для тега form. Если нажать внутри блока ctrl+s произойдёт $('.js-ctrl-send').submit();





*/	

var codreEvents = {}; // Хранилище событий

var loader = '<img class="wait" src="'+base+'js/system/loader.gif" />'; // картинка прелодера



(function( $ ) {



	/* ## События ## */



	$( document ).ready(function() {

		$(codreEvents).triggerHandler({

			type: 'initWindow',

			initiator: 'domready',

		});

	});



	$( document ).on('submit', '.js-ajax-form', function(e){

		var form = $(this);

		e.preventDefault();

		jsalert(loader+' '+(form.data('process-name')?form.data('process-name'):'Загрузка'), 'info');

		form.find('.js-error').stop(1,1).fadeOut('fast');
		form.find('[type=submit]').prop('disabled', 1);

		$.post(form.attr('action'), form.serialize(), function(data){
			form.find('[type=submit]').prop('disabled', 0);
			$('.js-alert').remove();

			if ($(codreEvents).triggerHandler({

				type: 'ajax-form-result',

				ajaxData: data,

				form : form,

 			})) return true;

			if (data.content == 'false' && typeof data.error == 'string'){

				return form.find('.js-error').stop(1,1).fadeIn('fast', function(){

					$('html, body').animate({

			        scrollTop: form.find('.js-error').offset().top-50

			    }, 200);

				}).html(data.error);					

			}

			if (data.content == 'true' && typeof data.redirect == 'string'){

				jsalert(loader+' Загрузка', 'info');

				window.location.href = data.redirect;

				return true;

			}	

			if (typeof data.content == 'string'){

				jsalert(data.content, 'success', 3000);

				return true;

			}		

			console.error('error js-ajax-form');

			console.log(data);

			return jsalert('Ошибка! Не удалось выполнить запрос. Попробуйте ещё раз.', 'danger');

		}, 'json').fail(function(){
			form.find('[type=submit]').prop('disabled', 0);
			jsalert('Неизвестная ошибка! Повторите попытку.', 'danger');

		});

	});

	

	$( document ).on('keydown', function(e){

		if (e.ctrlKey && e.keyCode == '83' && $('.js-ctrl-send').length){

			if ($('.js-ctrl-send').length > 1){

				$(e.taget).closest('.js-ctrl-send').submit();

				return false;

			}else{

				$('.js-ctrl-send').submit();

				return false;

			}

		}		

	});



})(jQuery);



/* ## Функции ## */



function CodreCommon() {



	this.version = function(){

		return  '0.0.4';

	}



	this.editor = function(el, params, style){

		$ = jQuery;

		if (!$(document.head).find("link[href='"+base+"js/system/editor/editor.css?"+this.version()+"']").length)

			$(document.head).append($('<link />', {

				type : "text/css",

				rel : "stylesheet",

				media : "all",

				href : base+"js/system/editor/editor.css?"+this.version(),

			}));

		if (!$(document.head).find("script[src='"+base+"js/system/editor/editor.js?"+this.version()+"']").length)

			$(document.head).append($('<script />', {

				type : "text/javascript",

				src : base+"js/system/editor/editor.js?"+this.version(),

			}));	

		if (!$(document.head).find("link[href='//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css']").length)

			$(document.head).append($('<link />', {

				rel : "stylesheet",

				href : "//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css",

			}));

		if (typeof style != 'undefined' && !$(document.head).find("link[href='"+base+"js/system/editor/styles/"+style+".css?"+this.version()+"']").length){

			$(document.head).append($('<link />', {

				type : "text/css",

				rel : "stylesheet",

				media : "all",

				href : base+"js/system/editor/styles/"+style+".css?"+this.version(),

			}));



			if (style == 'material' && typeof jQuery.material != 'undefined')

				$(document).on('DOMNodeInserted', '.modal-backdrop', function(e){

					jQuery.material.init();

				}); 

		}

		if (typeof params['lang'] == 'undefined')

			params['lang'] = 'ru-RU';

		if (params['lang'] != 'en-US' && !$(document.head).find("script[src='"+base+"js/system/editor/lang/"+params['lang']+".js?"+this.version()+"']").length)

			$(document.head).append($('<script />', {

				type : "text/javascript", 

				src : base+"js/system/editor/lang/"+params['lang']+".js?"+this.version(),

			}));

		if (typeof params['imageUploadUrl'] != 'undefined'){

			params['onImageUpload'] = function(files) {

			     $.each(files, function (idx, file) {

          			var filename = file.name;

          			if (typeof params['maximumImageFileSize'] != "undefined" && params.maximumImageFileSize < file.size) {

            			codreCommon.alert("Максимальный размер файла превышает", 'danger', 3000);

          			} else {

          				$.extend(new FileReader(), {

					        onload: function (e) {

					           	$.post(params['imageUploadUrl'], {data : e.target.result, filename : filename}, function(data){

					           		$(el).summernote('insertNode', $('<img />', {

					           			src : data.src

					           		})[0]);

					           	}, 'json').fail(function(){

					           		codreCommon.alert("Не удалось загрузить файл!", 'danger', 3000);

					           	});

					        },

					        onerror: function () {

					           codreCommon.alert("Не удалось загрузить файл!", 'danger', 3000);

					        }

					    }).readAsDataURL(file);					      

          			}

        		});

			}

		}

		$(el).summernote(params);

	}



	this.alert = function(text, type, timeClose) {

	   	$ = jQuery;

		$('.js-alert').remove();

		$(document.body).append($('<div />',{

			class : 'js-alert alert alert-'+type,

			html : text,

			style : 'position: fixed; top: 60px; left: 0px;z-index: 1050;',

		}));

		if (timeClose > 0)

			setTimeout(function(){

				$('.js-alert').remove();

			}, timeClose);

	}



	this.modal = function(url, title, noclose){

		$ = jQuery;

		jsalert(loader+' Загрузка', 'info');

		if (typeof title == 'undefined')

			title = '';

		$('#js-modal').remove();

		$('.modal-backdrop').remove();

		$.ajax({

			url : url,

	      	headers : { ajax : true },

	      	dataType : 'json'

		}).fail(function(){

			getModal(url, title, noclose);

		}).done(function(data){

			$('.js-alert').remove();

			var modal = $('<div />', {

				class : "modal fade",

				id : "js-modal",

				tabindex : '-1',

				role : "dialog",

				'aria-labelledby' : 'js-modalLabel',

				'aria-hidden' : 'false',

				'data-backdrop' : (noclose?'static':'true'),

			}).append(

				$('<div />', {

					class : 'modal-dialog',

				}).append(

					$('<div />', {

						class : "modal-content",

					}).append(

						$('<div />', {

							class : 'modal-header',

						}).append(

							(noclose?'':$('<button />',{

								type : 'button',

								class : 'close',

								'data-dismiss' : 'modal',

								html : '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>',

							}))

						).append(

							(title?$('<h4 />', {

								class : 'modal-title',

								id : 'js-modalLabel',

								html : title,

							}):'')

						)

					).append(

						$('<div />', {

							class : "modal-body",

							html : data.content,

						})

					)

				)

			).modal('show');

			modal.on('shown.bs.modal', function(){

				$(codreEvents).triggerHandler({

					type: 'initWindow',

					initiator: 'modal',

				});

			});

		});

		return false;

	}



	this.translitUrl = function(name){

		var tr='a b v g d e ["zh","j"] z i y k l m n o p r s t u f h c ch sh ["shh","shch"] ~ y ~ e yu ya ~ ["jo","e"]'.split(' ');

	 	var ww=''; w=name.toLowerCase().replace(/ /g,'-');

	 	for(i=0; i<w.length; ++i) { 

	   		cc=w.charCodeAt(i); ch=(cc>=1072?tr[cc-1072]:w[i]); 

	   		if(ch.length<3) 

	   			ww+=ch; 

	 	}

	 	return(ww.replace(/~/g,''));

	}



	this.htmlModel = function(html, title, noclose){

		$ = jQuery;

		if (typeof title == 'undefined')

			title = '';

		$('#js-modal').remove();

		$('.modal-backdrop').remove();

		var modal = $('<div />', {

			class : "modal fade",

			id : "js-modal",

			tabindex : '-1',

			role : "dialog",

			'aria-labelledby' : 'js-modalLabel',

			'aria-hidden' : 'false',

			'data-backdrop' : (noclose?'static':'true'),

		}).append(

			$('<div />', {

				class : 'modal-dialog',

			}).append(

				$('<div />', {

					class : "modal-content",

				}).append(

					$('<div />', {

						class : 'modal-header',

					}).append(

						(noclose?'':$('<button />',{

							type : 'button',

							class : 'close',

							'data-dismiss' : 'modal',

							html : '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>',

						}))

					).append(

						(title?$('<h4 />', {

							class : 'modal-title',

							id : 'js-modalLabel',

							html : title,

						}):'')

					)

				).append(

					$('<div />', {

						class : "modal-body",

						html : html,

					})

				)

			)

		).modal('show');

		modal.on('shown.bs.modal', function(){

			$(codreEvents).triggerHandler({

				type: 'initWindow',

				initiator: 'htmlmodal',

			});

		});

		return false;

	}



	this.confirm = function(body, title, onConfirm){

		$ = jQuery;

		if (typeof title == 'undefined')

			title = '';

		$('#js-modal').remove();

		$('.modal-backdrop').remove();

		var modal = $('<div />', {

			class : "modal fade",

			id : "js-modal",

			tabindex : '-1',

			role : "dialog",

			'aria-labelledby' : 'js-modalLabel',

			'aria-hidden' : 'false',

			'data-backdrop' : 'true',

		}).append(

			$('<div />', {

				class : 'modal-dialog',

			}).append(

				$('<div />', {

					class : "modal-content",

				}).append(

					$('<div />', {

						class : 'modal-header',

					}).append(

						$('<button />',{

							type : 'button',

							class : 'close',

							'data-dismiss' : 'modal',

							html : '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>',

						})

					).append(

						(title?$('<h4 />', {

							class : 'modal-title',

							id : 'js-modalLabel',

							html : title,

						}):'')

					)

				).append(

					$('<div />', {

						class : "modal-body",

						html : body,

					})

				).append(

					$('<div />', {

						class : 'modal-footer'

					}).append($('<button />', {

						type : 'button',

						class : 'btn btn-default',

						'data-dismiss' : 'modal',

						html : 'Отмена',

					})).append(

						$('<button />', {

							type : 'button',

							class : 'btn btn-primary',

							html : 'Подтвердить'

		 				}).on('click', function(){

		 					modal.modal('hide');

		 					onConfirm();

		 				})

					)

				)

			)

		).modal('show');

		modal.on('shown.bs.modal', function(){

			$(codreEvents).triggerHandler({

				type: 'initWindow',

				initiator: 'htmlmodal',

			});

		});

		return false;

	}



	this.sklon = function(number, var1, var2_4, var5_0) {

		last1 = number%10;

		last2 = parseInt((number%100)/10);

		if (last1 == 0 || last1 >= 5 || (last2 >= 10 && last2 <= 19))

			return var5_0;

		else

			if (last1 >=2 && last1 <= 4)

				return var2_4;

			else

				if (last1 == 1)

					return var1;

				else

					return var5_0;

	}

};



var codreCommon = new CodreCommon();

Number.prototype.formatMoney = function(c, d, t){

var n = this, 

    c = isNaN(c = Math.abs(c)) ? 2 : c, 

    d = d == undefined ? "." : d, 

    t = t == undefined ? "," : t, 

    s = n < 0 ? "-" : "", 

    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 

    j = (j = i.length) > 3 ? j % 3 : 0;

   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");

 };



/*

	DEPRECATED

*/



function jsalert(text, type, timeClose){

	return codreCommon.alert(text, type, timeClose);

}

function getModal(url, title, noclose){

	return codreCommon.modal(url, title, noclose);

}

function translitUrl(name){

	return codreCommon.translitUrl(name);

}