<?php
	require_once("config.php");

	function init(){
		if( !is_file( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) ){
			header("HTTP/1.1 404 Not Found");
			return;
		}

		if( is_file( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) ){
			if(
				( filemtime( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) < filemtime( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) ) &&
				( filemtime( "template/content.html" ) < filemtime( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) )
			){
				readfile( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] );
				return;
			}
		}

		define("PAGE",  $_SERVER["REDIRECT_URL"]);
		define("INDEX", preg_replace("/\/[^\/]+$/","/index.html",$_SERVER["REDIRECT_URL"]) );
		define("PARENT", preg_replace("/[^\/]+\/[^\/]+$/","index.html",$_SERVER["REDIRECT_URL"]) );
		define("ROOT",  "/index.html" );

		include(PATH_SIMPLEHTMLDOM);

		$dom_files_cash = array();
		function write( $filepath, $selector ){
			global $dom_files_cash;

			if( !isset($dom_files_cash[$filepath]) ){
				$dom_files_cash[$filepath] = file_get_html($_SERVER["DOCUMENT_ROOT"] . $filepath);
			}

			foreach( $dom_files_cash[$filepath]->find($selector) as $element ){
				echo $element->innertext;
			}
		}

		ob_start();
		include "template/content.html";
		$page_data = ob_get_contents();
		ob_end_clean();

		$target_dir = preg_replace( "/\/[^\/]+$/","/", PATH_CASHDIR . $_SERVER["REDIRECT_URL"] );
		if( !is_dir( $target_dir ) ){
			mkdir( $target_dir, 0777, true );
		}

		$cash_file_handle = fopen( PATH_CASHDIR . $_SERVER["REDIRECT_URL"], "w+");
		fwrite( $cash_file_handle, $page_data );
	
		echo $page_data;
	}
	init();
?>
