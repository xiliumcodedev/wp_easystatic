
if (typeof jQuery === 'function') {
  	define('jquery', function () { return jQuery; });
}

requirejs(["jquery", "wp_es_var", "wp_es_scanner", "wp_es_func", "wp_es_cm", "wp_es_alert"], 
	function(j, wp_es_var, wp_es_scanner, wp_es_func, wp_es_cm, wp_es_alert) {

	var events = wp_es_func.events;
	var Request = wp_es_func.Request;
	var static_file = wp_es_func.static_file(j);
	var vars = wp_es_var()
	var codemirror = wp_es_cm;

 	j(document).ready(function(){
 		var dt = j('#datatable').WPEasyDT();
 		var modal_alert = j('body').WPEasyAlert({
			'backgroundColor' : '#00000047',
		});

		events('change', {
			elem : vars.static_activate,
			func : function(){
				var ajax = Request(j.ajax);

				if(j(this).is(":checked")){
					ajax.send({
					url : wp_easystatic.url + '/wp-json/'+ wp_easystatic.slug +'/request/static/enable',
					method : 'post',
					data : { action : 'easystatic_rewrite_ht', 'static_active_field' : 1},
					success : function(res){
							console.log(res)
						}
					});
				}else{
					ajax.send({
					url : wp_easystatic.url + '/wp-json/'+ wp_easystatic.slug +'/request/static/disable',
					method : 'post',
					data : { action : 'easystatic_rewrite_ht', 'static_active_field' : 0},
					success : function(res){
							console.log(res)
						}
					});
				}
			}
		})


		events('click', {
			elem : vars.static_merge_update,
			func : function(){
				var cm_content = codemirror.mergeView().editor().getValue();
				var id = dt.current_id()
				modal_alert.build()
				static_file.do_promise(
					static_file.update_static(id, cm_content)
				).then((res) => {
					var _o = res[0]
					if(_o['status']==1){
						modal_alert.showMessage("<h1>" + _o['msg'] + "</h1>");
					}
				});
			}
		})

		events('click', {
			elem : vars.static_paste_update,
			func : function(){
				var static_content = codemirror.mergeView().editor().getValue();
				var site_content = codemirror.mergeView().leftOriginal().getValue();
				codemirror.mergeView().editor().setValue(site_content)
			}
		})

		events('click', {
			elem : vars.start_scan,
			func : function(){
				wp_es_scanner.scanning_process();
			}
		})

		events('click', {
			elem : vars.update_scan,
			func : function(){
				var type = j(this).data('type')
				wp_es_scanner.scan_update();
			}
		})

 	})
});