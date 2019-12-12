<?php

DEFINE('EASYSTASTIC_CACHE', "/cache/". EASYSTASTIC_SLUG);

/*
	This class is use to optimize HTML content
*/
if (!defined( 'ABSPATH' ) ) {
   exit;
}

class WP_Easystatic_Optimize{

/*
	check if cache diretory is created
*/
function check_cache_directory(){
	$cache =  WP_CONTENT_DIR . EASYSTASTIC_CACHE;

	if(!file_exists($cache)){
		mkdir($cache, 0777, true);
	}

	return $cache;
}

/*
	wrapper to minify css
*/
function minify_css( $styles ){
	$content = "";
	if(is_array($styles)){
		foreach($styles as $url){
			if(!is_array($url) && !isset($url['inline'])){
				$content .= WP_Easystatic_Utils::es_get_sitecontent($url);
			}else{
				$content .= $url['inline'];
			}
		}
	}
	else{
		$content = $styles;
	}

	$minify = new Easystastic\tubalmartin\CssMin\Minifier(true);
	$minify->keepSourceMapComment();
	return $minify->run($content);
}

/*
	wrapper to minify js
*/
function minify_js( $scripts ){
	$content = "";
	if(is_array($scripts)){
		foreach($scripts as $url){
			if(!is_array($url) && !isset($url['inline'])){
				$content .= WP_Easystatic_Utils::es_get_sitecontent($url) . "\n\r";
			}else{
				$content .= $url['inline'] . "\n\r";
			}
		}
	}else{
		$content = $scripts;
	}
	return JSMin::minify($content);
}

/*
	wrapper to minify HTML
*/
function minify_html( $content, $options ){
	
	if(empty($content)){
		return;
	}
	
	return Minify_HTML::minify( $content, $options );
}

/*
	Get content from minifier and create a cache file
*/
function cache_minified( $content, $type, $id ){

	$directory = $this->check_cache_directory();
	$cache_dir = $directory . DIRECTORY_SEPARATOR . $type;
	if(!file_exists($cache_dir)){
		mkdir($cache_dir, 0777, true);
	}

	$filename = 'easystatic_' . md5($id) . '.' . $type;
	$filedir =  $cache_dir . DIRECTORY_SEPARATOR . $filename;
	$file = fopen($filedir, "w");
	fwrite($file, $content);
	fclose($file);

	return $filename;

}

/*
	wrapper to filter the style from HTML ouput
*/
function filtered_css_codes( &$content, $exclude = false ){

	$split = explode('</head>', $content, 2);

	$head = $split[0];
	$body = $split[1];
	$default_exclude = array(
		'fonts.googleapis.com/css'
	);

	$styles = [];
	if (preg_match_all( '#(<style[^>]*>.*</style>)|(<link[^>]*stylesheet[^>]*>)#Usmi', $head, $matches ) ){
		foreach ( $matches[0] as $tag ) {
			$is_exclude = false;
			if(is_array($exclude)){
				foreach($exclude as $e){
					if(empty($e)){
						continue;
					}
					if(strpos($tag, $e)){
						$is_exclude = true;
					}
				}	
			}
			foreach($default_exclude as $a){
				if(strpos($tag, $a)){
					$is_exclude = true;
				}
			}
			if(!$is_exclude){
				$head = str_replace($tag, '', $head);
				$content = $head . '</head>' . $body;
				if ( preg_match( '#<link.*href=("|\')(.*)("|\')#Usmi', $tag, $source ) ) {
					$styles[] = current( explode( '?', $source[2]));
				}
				else if(preg_match( '#<style.*>(.*)</style>#Usmi', $tag, $code )){
					$styles[] = ['inline' => $code[1]];
				}
			}
		}
	}
	return $styles;
}

/*
	wrapper to filter the jsscript from HTML ouput
*/
function filtered_js_codes( &$content, $exclude = false ){

	$default_exclude = array(
		'document.write','html5.js','show_ads.js','google_ad','histats.com/js','statcounter.com/counter/counter.js',
	    'ws.amazon.com/widgets','media.fastclick.net','/ads/','comment-form-quicktags/quicktags.php','edToolbar',
	    'intensedebate.com','scripts.chitika.net/','_gaq.push','jotform.com/','admin-bar.min.js','GoogleAnalyticsObject',
	    'plupload.full.min.js','syntaxhighlighter','adsbygoogle','gist.github.com','_stq','nonce','post_id','data-noptimize'
	    ,'logHuman', 'googletagmanager', 'application/ld+json', 'data-cfasync'
		 );

	$scripts = [];
	if (preg_match_all( '#<script.*</script>#Usmi', $content, $matches)){
		foreach ( $matches[0] as $tag ) {
			$is_exclude = false;
			if(is_array($exclude)){
				foreach($exclude as $e){
					if(empty($e)){
						continue;
					}
					if(strpos($tag, $e)){
						$is_exclude = true;
					}
				}	
			}
			foreach($default_exclude as $a){
				if(strpos($tag, $a)){
					$is_exclude = true;
				}
			}
			if(!$is_exclude){
				$content = str_replace($tag, '', $content);
				if ( preg_match( '#<script[^>]*src=("|\')([^>]*)("|\')#Usmi', $tag, $source ) ) {
					$scripts[] = current( explode( '?', $source[2]));
				}else{
					preg_match( '#<script.*>(.*)</script>#Usmi', $tag , $code );
               		$scripts[] = ['inline' => $code[1]];
				}
			}
		}
	}
	return $scripts;
}

/*
	inject the cache file
*/
function inject_cache_file( $file, $content, $forcehead ){
	
	if($forcehead){
		$pos = strpos($content, '<title');
	}else{
		$pos = strpos($content, '</body');
	}
	
	$sub = $file . substr($content, $pos);
	$replace = substr($content, 0, $pos - strlen($content)) . $sub;

	return $replace;

}

/*
	method formatting html
*/
function es_format_html($content){
	
	$strip_content = stripslashes_deep($content);

	$dom = new DOMDocument();
	@$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED);
	@$dom->preserveWhiteSpace = false; 
	@$dom->formatOutput = true;
	return @$dom->saveHTML();

}

