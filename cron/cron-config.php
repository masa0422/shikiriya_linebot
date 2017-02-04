<?php
/**
 * Cron専用設定ファイル
 */

/**
 * 外部読み込みファイル群
 */
$path = realpath(dirname(__FILE__));
require_once( $path. '/../www/web-config.php');
require_once( $path. '/../www/vendor/autoload.php');

#require_once($path . '/web-config.php');
#require_once($path . '/vendor/autoload.php');

/**
 * クラス外部ファイル自動読み込み関数
 */
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
 * MySQL接続設定
 */
# DB
define('DB_NAME',        'line_bot_awards');
define('DB_USER',        'root');
define('DB_PASSWORD',    'root');
define('DB_HOST',        '127.0.0.1');
define('DB_PORT',        '3306');
define('DB_CHARSET',     'utf8');
define('DB_TYPE',        'mysql');
$db_dsn = DB_TYPE."://".DB_USER.":".DB_PASSWORD."@".DB_HOST.":".DB_PORT."/".DB_NAME."?charset=".DB_CHARSET;
$mdb2 =& MDB2::factory($db_dsn);
$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
$mdb2->loadModule('Extended'); // autoExecute()の有効化