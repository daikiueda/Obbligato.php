<?php
/**
 * OBBLIGATO �R���g���[��
 *
 * @version 0.1
 */

  require_once( "config.php" );
  require_once( PATH_SIMPLEHTMLDOM );
  require_once( "obbligato.php" );


  /**
   * ������
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
    
    // �L���b�V���t�@�C�������݂��A�ŐV�̏�Ԃł���΁A������o��
    if( false && is_file( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) ){
      if(
        ( filemtime( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] ) < filemtime( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) ) &&
        ( filemtime( "template/content.html" ) < filemtime( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] ) )
      ){
        readfile( PATH_CASHDIR . $_SERVER["REDIRECT_URL"] );
        exit;
      }
    }
    
    // �e���v���[�g���ߍ��ݗp�I�u�W�F�N�g
    $OBBLIGATO = new Obbligato();
    
    // �e���v���[�g���ߍ��ݗp�萔
    define("PAGE",  $_SERVER["REDIRECT_URL"]);
    define("INDEX", preg_replace("/\/[^\/]+$/","/index.html",$_SERVER["REDIRECT_URL"]) );
    define("PARENT", preg_replace("/[^\/]+\/[^\/]+$/","index.html",$_SERVER["REDIRECT_URL"]) );
    define("ROOT",  "/index.html" );
    
    // �y�[�W�\�z
    ob_start();
    include "template/content.html";
    $page_data = ob_get_contents();
    ob_end_clean();
    
    // �L���b�V���t�@�C����ۑ�
    // �Y���f�B���N�g�������݂��Ȃ��ꍇ�́A�f�B���N�g����V�K�쐬
    $target_dir = preg_replace( "/\/[^\/]+$/","/", PATH_CASHDIR . $_SERVER["REDIRECT_URL"] );
    if( !is_dir( $target_dir ) ){
      mkdir( $target_dir, 0777, true );
    }
    $cash_file_handle = fopen( PATH_CASHDIR . $_SERVER["REDIRECT_URL"], "w+");
    fwrite( $cash_file_handle, $page_data );
    
    // �o��
    echo $page_data;
  }
  init();
?>
