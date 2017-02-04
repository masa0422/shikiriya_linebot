#!/usr/bin/php
<?php
/**
 * 内容　　： 集計 & 通知
 * 実行間隔： 1分おきに実行
 * crontab -e
   * ex. * / 10 * * * * /usr/bin/php /var/www/html/cron/***.php >/dev/null2>/dev/null
 */

/**
 * cron設定ファイル読み込み
 */
include('cron-config.php');


// init
$ScheduleDAO = new ScheduleDAO;

/**
 * 集計 & 通知
 */
// 集計
$ScheduleDAO->aggregationAndNotification("");


// 通知


exit;