/*
	removing any scripts and styles in page
*/
function es_content_sanitize( $content ){

	$content_split = preg_split("/\<body (.*)([^>])/", $content);
	$remove_js = preg_replace('#<script.*</script>#Usmi', "", $content_split[1]);
	$content = preg_replace("/\<\/body.*[^>].*/", "", $remove_js);
	$content = stripslashes_deep(wp_filter_post_kses($content));

	return $content;
}

/*
	start to optimize
*/
function wp_easystatic_jscss_buffer( $content, $post ){
	$min_css_opt = get_option('static_minify_css');
	$is_critical_css = get_option('static_critical_enable');
	if($min_css_opt){
		$exclude_url = explode(",", get_option('static_exclude_css'));
		$codes = $this->filtered_css_codes( $content, $exclude_url);

		$minify = $this->minify_css($codes);
		$cache = apply_filters('easystatic_minify_cache', $minify, 'css');
		$file = $this->cache_minified($cache, 'css', $post->ID);
		$inject = apply_filters('easystastic_url_inject', "<link id='easystatic_css' rel='preload' as='style' href=\"" . EASYSTATIC_CONTENT . EASYSTASTIC_CACHE .'/css/' . $file . "\" media='all' onload=\"this.onload=null;this.rel='stylesheet'\"/>");
		if($is_critical_css){
			$inline_css = get_option("static_critical_css");
			$inline_minify = $this->minify_css($inline_css);
			$inline_style = "<style>" . $inline_minify . "</style>";
			$inject = apply_filters('easystastic_url_inject', $inline_style . $inject);
		}
		$content = $this->inject_cache_file($inject, $content, true);
	}

	$min_js_opt = get_option('static_minify_js');
	if($min_js_opt){
		$exclude_url = explode(",", get_option('static_exclude_js'));
		$codes = $this->filtered_js_codes( $content, $exclude_url );

		$minify = $this->minify_js($codes);
		$cache = apply_filters('easystatic_minify_cache', $minify, 'js');
		$file = $this->cache_minified($cache, 'js', $post->ID);
		$inject = apply_filters('easystastic_url_inject', "<script defer src=\"" . EASYSTATIC_CONTENT . EASYSTASTIC_CACHE .'/js/' . $file . "\" ></script>");
		$content = $this->inject_cache_file($inject, $content, false);
	}

	$min_html_opt = get_option('static_minify_html');
	if($min_html_opt){
		$content = $this->minify_html($content, 
			['keepComments' => false, 'xhtml' => true]);
	}

	return $content;
}

/*
	Get content from buffer
*/
function do_buffer(){
	while ( ob_get_level() > 0 ) {
        ob_end_clean();
    }

	ob_start(array($this, 'wp_easystatic_jscss_buffer'));
}

}


