define(['require', 'exports'], function(require, exports){
	
	var j = require('jquery');

	return function() {
		return {
			 start_scan : j("#start_scan"),
			 update_scan : j("#update_scan"),
			 percent_bar : j("#percent-bar"),
			 percent : j(".percent"),
			 page_log : j("#logs"),
			 console_wrapper : j(".static-console-wrapper"),
			 gen_static : j("#gen_static"),
			 static_modal : j("#static-file-modal"),
			 load_static : j("#load-static"),
			 refresh : j("#refresh"),
			 static_activate : j(".static_activate"),
			 static_merge_update : j('#static-merge-update'),
			 static_paste_update : j('#static-paste-update'),
			 create_backup : j("#create_backup"),
			 es_loader : j("#es-loader")
		}
	}

});
