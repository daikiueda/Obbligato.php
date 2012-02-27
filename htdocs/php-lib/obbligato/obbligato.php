<?php

/**
 * OBBLIGATO テンプレート埋め込み用オブジェクト
 *
 * @version 0.1
 */

/**
 * テンプレート埋め込み用クラス
 */
class Obbligato {

	/** HTMLファイル全体のDOMのキャッシュを格納する配列 */
	private $file_dom_caches = null;

	/**  */
	private $topic_path_caches = null;

	/**  */
	public $base_file_uri = null;

	/**  */
	public $base_dir_path = null;

	/**
	 * コンストラクタ
	 * @param $my_base_file_uri 対象HTMLファイルのコンテンツルートからのパス
	 */
	public function __construct( $my_base_file_uri ){
		$this->base_file_uri = $my_base_file_uri;
		$this->base_dir_path = $_SERVER['DOCUMENT_ROOT'] . preg_replace( '/\/[^\/]+$/', '', $_SERVER['REDIRECT_URL'] );
		$this->file_dom_caches = array( );
	}

	/**
	 * ファイルの読み込み
	 * @param $my_file_path 対象ファイルのパス
	 * @return ObbligatoFileDom
	 */
	public function &file( $my_file_path ){

		// 指定ファイルのパスを調整
		if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $my_file_path ) ){
			// TODO:未実装。httpでファイルを取得する機能にしたい。
		}
		else {
			$my_file_path = $_SERVER["DOCUMENT_ROOT"] . PATH_CONTENTS_ROOT_DIR . $my_file_path;
			$my_file_path = str_replace( '\\', '/', realpath( $my_file_path ) );
		}

		// DOMのキャッシュが無い場合
		if( !isset( $this->file_dom_caches[$my_file_path] ) ){
			if( $my_file_path ){
				// Simple HTML DOM Parser で、対象ファイルからDOMを取得
				$temp_dom = file_get_html( $my_file_path );
			}
			else {
				$temp_dom = null;
			}

			// キャッシュとして、ObbligatoFileDomのインスタンスを格納
			$this->file_dom_caches[$my_file_path] = new ObbligatoFileDom( $this, $temp_dom, $my_file_path );
		}

		// ObbligatoFileDom型のデータを返す
		return $this->file_dom_caches[$my_file_path];
	}

	/**
	 * ルートからの階層の取得
	 * @return array ルートからの階層を ObbligatoDir の配列で表現したもの
	 */
	public function &path(){
		if( $this->topic_path_caches == null ){
			$this->topic_path_caches = array( );

			// ルート起点のパス階層を配列に格納
			$arr_target_path = explode( '/', $this->base_file_uri );
			$str_filename = array_pop( $arr_target_path );

			$str_pathname = '';
			for( $i = 0, $last = count( $arr_target_path ); $i < $last; $i++ ){
				$str_pathname .= $arr_target_path[$i] . '/';
				$title_str = $this->file( $str_pathname . 'index.html' )->find( 'title' )->text( 0 );

				// キャッシュに格納
				array_push(
					$this->topic_path_caches, new ObbligatoDir(
						$title_str,
						PATH_CONTENTS_ROOT_DIR . $str_pathname . 'index.html'
					)
				);
			}
			if( $str_filename != 'index.html' ){
				array_push(
					$this->topic_path_caches, new ObbligatoDir(
						$this->file( $str_pathname . $str_filename )->find( 'title' )->text( 0 ),
						$str_pathname . $str_filename
					)
				);
			}
			reset( $this->topic_path_caches )->is_root = true;
			end( $this->topic_path_caches )->is_last = true;
		}
		return $this->topic_path_caches;
	}

}

/**
 * ディレクトリ情報を格納するクラス
 */
class ObbligatoDir {

	/**
	 * ディレクトリのタイトル
	 */
	public $title = null;

	/**
	 * ディレクトリ名：未実装
	 */
	public $dir_name = null;

	/**
	 * ドキュメントルートからのフルパス名
	 */
	public $full_path = null;

	/**
	 * 展開元ページからの相対パス：未実装
	 */
	public $rel_path = null;

	/**
	 * 最初の要素であることを示すフラグ
	 */
	public $is_root = false;

	/**
	 * 最後の要素であることを示すフラグ
	 */
	public $is_last = false;

	/**
	 * コンストラクタ
	 * @param $my_title 階層の名称を示す文字列
	 * @param $my_full_path ドキュメントルート（≠コンテンツルート）からのフルパス
	 */
	public function __construct( $my_title, $my_full_path ){
		$this->title = $my_title;
		$this->dir_name = null;

		// TODO:パス調整、とりあえず
		$this->full_path = $my_full_path;
		$this->rel_path = null;
	}

}

/**
 * テンプレート展開用のDOM（ファイル全体）を格納・操作するためのクラス
 */
class ObbligatoFileDom {

	private $controller = null;
	private $html_dom = null;
	private $file_path = null;
	private $file_dir_path = null;

