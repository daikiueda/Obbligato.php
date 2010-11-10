<?php
	function init(){
		if( !is_file( "." . $_SERVER["REDIRECT_URL"] ) ){
			header("HTTP/1.1 404 Not Found");
			return;
		}

		define("ROOT",  "./index.html" );
		define("INDEX", "." . preg_replace("/\/[^\/]+$/","/index.html",$_SERVER["REDIRECT_URL"]) );
		define("PAGE",  "." . $_SERVER["REDIRECT_URL"]);

		include("php-lib/simplehtmldom/simple_html_dom.php");

		$dom_files_cash = array();
		function write( $filepath, $selector ){
			global $dom_files_cash;

			if( !isset($dom_files_cash[$filepath]) ){
				$dom_files_cash[$filepath] = file_get_html($filepath);
			}

			foreach( $dom_files_cash[$filepath]->find($selector) as $element ){
				echo $element->innertext;
			}
		}

		include("template/content.html");
	}
	init();
?>
