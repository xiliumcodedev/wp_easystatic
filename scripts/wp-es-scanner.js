define(['require', 'exports', 'module'], function( require, exports, module) {

'use strict';

var j = require('jquery');
var _var = require("wp_es_var")();
var mainFunc = require("wp_es_func");

var Scanner = mainFunc.Scanner;
var t = null
var is_init = false;
var init_update = false;
var ids_update = [];
var page_id = [];
var scan_arr = [];
var total_count = 0
var static_dir = [];

j.noConflict();

var scan = Scanner(j)
var is_wait_gen = false;
var is_wait_update = false;
var scanning_process = async () => {
	
	t = setTimeout(scanning_process, 2000)
	var count_em = _var.console_wrapper.find('#count-em')

	if(!is_init){
		var a = await scan.init();
		var b = await scan.scan_static;
		scan.parallel([a, b]).then((res) => {
			var json = res[0]
			is_init = json['init'];
			if(!is_init){
				alert('please select post to convert');
				clearTimeout(t);
				return false
			}
			page_id = json['ids'].splice(0, json['ids'].length)
			total_count = page_id.length
			_var.percent.css('width', ((0 / total_count) * 100) + '%')
			static_dir = res[1]
			count_em.text(scan_arr.length + "/" + total_count)
		})
	}
	else if(is_init){
		
		if(is_wait_gen){
			return false
		}

		var id = page_id.splice(0, 1);

		if(!id.length){
			count_em.text("Total Page: " + scan_arr.length + "/" + total_count)
			clearTimeout(t)
			window.location.reload();
			return false
		}

		is_wait_gen = true;
		await scan.scanning(id[0]).then((res) => {
			scan_arr.push(id[0])
			_var.percent.css('width', ((scan_arr.length / total_count) * 100) + '%')
			count_em.text(scan_arr.length + "/" + total_count)
			var _res = res
			var _txt = 'generating static file for ( ' + _res['title'] + ' )';
			_var.page_log.text(_txt)
			is_wait_gen = false;
		})
	}

}

exports.scanning_process = scanning_process

var scan_update = async () => {

	t = setTimeout(scan_update, 2000)
	var count_em = _var.console_wrapper.find('#count-em')

	if(!init_update){
		var a = await scan.update_init();
		scan.parallel([a]).then((res) => {
			scan_arr = [];
			var json = res[0]
			init_update = json['init']
			if(!init_update){
				alert('please select post to convert');
				clearTimeout(t);
				return false
			}
			page_id = json['ids'].splice(0, json['ids'].length)
			total_count = page_id.length
			_var.percent.css('width', ((0 / total_count) * 100) + '%')
			count_em.text(scan_arr.length + "/" + total_count)
		})
	}
	else if(init_update){
		
		if(is_wait_update){
			return false
		}

		var id = page_id.splice(0, 1);

		if(!id.length){
			clearTimeout(t)
			if(ids_update.length){
				count_em.text("Successfully updated")
			}
			window.location.reload();
			return false
		}

		is_wait_update = true
		await scan.scan_update(id[0]).then((res) => {
			is_wait_update = false
			scan_arr.push(id[0])
			_var.percent.css('width', ((scan_arr.length / total_count) * 100) + '%')
			count_em.text(scan_arr.length + "/" + total_count)
			var _res = res
			var _txt = 'updating changes ( ' + _res['title'] + ' )';
			_var.page_log.text(_txt)
			if(_res['status'] == 1){
				ids_update.push(id[0])
			}
		})
	}
}

exports.scan_update = scan_update


})

