<?php

/**
 * OBBLIGATO カスタムタグ
 *
 * @version 0.1
 */

/**
 * トピックパスの生成
 * @param $my_obbligato Obbligatoクラスのインスタンス
 * @param $my_options オプション
 * @param $my_options['separator'] セパレータ文字列
 * @param $my_options['root'] ルート階層の表示用文字列
 */
function topic_path( $my_obbligato, $my_options = null ){
	$separator = ( isset( $my_options ) && isset( $my_options['separator'] ) ) ?
		$my_options['separator'] : ' &gt; ';

	foreach( $my_obbligato->path() as $index => $dir ){

		$title_str = ( $dir->is_root && isset( $my_options ) && isset( $my_options['root'] ) ) ?
			$my_options['root'] : $dir->title;
		$title_str = trim( $title_str ); //TODO trimの必要の有無・場所の確認

		if( !$dir->is_root ){
			echo $separator;
		}
		if( !$dir->is_last ){
			echo '<a href="' . $dir->full_path . '">' . $title_str . '</a>';
		}
		else {
			echo $title_str;
		}
	}
}