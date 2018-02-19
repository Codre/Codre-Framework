/**
* droidModal plugin
* © Codre Develope studio 2013 
*/

(function($){
    var droidModal = {   
        init : function(options) { 
            if (this.options)
                this.options = $.extend(this.options, options);
            else
                this.options = $.extend({
                    text          : null,
                    href          : null,
                    type          : 'ajax',
                    overlayClass  : 'modal-overlay',
                    contentClass  : 'modal-content',
                    closeBtnClass : 'close',
                    backdropClass : 'modal-backdrop',
                    titleClass    : 'modal-title',
                    title         : null,
                    events        : {'.droidModal' : 'click'},
                    preloader     : 'Loading...',
                    dataType      : 'text',
                    dataParam     : null,
                    errorText     : 'Loading error',
                    autoCenter    : true,
                    headers       : {'ajax' : true},
                    closeBtn      : true,
                    closeOnBgClick: true,
                }, options);            
            return this.options;                 
        },
        close : function(callback){
            if (typeof callback != 'function') callback = function(){};
            if (!$('#droidModal-overlay').length) return callback();
            $('#droidModal-backdrop').stop(1,1).fadeOut(400);
            $('#droidModal-overlay').stop(1,1).fadeOut(400, function(){
                $('#droidModal-overlay').remove();
                $.droidModal({text : ''});
                 return callback();
            });
        },
        show : function(options){
            var options = $.droidModal(options);
            return $.droidModal('close', function(){                
                if (options.text)
                    return $.droidModal('loadModal');                
                else if (options.href && options.type == 'ajax')
                    return $.droidModal('ajax', options.href);
                else{
                    console.log(options);
                    $.error('Нет условий для параметров');
                }
            });
                
        },
        ajax : function(href){           
            var options = this.options;
            
            $.ajax({
                url : href,
                dataType : options.dataType,
                beforeSend : function() {
                    var oldOptions = {
                        closeBtn : options.closeBtn,
                        loseOnBgClick : options.closeOnBgClick,
                        title : options.title,
                    };
                    options = $.droidModal({text : options.preloader, closeBtn : false, closeOnBgClick : true, title : null}); 
                    $.droidModal('loadModal');  
                    $.droidModal(oldOptions); 
                },
                headers : this.options.headers,
            })
            .done(function(data) {
                if (options.dataType == 'json' && options.dataParam){
                    options = $.droidModal({text : data[options.dataParam]}); 
                    $.droidModal('loadModal');  
                    $.droidModal({title : null}); 
                    if (typeof data.script != 'undefined')
                        eval(data.script);
                }else{
                    options = $.droidModal({text : data}); 
                    $.droidModal('loadModal');  
                    $.droidModal({title : null}); 
                }
            })
            .fail(function() {
                options = $.droidModal({text : options.errorText}); 
                 $.droidModal('loadModal');  
            });
        },
        loadModal : function(){
            $('#droidModal-overlay').remove();
            $(document.body).append(
                $('<div />', {
                    class : this.options.overlayClass,
                    id    : 'droidModal-overlay',
                }).hide().append(
                    $('<div/>', {
                        class : 'modal-dialog',
                        id    : 'droidModal-dialog'
                    }).append(
                        $('<div />', {
                            class : this.options.contentClass,
                            id    : 'droidModal-content',
                        }).append(
                            $('<div />', {'html' : this.options.text, 'id' : 'droidModal-content-block'})
                        )
                    )
                )
            ); 
            if (this.options.closeBtn)
                $('#droidModal-content').prepend(
                    $('<button/>', {
                        class   : this.options.closeBtnClass,
                        html    : "&times;",
                    }).click(function(){jQuery.droidModal('close');})
                );
            if (this.options.title)
                $('#droidModal-content').prepend(
                    $('<div />', {
                        class   : this.options.titleClass,
                        id      : 'droidModal-title',
                        html    : this.options.title,
                    })
                );
            if (this.options.closeOnBgClick)
                $('#droidModal-overlay').click(function(ev){
                    if ($(ev.target).attr('id') == $('#droidModal-overlay').attr('id'))
                        $.droidModal('close');
                });
            $(document.body).append($('<div/>', {
                class : 'backdropClass',
                id    : 'droidModal-backdrop',
            }).hide());
            $('#droidModal-overlay').stop(1,1).fadeIn(400);
            $('#droidModal-backdrop').stop(1,1).fadeIn(400);
            if (this.options.autoCenter){
                var nH = $('#droidModal-dialog').height()/2+100;
                if ($('#droidModal-dialog').height() > $(window).height())
                    $('#droidModal-dialog').css('top', '100px');
                else
                    $('#droidModal-dialog').css('margin-top', '-'+nH+'px');
                var nW = $('#droidModal-dialog').width()/2;
                $('#droidModal-dialog').css('margin-left', '-'+nW+'px');
            }
        }
    };

    $.droidModal = function(method) {        
        if (droidModal[method]) {
            return droidModal[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return droidModal.init.apply(this, arguments);
        } else {
            $.error( 'Метод с именем ' +  method + ' не существует для $.droidModal' );
        } 
    };
})(jQuery);