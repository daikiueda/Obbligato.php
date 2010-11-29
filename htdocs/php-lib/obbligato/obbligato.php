<?php

class Obbligato {

	private $file_dom_cashes = null;

	public $base_dir_path = null;

	public function __construct(){
		$this->file_dom_cashes = array();
		$this->base_dir_path = preg_replace( '/\/[^\/]+$/', '', $_SERVER['REDIRECT_URL'] );
	}

	public function file( $my_file_path ){
		if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $my_file_path ) ){
			$my_file_path = $this->base_dir_path . "/" . $my_file_path;
		}

		if( !isset( $this->file_dom_cashes[ $my_file_path ] ) ){
			if( is_file( $_SERVER["DOCUMENT_ROOT"] . $my_file_path )){
				$temp_dom = file_get_html( $_SERVER["DOCUMENT_ROOT"] . $my_file_path );
			} else {
				$temp_dom = null;
			}

			$this->file_dom_cashes[ $my_file_path ] =& new ObbligatoDom( $this, $temp_dom, $my_file_path );
		}
		return $this->file_dom_cashes[ $my_file_path ];
	}
}

class ObbligatoDom {

	private $controller = null;

	private $html_dom = null;
	private $file_path = null;
	private $file_dir_path = null;

	public function __construct( &$my_controller = null, &$my_dom = null, $my_file_path = null ){
		$this->controller =& $my_controller;
		$this->html_dom =& $my_dom;
		
		if( $html_dom != null && $my_file_path != null ){
			$this->file_path = $my_file_path;
			$this->file_dir_path = preg_replace( '/\/[^\/]+$/','', $my_file_path );
			
			if( $this->file_dir_path != $this->controller->base_dir_path ){
				
				$arr_base_file_dir_path = explode( '/', $this->controller->base_dir_path );
				$arr_inc_file_dir_path  = explode( '/', $this->file_dir_path );
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
				
				//var_dump( $depth );
				//echo '<br>$arr_base_file_dir_path : ';
				//var_dump( $arr_base_file_dir_path );
				//echo '<br>$arr_base_file_dir_path_buffer : ';
				//var_dump( $arr_base_file_dir_path_buffer );
				//echo '<br>$arr_inc_file_dir_path : ';
				//var_dump( $arr_inc_file_dir_path );
				//echo '<br>$arr_inc_file_dir_path_buffer : ';
				//var_dump( $arr_inc_file_dir_path_buffer );
				//echo '<br>';
				//
				//echo '<br>$arr_base_file_dir_path : ', count( $arr_base_file_dir_path );
				//echo '<br>$arr_inc_file_dir_path : ', count( $arr_inc_file_dir_path );
				//echo '<br>$arr_base_file_dir_path_buffer : ', count( $arr_base_file_dir_path_buffer );
				//echo '<br>$arr_inc_file_dir_path_buffer : ', count( $arr_inc_file_dir_path_buffer );
				//echo '<br>';
				
				$temp_str = '';
				for( $i=0; $i<count( $arr_base_file_dir_path ); $i++ ){
					$temp_str .= '../';
				}
				if( count( $arr_inc_file_dir_path ) != 0 ){
					$temp_str .= implode( '/', $arr_inc_file_dir_path ) . "/";
				}
				
				foreach( $this->html_dom->find('a[href]') as $element ){
					$test_href_attr = $element->getAttribute("href");
					if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $test_href_attr ) ){
						$element->setAttribute( "href", $temp_str . $test_href_attr );
					}
				}
			}
		}
	}

	function write( $my_selector ){
		if( $this->html_dom == null ){
			return;
		}

		foreach( $this->html_dom->find( $my_selector ) as $element ){
			echo $element->innertext;
		}
	}
}
?>
