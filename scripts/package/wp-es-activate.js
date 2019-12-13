(function(j){
	
function events(type, obj){
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

events('change', {
	elem : j(".static_activate"),
	func : function(){
		var ajax = new Request(j.ajax);

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
	elem : j("#optmize"),
	func : function(){
		var ajax = new Request(j.ajax);
		ajax.send({
			url : wp_easystatic.url + '/wp-json/'+ wp_easystatic.slug +'/request/optimize/init',
			method : 'GET',
			success : function(res){
					console.log(res)
				}
			});
	}
});

})(jQuery)