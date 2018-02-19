/*
	Версия 0.0.1
	Расширение для SweetAlert - http://tristanedwards.me/sweetalert

	События для классов
	.js-cancel-form onClick - при подверждение отправляет на указанную в href страницу
	.js-del-img onClick - при подверждение отправляет get запрос на href и после получения результата удаляет родительский блок.
	.js-del-item-list onClicl - при подверждение отправляет get запрос на href и после получения результата удаляет родительский блок .item. После 3 подтверждений, выполняется без запроса.


	Функции
		removeSwal($url, $title, $text) - Вызывает alert с заголовком $title и текстом $text, при подверждение выполняет смену location на $url
		dangerSwal($title, $text, $btn, $onConfirm) - Выводит alert с заголовком $title, текстом $text и надписью на кнопке $btn, при подверждение выполняет $onConfirm

*/

(function( $ ) {

	$( document ).on('click', '.js-cancel-form', function(){
	    var a = $(this);
	    dangerSwal("Отменить изменения?", "Все изменения не будут сохранены!", "Продолжить", function(){    
	    	if ($('#js-modal').length)    	     
				$('#js-modal').modal('hide');  
			else{
	    		window.location.href = a.attr('href');
	        	$(a).html(loader);
	        }
	    });
	    return false;
	});

	$( document ).on('click', '.js-del-img', function(){
	    var a = $(this);
	    dangerSwal("Удалить изображение?", "Изображение будет удалено с сервера.", "Удалить, безвозвратно", function(){            
	        $(a).html(loader);
	        $.get($(a).attr('href'), function(){
	            $(a).parent().remove();
	        });
	    });
	    return false;
	});

	var del_item_list_confirm = 0;
	$( document ).on('click', '.js-del-item-list', function(){
	    var a = $(this);
	    if (del_item_list_confirm > 2)
	        return delItemList();

	    dangerSwal(
	    	(a.data('del-title')?a.data('del-title'):"Удалить запись?"), 
	    	(a.data('del-text')?a.data('del-text'):"Запись будет удалена с сервера безвозвратно!"), 
	    	"Удалить, безвозвратно", 
	    	function(){            
		        del_item_list_confirm++;
		        var p = $(a).parent();
				var href = a.attr('href');
				p.html(loader);
			    $.get(href, function(){
			        p.closest('.item').remove();
			    });
			    return false;
	    	}
	    );
	    return false;
	});

})(jQuery);

function removeSwal(url, title, text){
	swal({   
		title: title,   
		text: text,   
		type: "warning",   
		showCancelButton: true,   
		confirmButtonColor: "#DD6B55",   
		confirmButtonText: "Удалить, безвозвратно",   
		cancelButtonText: "Отмена",   
		closeOnConfirm: true,   
		closeOnCancel: true 
	}, 
	function(isConfirm){   
		if (isConfirm)
			window.location.href = url;
	});	
	return false;
}

function dangerSwal(title, text, btn, onConfirm){
	swal({   
		title: title,   
		text: text,   
		type: "warning",   
		showCancelButton: true,   
		confirmButtonColor: "#DD6B55",   
		confirmButtonText: btn,   
		cancelButtonText: "Отмена",   
		closeOnConfirm: true,   
		closeOnCancel: true 
	}, 
	function(isConfirm){   
		if (isConfirm)
			if (onConfirm)
				onConfirm();
	});	
	return false;
}