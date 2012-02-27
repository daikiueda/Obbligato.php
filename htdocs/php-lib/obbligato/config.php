<?php

/**
 * OBBLIGATO 設定ファイル
 *
 * @version 0.1
 */
/** コンテンツのルート階層のパス ※ルートの場合は空文字 */
define( "PATH_CONTENTS_ROOT_DIR", "" );

/** Simple HTML DOM Parser ライブラリの場所 */
define( "PATH_SIMPLE_HTML_DOM", "../simplehtmldom/simple_html_dom.php" );

/** テンプレートファイルの格納ディレクトリ */
define( "PATH_TEMPLATES_DIR", "./template" );

/**
 * デフォルトテンプレートのファイル名
 * 設定しない場合は、空文字を記述
 * （metaの指定が無い場合、テンプレートを適用しない）
 */
define( "STR_DEFAULT_TEMPLATE", "default.html" );

/** キャッシュディレクトリ */
define( "PATH_CACHE_DIR", "./cache" );
?>
