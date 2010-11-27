<?php

class Obbligato {

	private $file_dom_cashes = null;

	public function __construct(){
		$this->file_dom_cashes = array();
	}

	public function file( $filepath ){
		if( !isset( $this->file_dom_cashes[$filepath] ) ){
			$this->file_dom_cashes[$filepath] = file_get_html( $_SERVER["DOCUMENT_ROOT"] . $filepath );
		}
		return $this->file_dom_cashes[$filepath];
	}

	public function write( $filepath, $selector ){
		$data_dom = $this->file( $filepath );

		$base_file_dir_path = preg_replace("/\/[^\/]+$/","",PAGE);
		$inc_file_dir_path  = preg_replace("/\/[^\/]+$/","",$filepath);

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

			$temp_str = "";
			for( $i=0; $i<count( $arr_base_file_dir_path ); $i++ ){
				$temp_str .= "../";
			}

			foreach( $data_dom->find('a[href]') as $element ){
				$test_href_attr = $element->getAttribute("href");
				if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $test_href_attr ) ){
					$element->setAttribute( "href", $temp_str . $test_href_attr );
				}
			}
		}

		if( $write_outertext ){
			foreach( $data_dom->find($selector) as $element ){
				echo $element->outertext;
			}
		}
		else {
			foreach( $data_dom->find($selector) as $element ){
				echo $element->innertext;
			}
		}

	}

}

?>
