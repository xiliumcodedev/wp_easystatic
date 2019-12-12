define(['require', 'exports', 'module'], function( require, exports, module ) {

var wp_easystatic = module.config().wp_easystatic;
var staticUrl = module.config().staticUrl();

exports.events = (type, obj) => {
	switch(type){
		case 'click' :
			obj.elem.click(obj.func)
			break
		case 'change' :
			obj.elem.change(obj.func)
		default :
			break
	}
}

function Request(a){
	this.send = (prop) => {
		a({
			url : prop.url,
			method : prop.method,
			data : prop.data,
			success : prop.success,
			error : prop.error,
			timeout: 100000
		})
	}
}


exports.Request = function(a){
	return new Request(a)
}

function Scanner(j){
	
	var ajax = new Request(j.ajax)

	this.init = () => {	
		return (_) => ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_init,
			method : 'get',
			success : function(res){
				_(false, res)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.update_init = () => {	
		return (_) => ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_update_init,
			method : 'get',
			success : function(res){
				_(false, res)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}


	this.scan_static = async (_) => {
		ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_dir,
			method : 'get',
			success : function(res){
				_(false, res)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.scan_update = async (page_id) => {
		var p = new Promise((resolve, reject) => {
			ajax.send({
				url : wp_easystatic.url + '/' + staticUrl.rq_scan,
				method : 'post',
				data : { page_id : page_id},
				success : function(res){
					resolve(res)
				},
				error : function(xhr, status, err){
					reject(xhr.responseText)
				}
			})
		})

		return await p
	}
	this.scanning = async (page_id) => {	
		var p = new Promise((resolve, reject) => {
			ajax.send({
				url : wp_easystatic.url + '/' + staticUrl.rq_scan,
				method : 'post',
				data : { page_id : page_id},
				success : function(res){
					resolve(res)
				},
				error : function(xhr, status, err){
					reject(status)
				}
			})
		})

		return await p
	}	

	this.parallel = async (a) => {
		var _res = [];
		for(var i in a){
			var p = new Promise((resolve, reject) => {
				var o = a[i]
				o((err, data) => {
					resolve(data)
				})
			})

			_res.push(p)
		}
		var all = Promise.all(_res)
		return await all
	}

}

exports.Scanner = function(a){
	return new Scanner(a)
}

function static_file(j) {

	var ajax = new Request(j.ajax)

	this.init_files = () => {
		return (_) => ajax.send({
			url : wp_easystatic.url,
			method : 'post',
			data : { action : 'easystatic_init_static'},
			success : function(res){
				_(res, false)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.create_static = (page_id) => {
		var id = page_id.splice(0, 1)[0];
		
		return (_) => ajax.send({
			url : wp_easystatic.url,
			method : 'post',
			data : { action : 'easystatic_create_static', id : id},
			success : function(res){
				_(res, page_id)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.edit_static = (id) => {
		return (_) => ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_edit,
			method : 'post',
			data : { id : id},
			success : function(res){
				_(res, false)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.remove_static = (id) => {

		return (_) => ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_remove,
			method : 'post',
			data : { id : id},
			success : function(res){
				_(res, false)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})

	}

	this.review_static = (id) => {
		return (_) => ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_review,
			method : 'post',
			data : { id : id},
			success : function(res){
				_(res, false)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.update_static = (id, content) => {
		return (_) => ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_update,
			method : 'post',
			data : { id : id, content : content},
			success : function(res){
				_(res, false)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})
	}

	this.append_static = (id, content) => {
		
		var _a = (_) => ajax.send({
			url :  wp_easystatic.url + '/' + staticUrl.rq_append,
			method : 'post',
			data : { id : id, content : content},
			success : function(res){
				_(res, false)
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})

		return _a
	}

	this.do_promise = async (obj) => {
		var promise = new Promise((resolve, reject) => {
			obj((a, b) => {
				resolve([a, b]);
			})
		})
		return await promise
	}

}

exports.static_file = function(a){
	return new static_file(a)
}


function ZipFile(a){

	var ajax = new Request(a.ajax)

	this.create = (_f) => {
		
		ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_create_zip,
			method : 'post',
			data : { action : 'easystatic_createzipfile'},
			success : function(res){
				alert("Successfully Backup Created");
				window.location.reload();
				_f.hide_loader();
			},
			error : function(xhr, status, err){
				_(status, false)
			}
		})

	}

	this.restore = (_f) => {
		ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_restore_zip,
			method : 'post',
			data : { action : 'easystatic_restorezipfile', url : _f.file},
			success : function(res){
				alert("Successfully Restored");
				_f.hide_loader()
			},
			error : function(xhr, status, err){
				alert("Error status: " + err);
			}
		})
	}

	this._remove = (_f) => {
		ajax.send({
			url : wp_easystatic.url + '/' + staticUrl.rq_remove_zip,
			method : 'post',
			data : { action : 'easystatic_removezipfile', url : _f.file},
			success : function(res){
				alert("Successfully Removed");
				_f.hide_loader();
				_f.del_row();
			},
			error : function(xhr, status, err){
				alert("Error status: " + err);
			}
		})
	}

}

exports.zipfile = function(a){
	return new ZipFile(a)
}


});
