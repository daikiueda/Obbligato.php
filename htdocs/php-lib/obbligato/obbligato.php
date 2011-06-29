<?php
/**
 * OBBLIGATO �e���v���[�g���ߍ��ݗp�I�u�W�F�N�g
 *
 * @version 0.1
 */

/**
 * OBBLIGATO �e���v���[�g���ߍ��ݗp�N���X
 */
class Obbligato {

  private $file_dom_cashes = null;

  public $base_dir_path = null;

  /**
   * �R���X�g���N�^
   */
  public function __construct(){
    $this->file_dom_cashes = array();
    $this->base_dir_path = $_SERVER['DOCUMENT_ROOT'] . preg_replace( '/\/[^\/]+$/', '', $_SERVER['REDIRECT_URL'] );
  }

  /**
   * �t�@�C���̓ǂݍ���
   * @param $my_file_path �Ώۃt�@�C���̃p�X
   * @return ObbligatoDom
   */
  public function file( $my_file_path ){

    // �w��t�@�C���̃p�X�𒲐�
    if( !preg_match( '/^(http:\/\/|https:\/\/|\/)/', $my_file_path ) ){
      $my_file_path = $this->base_dir_path . "/" . $my_file_path;
    } else {
      $my_file_path = $_SERVER["DOCUMENT_ROOT"] . $my_file_path;
    }
    $my_file_path = str_replace( '\\', '/', realpath( $my_file_path ) );
    
    // DOM�̃L���b�V���������ꍇ
    if( !isset( $this->file_dom_cashes[ $my_file_path ] ) ){
      if( $my_file_path ){
        // Simple HTML DOM Parser �ŁA�Ώۃt�@�C������DOM���擾
        $temp_dom = file_get_html( $my_file_path );
      } else {
        $temp_dom = null;
      }
      
      // �L���b�V���Ƃ��āAObbligatoDom�̃C���X�^���X���i�[
      $this->file_dom_cashes[ $my_file_path ] =& new ObbligatoDom( $this, $temp_dom, $my_file_path );
    }
    
    // ObbligatoDom�^�̃f�[�^��Ԃ�
    return $this->file_dom_cashes[ $my_file_path ];
  }
}

/**
 * OBBLIGATO �e���v���[�g�W�J�p��DOM
 */
class ObbligatoDom {

  private $controller = null;

  private $html_dom = null;
  private $file_path = null;
  private $file_dir_path = null;

  /**
   * �R���X�g���N�^
   * @param $my_controller OBBLIGATO�I�u�W�F�N�g�̎Q��
   * @param $my_dom Simple HTML DOM Parser�œ�����DOM
   * @param $my_file_path �t�@�C���p�X
   */
  public function __construct( &$my_controller = null, &$my_dom = null, $my_file_path = null ){
    $this->controller =& $my_controller;
    $this->html_dom =& $my_dom;
    $this->file_path = $my_file_path;
      
    if( $this->html_dom != null && $this->file_path != null ){

      // �Ώۃt�@�C���̃f�B���N�g���i�p�X���j���擾
      $this->file_dir_path = preg_replace( '/\/[^\/]+$/','', $my_file_path );

      // �W�J���t�@�C���ƑΏۃt�@�C���ŁA�f�B���N�g���i�p�X���j���قȂ�ꍇ�́A
      // DOM���̃p�X�L�q���C��
      if( $this->file_dir_path != $this->controller->base_dir_path ){
        
        // ���[�g����̃f�B���N�g���K�w�ɂ��ƂÂ��āA�z��𐶐�
        $arr_base_file_dir_path = explode( '/', $this->controller->base_dir_path );
        $arr_inc_file_dir_path  = explode( '/', $this->file_dir_path );
        
        // �z��̐擪�v�f��j��
        array_shift( $arr_base_file_dir_path );
        array_shift( $arr_inc_file_dir_path );
        
        // ���v����K�w��ۊǂ��邽�߂̔z��
        $arr_base_file_dir_path_buffer = array();
        $arr_inc_file_dir_path_buffer  = array();
        
        // ���[�g�����K�w����r
        for( $depth = 0; count($arr_base_file_dir_path) > 0; $depth++ ){
          // �f�B���N�g�����ɍ��ق�����΁A���[�v���I��
          if( $arr_base_file_dir_path[0] != $arr_inc_file_dir_path[0] ){
            break;
          }
          
          // �f�B���N�g�����ɍ��ق������ꍇ�́A���ꂼ��̃f�B���N�g������
          // �z��ɕۊ�
          $arr_base_file_dir_path_buffer = array_shift($arr_base_file_dir_path);
          $arr_inc_file_dir_path_buffer  = array_shift($arr_inc_file_dir_path);
        }
        
        // �p�X�����p�̕�����
        $temp_str = '';
        
        // �W�J���t�@�C���̃p�X���z�񒆂Ɏc���Ă���ꍇ�́A
        // ���̐����A�K�w���グ��i../�j
        for( $i=0; $i<count( $arr_base_file_dir_path ); $i++ ){
          $temp_str .= '../';
        }
        // �Ώۃt�@�C���̃p�X���z�񒆂Ɏc���Ă���ꍇ�́A
        // ���̕��A�K�w��������B
        if( count( $arr_inc_file_dir_path ) != 0 ){
          $temp_str .= implode( '/', $arr_inc_file_dir_path ) . "/";
        }
        
        // <a href="...">�̑��΃p�X�L�q�ɂ��āA�K�w�𒲐�
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
   * �o��
   * @param $my_selector CSS�Z���N�^�[�`���ŁA�Ώ�DOM���w��
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
