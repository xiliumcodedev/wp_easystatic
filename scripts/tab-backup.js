
if (typeof jQuery === 'function') {
  	define('jquery', function () { return jQuery; });
}

requirejs(["jquery", "wp_es_var", "wp_es_func", "wp_es_cm", "wp_es_dt", "wp_es_alert"], 
	function(j, wp_es_var, wp_es_func, wp_es_cm, wp_es_dt, wp_es_alert) {
	
	var events = wp_es_func.events;
	var vars = wp_es_var()
	var ajax_func = wp_es_func;
	var request = ajax_func.Request
	var zip_file = ajax_func.zipfile(j)
	
	j(document).ready(function(){
		
		var dt = j('#backup-dt').WPEasyDT();
		
		events('click', {
			elem : vars.create_backup,
			func : function(){
				vars.es_loader.css({'display' : 'block'});
				zip_file.create({
					hide_loader : function(){
						vars.es_loader.removeAttr('style')
					}
				});
			}
		})

		events('change', {
			elem : vars.static_activate,
			func : function(){
				var ajax = request(j.ajax);
				if(j(this).is(":checked")){
					ajax.send({
					url : wp_easystatic.url + 'wp-json/'+ wp_easystatic.slug +'/request/static/enable',
					method : 'post',
					data : { action : 'easystatic_rewrite_ht', 'static_active_field' : 1},
					success : function(res){
							console.log(res)
						}
					});
				}else{
					ajax.send({
					url : wp_easystatic.url + 'wp-json/'+ wp_easystatic.slug +'/request/static/disable',
					method : 'post',
					data : { action : 'easystatic_rewrite_ht', 'static_active_field' : 0},
					success : function(res){
							console.log(res)
						}
					});
				}
			}
		})

	})
})