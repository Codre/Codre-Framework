(function() {
  jQuery.keyboardLayout = {};
  jQuery.keyboardLayout.target;
  jQuery.keyboardLayout.layout;
  jQuery.keyboardLayout.show = function(layout){
    this.layout = layout;
    $(this.target).closest('.js-input-lang-block').find('.js-input-lang').html(layout);
    $(this.target).after(this.indicator);
  };
  jQuery.keyboardLayout.hide = function(){
    $(this.target).closest('.js-input-lang-block').find('.js-input-lang').html('');
    this.target = null;
    this.layout = null;    
  };
  jQuery.fn.keyboardLayout = function(){
    this.each(function(){
      $(this).focus(function(){
        jQuery.keyboardLayout.target = $(this);
      });
      $(this).blur(function(){
        jQuery.keyboardLayout.hide();
      });
      $(this).keypress(function(e){
        var c = (e.charCode == undefined ? e.keyCode : e.charCode);
        var layout = jQuery.keyboardLayout.layout;
        if (c >= 97/*a*/  && c <= 122/*z*/ && !e.shiftKey ||
            c >= 65/*A*/  && c <= 90/*Z*/  &&  e.shiftKey ||
             (c == 91/*[*/  && !e.shiftKey ||
              c == 93/*]*/  && !e.shiftKey ||
              c == 123/*{*/ &&  e.shiftKey ||
              c == 125/*}*/ &&  e.shiftKey ||
              c == 96/*`*/  && !e.shiftKey ||
              c == 126/*~*/ &&  e.shiftKey ||
              c == 64/*@*/  &&  e.shiftKey ||
              c == 35/*#*/  &&  e.shiftKey ||
              c == 36/*$*/  &&  e.shiftKey ||
              c == 94/*^*/  &&  e.shiftKey ||
              c == 38/*&*/  &&  e.shiftKey ||
              c == 59/*;*/  && !e.shiftKey ||
              c == 39/*'*/  && !e.shiftKey ||
              c == 44/*,*/  && !e.shiftKey ||
              c == 60/*<*/  &&  e.shiftKey ||
              c == 62/*>*/  &&  e.shiftKey) && layout != 'EN') {
          layout = 'en';
        } else if (c >= 65/*A*/ && c <= 90/*Z*/  && !e.shiftKey ||
                   c >= 97/*a*/ && c <= 122/*z*/ &&  e.shiftKey) {
          layout = 'EN';
        } else if (c >= 1072/*а*/ && c <= 1103/*я*/ && !e.shiftKey ||
                   c >= 1040/*А*/ && c <= 1071/*Я*/ &&  e.shiftKey ||
                   (c == 1105/*ё*/ && !e.shiftKey ||
                    c == 1025/*Ё*/ &&  e.shiftKey ||
                    c == 8470/*№*/ &&  e.shiftKey ||
                    c == 59/*;*/   &&  e.shiftKey ||
                    c == 44/*,*/   &&  e.shiftKey) && layout != 'RU') {
          layout = 'ru';
        } else if (c >= 1040/*А*/ && c <= 1071/*Я*/ && !e.shiftKey ||
                   c >= 1072/*а*/ && c <= 1103/*я*/ &&  e.shiftKey) {
          layout = 'RU';
        }
        if (layout) {
            jQuery.keyboardLayout.show(layout);
        }
      });
    });
  };
})();
$(function(){ $(':password').keyboardLayout(); }); 