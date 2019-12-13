requirejs.config({
	baseUrl: wp_easystatic.baseUrl,
	deps: ["main"],
	config : {
			wp_es_func : {
				wp_easystatic : wp_easystatic,
				staticUrl : function(){
					var wp_json = 'wp-json';
					var slug = wp_easystatic.slug;
					var base_url = wp_json + '/' + slug;
					return {
						rq_init : base_url + '/request/ids/init',
						rq_dir :  base_url + '/request/ids/directories',
						rq_scan : base_url + '/request/ids/read',
						rq_update_init : base_url + '/request/ids/init_update',
						rq_edit : base_url + '/request/static/edit',
						rq_remove : base_url + '/request/static/remove',
						rq_review : base_url + '/request/static/review',
						rq_update : base_url + '/request/static/update',
						rq_append : base_url + '/request/static/append',
						rq_create_zip : base_url + '/request/zip/create',
						rq_restore_zip : base_url + '/request/zip/restore',
						rq_remove_zip : base_url + '/request/zip/remove',
					}
				}
			},
			wp_es_cm : {
				wp_easystatic : wp_easystatic
			},
			wp_es_dt : {
				wp_easystatic : wp_easystatic
			}
	},
	packages: [{
        name: "codemirror",
        main: "scripts/js/codemirror"
    }],
	paths: {
		wp_es_var : "variables",
		wp_es_func : "package/wp-es-func",
		wp_es_dt : "package/wp-es-dt",
		wp_es_alert : "package/wp-es-alert",
		wp_es_scanner : "package/wp-es-scanner",
		wp_es_cm : 'codemirror/main'

	},
	shim: {
	  wp_es_dt: {
			deps: ["jquery", "codemirror", "wp_es_cm", "wp_es_var", "wp_es_func", "wp_es_alert"],
	    	exports: "wp_es_dt",
	  },
	  wp_es_scanner: {
	    deps: ["jquery", "wp_es_var", "wp_es_func", "wp_es_dt"],
	    exports: "wp_es_scanner"
	  }
	},
	waitSeconds : 10
});