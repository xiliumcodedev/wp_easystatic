define(['require', 'exports', 'module'], function( require, exports, module ) {
	
'use strict';

var wp_easystatic = module.config().wp_easystatic;
var j = require('jquery');
var mainFunc = require("wp_es_func")
var dtAlert = require("wp_es_alert")
var events = mainFunc.events;
var static_file = mainFunc.static_file;
var zip_file = mainFunc.zipfile(j)
var codemirror = require('wp_es_cm');
var editor = codemirror.Editor();
var vars = require('wp_es_var');

j.noConflict();

function WPEasyDT_Plug(){
	var search_title = j('#search-title');
	var es_show_select = j("#es-select-show")
	var remove_link = j(".stat_remove_file");
	var $this = this
	var dt;
	var clone;
	var paginate;
	var total_tr;
	var tfoot = j('<div>')
	var tbody;
	var _tbody;
	var btn_start = 0;
	var btn_limit = 5;
	var current_id = 0;
	var settings;
	var has_search = false;
	var temp_tbody = null;
	var modal_alert = j('body').WPEasyAlert({
		'backgroundColor' : '#00000047',
	});


	this.init = function(table){
		dt = table;
		clone = table.clone()
		tbody = table.clone().find('tbody');
		_tbody = table.clone().find('tbody');
	}

	this.fill = function(){
		total_tr = tbody.find('tr').length
		paginate = Array(total_tr).fill('')
	}

	this.settings = function(obj){
		settings = obj
	}

	this.current_id = function(){
		return current_id
	}

	var removeChild = function(){
		tbody.children().remove();
	}

	var removeFooter = function(){
		tfoot.children().remove();
	}
	
	this.redraw = function(){
		var limit = 0;
		var tclone = _tbody.clone()
		var row = tclone.find('tr');
		row.each(function(i, v){
			if(i >= settings.current && limit < settings.max_rows){
				$this.edit_static(v);
				$this.do_review_static(v, i);
				$this.remove_file(v, i);
				$this.restore_backup(v, i);
				$this.remove_backup(v, i);
				limit++;
			}else{
				v.remove();
			}
		})
		clone.find('tbody').replaceWith(tclone)
	}

	this.edit_static = function(v){
		var static_edit = j(v).find('.stat_edit_file')
		events('click', {
			elem : static_edit,
			func : function(e){
				var id = j(this).data('id')
				var static_page = static_file(j);
				static_page.do_promise(
					static_page.edit_static(id)
				).then((res) => {
					var json = res[0];
					var content = json['content'];
					var link = json['link']
					var title = json['title']
					editor.setValue(content)
					events('click', {
						elem : j('#save-source'),
						func : function(){
							modal_alert.build()
							var static_page = static_file(j);
							static_page.do_promise(
								static_page.append_static(id, editor.getValue())
							).then((res) => {
								if(res){
									modal_alert.showMessage("<h1>Succesfully Saved</h1>")
								}
							});
						}
					})
					j('.edit-title').text(title)
				});
			}
		})
	}

	this.do_review_static = function(v, i){
		var stat_update_file = j(v).find('.stat_update_file');
		var id = stat_update_file.data('id');
		events('click', {
			elem : stat_update_file,
			func : function(){
				current_id = id
				var static_page = static_file(j);
				static_page.do_promise(
					static_page.review_static(id)
				).then((res) => {
					var _o = res[0]
					if(_o['status']==1){
						j('.update-title').text(_o['title']);
						codemirror.checkStaticUpdate(_o['sitecontent'], _o['editcontent']);
					} 
				})
			}
		})
	}

	this.remove_file = function(v, i){
		var remove_btn = j(v).find('.stat_remove_file');
		var id = remove_btn.data('id');
		events('click', {
			elem : remove_btn,
			func : function(){
				console.log(id)
				var static_page = static_file(j);
				static_page.do_promise(
					static_page.remove_static(id)
				).then((res) => {
					_tbody.find('tr').eq(i).remove();
					tbody.find('tr').eq(i).remove();
					paginate = Array(_tbody.find('tr').length).fill('')
					$this.refresh();
				});
			}
		})
	}

	this.restore_backup = function(v, i){
		var _btn = j(v).find('.es-restore-backup');
		events('click', {
			elem : _btn,
			func : function(e){
				e.preventDefault()
				var pl_url = wp_easystatic.baseUrl;
				_btn.after('<img src="' + pl_url + '/../assets/images/loader.gif" width="50px" style="position:absolute;top:0px;" />')
				var f = j(this).attr('href');
				zip_file.restore({
					file : f,
					hide_loader : function(){
						_btn.parent().find('img').remove();
					}
				})
			}
		})
	}

	this.remove_backup = function(v, i){
		var _btn = j(v).find('.es-remove-backup');
		events('click', {
			elem : _btn,
			func : function(e){
				e.preventDefault()
				var pl_url = wp_easystatic.baseUrl;
				var f = j(this).attr('href');
				_btn.after('<img src="' + pl_url + '/../assets/images/loader.gif" width="50px" style="position:absolute;top:0px;" />')
				zip_file._remove({
					file : f,
					hide_loader : function(){
						_btn.parent().find('img').remove();
					},
					del_row : function(){
						tbody.find('tr').eq(i).remove();
						paginate = Array(_tbody.find('tr').length).fill('')
						$this.refresh();
					}
				})
			}
		})
	}

	var paginate_btn = function(){
		var btn_count = 0
		paginate = Array(_tbody.find('tr').length).fill('')
		return paginate.map(function(v, i){
			var btn = j('<button>')
			if((i % settings.max_rows) == 0 && i < paginate.length - 1){
				btn_count++
				btn.text(btn_count)
				btn.attr({ 'data-index' : i})
				return btn
			}
			else if(i == paginate.length - 1 && (i % settings.max_rows) == 1){
				btn_count++
				btn.text(btn_count)
				btn.attr({ 'data-index' : i})
				return btn
			}
			else if(i == paginate.length - 1 && (i % settings.max_rows) == 0){
				btn_count++
				btn.text(btn_count)
				btn.attr({ 'data-index' : i})
				return btn
			}

		});
	}

	var filter_btns = function(){
		return paginate_btn().filter(function(v, i){
			return typeof v != 'undefined'
		})
	}

	var _btns;
	var footer_end_btn = function(i){
	 	if( i == _btns.length - 1 && btn_limit <= filter_btns().length - 1){
	 		var btn_gap = j("<button>")
	 		btn_gap.text("...")
	 		tfoot.append(btn_gap)
	 		events('click', {
	 			elem: btn_gap,
	 			func : function(){
	 				btn_start = filter_btns().length - 5
	 				btn_limit = filter_btns().length;
	 				footer_btns();
	 			}
	 		})

	 		filter_btns().map((_v, i) => {
	 			if(i == filter_btns().length - 1){
	 				tfoot.append(_v)
	 				_v.click(function(){
	 					tbody.children().remove();
	 					var index = j(this).data('index')
	 					btn_start = filter_btns().length - 5
	 					btn_limit = filter_btns().length;
	 					settings.current = index
	 					$this.redraw();
	 					footer_btns();
	 				})
	 			}
	 		})
	 	}
	}

	search_title.on('keyup', function(){
		var val = j(this).val()
		clone.find('tbody tr').remove() 
		settings.current = 0
		btn_start = 0;
		btn_limit = 5;
		
		if(!has_search){
			temp_tbody = _tbody.clone();
		}

		if(val != ''){
			has_search = true
		}else{
			has_search = false;
		}

		_tbody = temp_tbody.clone();
		_tbody.find("tr").each(function(i, v){
			var title = j(this).find('td').eq(1).text();
			let str = new String(title)
			var rgxp = new RegExp(val, "i");
			if(str.match(rgxp) == null){
				j(this).remove();
			}
		});

		clone.find('tbody').replaceWith(_tbody);
		paginate = Array((val != '') ? _tbody.find("tr").length : total_tr).fill('')
		$this.redraw();
		footer_btns()
		
	})

	events('change', {
		elem : es_show_select,
		func : function(){
			var max_num = j(this).val()
			btn_start = 0;
			btn_limit = 5;
			settings.max_rows = max_num
			settings.current = 0;
			$this.redraw();
			footer_btns()
		}
	})

	var footer_btns = function(){
		_btns = filter_btns().slice(btn_start, btn_limit)
		tfoot.children().remove();
		_btns.map(function(v, i){
			tfoot.append(v)
			events('click', {
					elem : j(v),
					func : function(e){
						var index = j(this).data('index')
						settings.current = index
						if(!i){
							btn_limit -= _btns.length - 1
							btn_start -= _btns.length - 1;
							if(btn_start < 1){
								btn_start = 0
								btn_limit = _btns.length;
							}
						}

						if( i == (_btns.length - 1) && btn_limit < filter_btns().length - 1){
							btn_start += _btns.length - 1;
							btn_limit += _btns.length - 1;
						}

						footer_btns();
	 					$this.redraw();
					}
				});

			footer_end_btn(i)
		});

		return tfoot
	}
	
	this.build = function(){
		var dt_wrapper = j("<div>");
		dt_wrapper.addClass('table-wrapper');
		dt_wrapper.append(clone)
		tfoot.addClass('pagination')
		dt_wrapper.append(footer_btns())
		dt.replaceWith(dt_wrapper)
		$this.redraw();
	}

	this.check_update = function(ids){
		var tr = [];
		_tbody.find('tr').each(function(i, v){
			if(j(v).find('td').length > 1){
				var id = j(v).find('td').last().find('a').data('id')
				if(ids.indexOf(id) != -1){
					var _a = j("<a>");
					_a.attr({'href' : 'javascript:void(0)', 'class' : 'stat_update_file', 'data-id' : id});
					_a.text('update');
					j(v).find('td').last().append(_a)
					tr.push(j(v));
				}
			}
		});
		
		_tbody.find('tr').remove();
		_tbody.append(tr)
		settings.current = 0;
		$this.redraw()
		footer_btns();
	}


	this.refresh = function(dt){
		$this.redraw();
		footer_btns();
	}

}

j.fn.WPEasyDT = function( options ){

	 var settings = j.extend({
            max_rows: 5,
            current: 0
     }, options );

	var plug = new WPEasyDT_Plug();
	plug.init(j(this))
	plug.fill();
	plug.settings(settings);
	plug.build()

	return plug;
};

})