	/**
	 * コンストラクタ
	 * @param $my_controller OBBLIGATOオブジェクトの参照
	 * @param $my_dom Simple HTML DOM Parserで得られるDOM
	 * @param $my_file_path ファイルパス
	 */
	public function __construct( &$my_controller = null, &$my_dom = null, $my_file_path = null ){
		$this->controller = & $my_controller;
		$this->html_dom = & $my_dom;
		$this->file_path = $my_file_path;

		if( $this->html_dom != null && $this->file_path != null ){

			// 対象ファイルのディレクトリ（パス名）を取得
			$this->file_dir_path = preg_replace( '/\/[^\/]+$/', '', $my_file_path );

			// 展開元ファイルと対象ファイルで、ディレクトリ（パス名）が異なる場合は、
			// DOM中のパス記述を修正
			if( $this->file_dir_path != $this->controller->base_dir_path ){

				// ルートからのディレクトリ階層にもとづいて、配列を生成
				$arr_base_file_dir_path = explode( '/', $this->controller->base_dir_path );
				$arr_inc_file_dir_path = explode( '/', $this->file_dir_path );

				// 配列の先頭要素を破棄
				array_shift( $arr_base_file_dir_path );
				array_shift( $arr_inc_file_dir_path );

				// 合致する階層を保管するための配列
				$arr_base_file_dir_path_buffer = array( );
				$arr_inc_file_dir_path_buffer = array( );

				// ルートから一階層ずつ比較
				for( $depth = 0; count( $arr_base_file_dir_path ) > 0; $depth++ ){
					// ディレクトリ名に差異があれば、ループを終了
					if(
						count( $arr_inc_file_dir_path ) == 0 ||
						$arr_base_file_dir_path[0] != $arr_inc_file_dir_path[0]
					){
						break;
					}

					// ディレクトリ名に差異が無い場合は、それぞれのディレクトリ名を
					// 配列に保管
					$arr_base_file_dir_path_buffer = array_shift( $arr_base_file_dir_path );
					$arr_inc_file_dir_path_buffer = array_shift( $arr_inc_file_dir_path );
				}

				// パス調整用の文字列
				$temp_str = '';

				// 展開元ファイルのパスが配列中に残っている場合は、
				// その数分、階層を上げる（../）
				for( $i = 0; $i < count( $arr_base_file_dir_path ); $i++ ){
					$temp_str .= '../';
				}
				// 対象ファイルのパスが配列中に残っている場合は、
				// その分、階層をくだる。
				if( count( $arr_inc_file_dir_path ) != 0 ){
					$temp_str .= implode( '/', $arr_inc_file_dir_path ) . "/";
				}

				// <a href="...">の相対パス記述について、階層を調整
				foreach( $this->html_dom->find( 'a[href]' ) as $element ){
					$test_href_attr = $element->getAttribute( "href" );
					if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $test_href_attr ) ){
						$element->setAttribute( "href", $temp_str . $test_href_attr );
					}
				}
			}
		}
	}

	/**
	 * 要素の選択
	 * @param $my_selector CSSセレクター形式で、対象DOMを指定
	 * @return ObbligatoDom
	 */
	function find( $my_selector ){
		if( $this->html_dom == null ){
			return;
		}

		return new ObbligatoDom( $this->controller, $this->html_dom->find( $my_selector ) );
	}

}

/**
 * テンプレート展開用のDOM（部分要素）を展開・操作するためのクラス
 */
class ObbligatoDom {

	private $controller = null;
	private $html_dom = null;

	/**
	 * コンストラクタ
	 * @param $my_controller OBBLIGATOオブジェクトの参照
	 * @param $my_dom Simple HTML DOM Parserで得られるDOM
	 */
	public function __construct( $my_controller = null, $my_dom = null ){
		$this->controller = & $my_controller;
		$this->html_dom = & $my_dom;
	}

	/**
	 * 文字列化
	 * $html_domのすべてのOuterHTMLを返す
	 */
	public function __toString(){
		$str_text = '';
		foreach( $this->html_dom as $element ){
			$str_text .= $element->outertext;
		}
		return $str_text;
	}

	/**
	 * 抽出
	 * @param $my_index 抽出の対象とする配列要素のインデックス
	 */
	public function get( $my_index ){
		return new ObbligatoDom( $this->controller, $this->html_dom[$my_index] );
	}

	/**
	 * innerHTMLの取得
	 * @return ObbligatoValuesのインスタンス
	 */
	public function html(){
		$values = array( );
		foreach( $this->html_dom as $element ){
			array_push( $values, $element->innertext );
		}
		return new ObbligatoValues( $values );
	}

	/**
	 * innerTextの取得
	 * @return ObbligatoValuesのインスタンス
	 */
	public function text(){
		$values = array( );
		foreach( $this->html_dom as $element ){
			array_push( $values, $element->innertext );
		}
		return new ObbligatoValues( $values );
	}

	/**
	 * 属性値の取得
	 * @param 対象の属性の名称
	 * @return ObbligatoValuesのインスタンス
	 */
	public function attr( $my_attr_name ){
		$values = array( );
		foreach( $this->html_dom as $element ){
			array_push( $values, $element->$my_attr_name );
		}
		return new ObbligatoValues( $values );
	}

	/**
	 * 出力
	 * 該当する全てのDOMのOuterHTMLを出力する
	 */
	public function write(){
		echo $this->__toString();
	}

}

/**
 * テンプレート展開用のDOMの一部や値を出力するためのクラス
 */
class ObbligatoValues {

	private $values;

	/**
	 * コンストラクタ
	 * @param $my_values 値の配列
	 */
	public function __construct( $my_values ){
		$this->values = & $my_values;
	}

	/**
	 * 文字列化
	 * @return $valuesの値をすべて結合した文字列を返す
	 */
	public function __toString(){
		$str_text = '';
		foreach( $this->values as $value ){
			$str_text .= $value . ' ';
		}
		return rtrim( $str_text );
	}

	/**
	 * 出力
	 * @param $my_index 出力の対象とする配列要素のインデックス
	 * 	インデックスの指定が無い場合は全てを出力する
	 */
	public function write( $my_index = null ){
		if( $my_index == null ){
			echo $this->__toString();
			return;
		}
		else if( isset( $this->values[$my_index] ) ){
			echo $this->values[$my_index];
			return;
		}
	}

}
