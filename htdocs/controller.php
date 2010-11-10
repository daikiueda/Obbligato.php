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
		function write( $text, $query ){
			$data = file_get_html($text);
			foreach( $data->find($query) as $element ){
				echo $element->innertext;
			}
		}

		//include("." . $_SERVER["REDIRECT_URL"]);
		include("template/content.html");
	}
	init();
?>
