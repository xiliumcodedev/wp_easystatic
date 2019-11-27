define(['require', 'exports', 'module'], function( require, exports, module ) {

var j = require('jquery');

j.noConflict();


j.fn.WPEasyAlert = function(options){

	var $this = this;

	var settings = j.extend({
            backgroundColor : '#000',
            animatedStyle : 'linear',
     }, options );


	var background = function(){
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

	var modal = function(){
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

	var topModal  = function(_){
		var elem = j('<div>');
		var close = j("<a>");
		close.attr('src', 'javascript:void(0)');
		close.addClass('modal_close_btn');
		close.click(function(){
			_['md'].animate({
				'opacity' : '0'
			}, 200, 'swing', function(){
				_['bg'].remove()
				j("#TB_closeWindowButton").trigger('click')
			});
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

	var messageBox = function(textMessage){
		var elem = j('<div>');
		var span = j('<span>');
		span.css({
			'textAlign' : 'center'
		});
		span.html(textMessage)
		elem.append(span);
		return elem
	}

	this.build = function(){
		var bg = background();
		j($this).append(bg);
		this.showMessage = function(textMessage){
			var md = modal();
			md.append(topModal({ md : md, bg : bg}))
			md.append(messageBox(textMessage))
			bg.append(md)
		}
	}

	return this;
}

});