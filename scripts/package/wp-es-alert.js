define(['require', 'exports', 'module'], function( require, exports, module ) {

var j = require('jquery');

j.noConflict();


j.fn.WPEasyAlert = function(options){

	var $this = this;

	var settings = j.extend({
            backgroundColor : 'rgba(0, 0, 0, 0.50)',
            animatedStyle : 'linear',
     }, options );


	this.background = function(){
		var elem = j("<div>");
		elem.css({
			'backgroundColor' : settings.backgroundColor,
			'position' : 'fixed',
			'height' : '100%',
			'width' : '100%',
			'top' : '0px',
			'zIndex' : '999999'
		});
		return elem
	}

	this.modal = function(){
		var elem = j('<div>');
		elem.css({
			'backgroundColor' : '#FFF',
			'position' : 'absolute',
			'width' : '50%',
			'left' : '20%',
			'top' : '30%'
		})

		return elem
	}

	this.topModal  = function(bg, md){
		var elem = j('<div>');
		var close = j("<a>");
		close.attr('src', 'javascript:void(0)');
		close.addClass('modal_close_btn');
		close.click(function(){
			md.remove();
			bg.remove()
			j("#TB_closeWindowButton").trigger('click')
		})
		elem.css({
			'backgroundColor' : '#FFF',
			'borderBottom' : 'solid 1px #bdb9b9',
			"padding": "5px",
			"textAlign" : 'right'
		});
		elem.append(close)
		return elem
	}

	this.messageBox = function(textMessage){
		var elem = j('<div>');
		var span = j('<span>');
		span.css({
			'textAlign' : 'center'
		});
		span.html(textMessage)
		elem.append(span);
		return elem
	}

	this.showMessage = function(textMessage){
		var bg = $this.background();
		j($this).append(bg);
		var md = $this.modal();
		md.append($this.topModal(bg, md))
		md.append($this.messageBox(textMessage))
		bg.append(md)
	}

	return $this;
}

});