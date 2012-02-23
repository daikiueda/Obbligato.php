<?php
/**
 * OBBLIGATO コントローラ
 *
 * @version 0.1
 */

  require_once( "config.php" );
  require_once( PATH_SIMPLEHTMLDOM );
  require_once( "obbligato.php" );


  /**
   * 初期化
   */
  function init(){
    if( !is_file( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) ){
      header("HTTP/1.1 404 Not Found");
      exit;
    }
    
    //echo $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"];
    //$test_dom = file_get_html( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] );
    //foreach( $test_dom->find("meta") as $element ){
    //  echo $element->outertext;
    //}
    
    // キャッシュファイルが存在し、最新の状態であれば、それを出力
    if( false && is_file( PATH_CACHEDIR . $_SERVER["REDIRECT_URL"] ) ){
      if(
        ( filemtime( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) < filemtime( PATH_CACHEDIR . $_SERVER["REDIRECT_URL"] ) ) &&
        ( filemtime( "template/content.html" ) < filemtime( PATH_CACHEDIR . $_SERVER["REDIRECT_URL"] ) )
      ){
        readfile( PATH_CACHEDIR . $_SERVER["REDIRECT_URL"] );
        exit;
      }
    }
    
    // テンプレート埋め込み用オブジェクト
    $OBBLIGATO = new Obbligato();
    
    // テンプレート埋め込み用定数
    define("PAGE",  $_SERVER["REDIRECT_URL"]);
    define("INDEX", preg_replace("/\/[^\/]+$/","/index.html",$_SERVER["REDIRECT_URL"]) );
    define("PARENT", preg_replace("/[^\/]+\/[^\/]+$/","index.html",$_SERVER["REDIRECT_URL"]) );
    define("ROOT",  "/index.html" );
    
    // ページ構築
    ob_start();
    include "template/content.html";
    $page_data = ob_get_contents();
    ob_end_clean();
    
    // キャッシュファイルを保存
    // 該当ディレクトリが存在しない場合は、ディレクトリを新規作成
    $target_dir = preg_replace( "/\/[^\/]+$/","/", PATH_CACHEDIR . $_SERVER["REDIRECT_URL"] );
    if( !is_dir( $target_dir ) ){
      mkdir( $target_dir, 0777, true );
    }
    $cash_file_handle = fopen( PATH_CACHEDIR . $_SERVER["REDIRECT_URL"], "w+");
    fwrite( $cash_file_handle, $page_data );
    
    // 出力
    echo $page_data;
  }
  init();
?>
