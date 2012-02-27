<?php

/**
 * OBBLIGATO コントローラ
 *
 * @version 0.1
 */
require_once( "config.php" );
require_once( PATH_SIMPLE_HTML_DOM );
require_once( "obbligato.php" );
require_once( "tags.php" );

/**
 * 初期化
 */
function init(){
	if( !is_file( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) ){
		header( "HTTP/1.1 404 Not Found" );
		exit;
	}

	//echo $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"];
	//$test_dom = file_get_html( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] );
	//foreach( $test_dom->find("meta") as $element ){
	//  echo $element->outertext;
	//}
	// キャッシュファイルが存在し、最新の状態であれば、それを出力
	if( false && is_file( PATH_CACHE_DIR . $_SERVER["REDIRECT_URL"] ) ){
		if(
			( filemtime( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) < filemtime( PATH_CACHE_DIR . $_SERVER["REDIRECT_URL"] ) ) &&
			( filemtime( "template/content.html" ) < filemtime( PATH_CACHE_DIR . $_SERVER["REDIRECT_URL"] ) )
		){
			readfile( PATH_CACHE_DIR . $_SERVER["REDIRECT_URL"] );
			exit;
		}
	}

	// テンプレート埋め込み用の各種定数を設定
	$page_uri = preg_replace( "/^" . str_replace( '/', '\\/', PATH_CONTENTS_ROOT_DIR ) . "/", "", $_SERVER["REDIRECT_URL"] );

	/** 対象ページのURL */
	define( "PAGE", $page_uri );

	/** 対象ページが属する階層のインデックスページのURL */
	define( "INDEX", preg_replace( "/\/[^\/]+$/", "/index.html", $page_uri ) );

	/** 対象ページの上位階層のインデックスページのURL */
	define( "PARENT", preg_replace( "/[^\/]+\/[^\/]+$/", "index.html", $page_uri ) );

	/** コンテンツのルート階層のインデックスページのURL */
	define( "ROOT", "/index.html" );


	/** テンプレート埋め込み用オブジェクト */
	$OBBLIGATO = new Obbligato( $page_uri );


	// ページ構築
	ob_start();
	include "template/content.html";
	$page_data = ob_get_contents();
	ob_end_clean();

	// キャッシュファイルを保存
	// 該当ディレクトリが存在しない場合は、ディレクトリを新規作成
	$target_dir = preg_replace( "/\/[^\/]+$/", "/", PATH_CACHE_DIR . $_SERVER["REDIRECT_URL"] );
	if( !is_dir( $target_dir ) ){
		mkdir( $target_dir, 0777, true );
	}
	$cash_file_handle = fopen( PATH_CACHE_DIR . $_SERVER["REDIRECT_URL"], "w+" );
	fwrite( $cash_file_handle, $page_data );

	// 出力
	echo $page_data;
}

init();
