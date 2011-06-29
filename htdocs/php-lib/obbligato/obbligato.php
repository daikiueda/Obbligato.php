<?php
/**
 * OBBLIGATO テンプレート埋め込み用オブジェクト
 *
 * @version 0.1
 */

/**
 * OBBLIGATO テンプレート埋め込み用クラス
 */
class Obbligato {

  private $file_dom_cashes = null;

  public $base_dir_path = null;

  /**
   * コンストラクタ
   */
  public function __construct(){
    $this->file_dom_cashes = array();
    $this->base_dir_path = $_SERVER['DOCUMENT_ROOT'] . preg_replace( '/\/[^\/]+$/', '', $_SERVER['REDIRECT_URL'] );
  }

  /**
   * ファイルの読み込み
   * @param $my_file_path 対象ファイルのパス
   * @return ObbligatoDom
   */
  public function file( $my_file_path ){

    // 指定ファイルのパスを調整
    if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $my_file_path ) ){
      $my_file_path = $this->base_dir_path . "/" . $my_file_path;
    } else {
      $my_file_path = $_SERVER["DOCUMENT_ROOT"] . $my_file_path;
    }
    $my_file_path = str_replace( '\\', '/', realpath( $my_file_path ) );
    
    // DOMのキャッシュが無い場合
    if( !isset( $this->file_dom_cashes[ $my_file_path ] ) ){
      if( $my_file_path ){
        // Simple HTML DOM Parser で、対象ファイルからDOMを取得
        $temp_dom = file_get_html( $my_file_path );
      } else {
        $temp_dom = null;
      }
      
      // キャッシュとして、ObbligatoDomのインスタンスを格納
      $this->file_dom_cashes[ $my_file_path ] =& new ObbligatoDom( $this, $temp_dom, $my_file_path );
    }
    
    // ObbligatoDom型のデータを返す
    return $this->file_dom_cashes[ $my_file_path ];
  }
}

/**
 * OBBLIGATO テンプレート展開用のDOM
 */
class ObbligatoDom {

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
    $this->controller =& $my_controller;
    $this->html_dom =& $my_dom;
    $this->file_path = $my_file_path;
      
    if( $this->html_dom != null && $this->file_path != null ){

      // 対象ファイルのディレクトリ（パス名）を取得
      $this->file_dir_path = preg_replace( '/\/[^\/]+$/','', $my_file_path );

      // 展開元ファイルと対象ファイルで、ディレクトリ（パス名）が異なる場合は、
      // DOM中のパス記述を修正
      if( $this->file_dir_path != $this->controller->base_dir_path ){
        
        // ルートからのディレクトリ階層にもとづいて、配列を生成
        $arr_base_file_dir_path = explode( '/', $this->controller->base_dir_path );
        $arr_inc_file_dir_path  = explode( '/', $this->file_dir_path );
        
        // 配列の先頭要素を破棄
        array_shift( $arr_base_file_dir_path );
        array_shift( $arr_inc_file_dir_path );
        
        // 合致する階層を保管するための配列
        $arr_base_file_dir_path_buffer = array();
        $arr_inc_file_dir_path_buffer  = array();
        
        // ルートから一階層ずつ比較
        for( $depth = 0; count($arr_base_file_dir_path) > 0; $depth++ ){
          // ディレクトリ名に差異があれば、ループを終了
          if( $arr_base_file_dir_path[0] != $arr_inc_file_dir_path[0] ){
            break;
          }
          
          // ディレクトリ名に差異が無い場合は、それぞれのディレクトリ名を
          // 配列に保管
          $arr_base_file_dir_path_buffer = array_shift($arr_base_file_dir_path);
          $arr_inc_file_dir_path_buffer  = array_shift($arr_inc_file_dir_path);
        }
        
        // パス調整用の文字列
        $temp_str = '';
        
        // 展開元ファイルのパスが配列中に残っている場合は、
        // その数分、階層を上げる（../）
        for( $i=0; $i<count( $arr_base_file_dir_path ); $i++ ){
          $temp_str .= '../';
        }
        // 対象ファイルのパスが配列中に残っている場合は、
        // その分、階層をくだる。
        if( count( $arr_inc_file_dir_path ) != 0 ){
          $temp_str .= implode( '/', $arr_inc_file_dir_path ) . "/";
        }
        
        // <a href="...">の相対パス記述について、階層を調整
        foreach( $this->html_dom->find('a[href]') as $element ){
          $test_href_attr = $element->getAttribute("href");
          if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $test_href_attr ) ){
            $element->setAttribute( "href", $temp_str . $test_href_attr );
          }
        }
      }
    }
  }
  
  /**
   * 出力
   * @param $my_selector CSSセレクター形式で、対象DOMを指定
   */
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
