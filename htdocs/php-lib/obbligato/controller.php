<?php
	require_once("config.php");

	function init(){
		if( !is_file( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) ){
			header("HTTP/1.1 404 Not Found");
			return;
		}

		if( false && is_file( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) ){
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
		function write( $filepath, $selector, $write_outertext = false ){
			global $dom_files_cash;

			if( !isset($dom_files_cash[$filepath]) ){
				$dom_files_cash[$filepath] = file_get_html($_SERVER["DOCUMENT_ROOT"] . $filepath);
			}

			$base_file_dir_path = preg_replace("/\/[^\/]+$/","/",PAGE);
			$inc_file_dir_path  = preg_replace("/\/[^\/]+$/","/",$filepath);

			if( $base_file_dir_path != $inc_file_dir_path ){

				$arr_base_file_dir_path = explode( "/", $base_file_dir_path );
				$arr_inc_file_dir_path  = explode( "/", $inc_file_dir_path );
				array_shift( $arr_base_file_dir_path );
				array_shift( $arr_inc_file_dir_path );
				$arr_base_file_dir_path_buffer = array();
				$arr_inc_file_dir_path_buffer  = array();

				for( $depth = 0; count($arr_base_file_dir_path) > 0; $depth++ ){
					if( $arr_base_file_dir_path[0] != $arr_inc_file_dir_path[0] ){
						break;
					}
					$arr_base_file_dir_path_buffer = array_shift($arr_base_file_dir_path);
					$arr_inc_file_dir_path_buffer  = array_shift($arr_inc_file_dir_path);
				}
/*
var_dump( $depth );
echo '<br>$arr_base_file_dir_path : ';
var_dump( $arr_base_file_dir_path );
echo '<br>$arr_base_file_dir_path_buffer : ';
var_dump( $arr_base_file_dir_path_buffer );
echo '<br>$arr_inc_file_dir_path : ';
var_dump( $arr_inc_file_dir_path );
echo '<br>$arr_inc_file_dir_path_buffer : ';
var_dump( $arr_inc_file_dir_path_buffer );
echo '<br>';

echo '<br>$arr_base_file_dir_path : ', count( $arr_base_file_dir_path );
echo '<br>$arr_inc_file_dir_path : ', count( $arr_inc_file_dir_path );
echo '<br>$arr_base_file_dir_path_buffer : ', count( $arr_base_file_dir_path_buffer );
echo '<br>$arr_inc_file_dir_path_buffer : ', count( $arr_inc_file_dir_path_buffer );
echo '<br>';
*/


				$up_count = count( $arr_base_file_dir_path ) - count( $arr_inc_file_dir_path );

				$temp_str = "";
				for( $i=0; $i<$up_count; $i++ ){
					$temp_str .= "../";
				}

				foreach( $dom_files_cash[$filepath]->find('a[href]') as $element ){
					$test_href_attr = $element->getAttribute("href");
					if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $test_href_attr ) ){
						$element->setAttribute( "href", $temp_str . $test_href_attr );
					}
				}
			}

			if( $write_outertext ){
				foreach( $dom_files_cash[$filepath]->find($selector) as $element ){
					echo $element->outertext;
				}
			}
			else {
				foreach( $dom_files_cash[$filepath]->find($selector) as $element ){
					echo $element->innertext;
				}
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
