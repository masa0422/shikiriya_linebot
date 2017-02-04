<?php

include (__ABSPATH__.'vendor/autoload.php');
require_once(__ABSPATH__.'vendor/linecorp/line-bot-sdk/line-bot-sdk-tiny/LINEBotTiny.php');

/**
 * クラス外部ファイル自動読み込み関数
 * -------------------------------------------------------------------------- */
spl_autoload_register(function ($className) {
  $dirs = array('', 'common/');
  foreach ($dirs as $dir) {
    $path =  LIBPATH . $dir . $className . '.class.php';
    if (is_readable($path)) {
      include_once $path;
      return;
    }
  }
});


/**
 * DB接続準備
 * -------------------------------------------------------------------------- */
# DB
define('DB_NAME',        'line_bot_awards');
define('DB_USER',        'root');
define('DB_PASSWORD',    'root');
define('DB_HOST',        '127.0.0.1');
define('DB_PORT',        '3306');
define('DB_CHARSET',     'utf8');
define('DB_TYPE',        'mysql');
$db_dsn = DB_TYPE."://".DB_USER.":".DB_PASSWORD."@".DB_HOST.":".DB_PORT."/".DB_NAME."?charset=".DB_CHARSET;

//MySQL
$mdb2 =& MDB2::factory($db_dsn);
$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
$mdb2->loadModule('Extended'); // autoExecute()の有効化

/**
 * オブジェクト生成
 * ------------------------------------------------------------------ */
$elObj              = new ErrorLog;

