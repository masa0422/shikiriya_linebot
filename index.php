<?php

ini_set('display_errors', 'On');


include('web-config.php');
include(LIBPATH . 'common/initialize.php');

use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

/**
 * オブジェクト生成
 * ------------------------------------------------------------------ */
$LineBotDAO  = new LineBotDAO;
$RawDataDAO  = new RawDataDAO;
$ScheduleDAO = new ScheduleDAO;

$question = array(
  0 => array('title' => '今から飲める？', 'limit' => 5),
  1 => array('title' => '今日の夜、飲める？', 'limit' => 5),
  2 => array('title' => '今週末、飲める？', 'limit' => 5),
  3 => array('title' => '今月末、飲める？', 'limit' => 5),
);

//print_r($LineBotDAO->echo_word('a'));

//// パラメータ設定
//$ins = array();
//$ins['timestamp'] = date("Y-m-d H:i:s");
//$ins['userid']    = '';
//$ins['keyword']   = 'abc';
//
//// DB登録
//$LineBotDAO->insert($ins);

//$keyword = 'グループ5に入れてー';
//$members = $LineBotDAO->getGroupList($keyword);
//
//print_r($members);
//exit();


$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNELACCESSTOKEN);
$bot        = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNELSECRET]);

//POST
$input = file_get_contents('php://input');
$json  = json_decode($input);
//$event = $json->events[0];

foreach ($json->events as $event) {

  // rawデータ保存
  //  $RawDataDAO->insert(($event));

  //イベントタイプ判別
  if ("message" == $event->type) {            //一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)

    syslog(LOG_EMERG, print_r($message_text, true));

    if ("text" == $event->message->type) {

      // 初期化
      $message_text = $event->message->text;

      if (preg_match('/に入れてー\z/', $message_text)) {

        // パラメータ設定
        $ins              = array();
        $ins['timestamp'] = $event->timestamp;
        $ins['userid']    = $event->source->userId;
        $ins['keyword']   = mb_substr($event->message->text, 0, -5);

        // Profile取得
        $data                 = $LineBotDAO->getProfile($event->source->userId);
        $profile              = json_decode($data, true);
        $ins['displayName']   = $profile['displayName'];
        $ins['pictureUrl']    = $profile['pictureUrl'];
        $ins['statusMessage'] = $profile['statusMessage'];

        // DB登録
        $LineBotDAO->insert($ins);

        // 結果格納
        $rep_message = $profile['displayName'] . "さんを" . $ins['keyword'] . "に入れたよー。";

        // メッセージ作成
        //        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($profile['displayName']);
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($rep_message);
        $response = $bot->replyMessage($event->replyToken, $textMessageBuilder);

      } elseif (preg_match('/^飲み行かない？/', $message_text)) {

        // レスポンス: 「グループ1のみんなを誘うよ?」

        // 結果格納

//        $groupList = $LineBotDAO->getGroupList();


        // メッセージ作成(お知らせ)
        $textMessageBuilder = new TemplateMessageBuilder(
          'Confirm alt text',
          new ConfirmTemplateBuilder('グループ1のみんなを誘うよ?', [
            new MessageTemplateActionBuilder('Yes', 'invite_true'),
            new MessageTemplateActionBuilder('No', 'invite_false')
          ])
        );
        $response = $bot->replyMessage($event->replyToken, $textMessageBuilder);

      } elseif (preg_match('/invite_true/', $message_text)) {

        $keyword = 'グループ1';

        // 全員に案内メッセージ
        syslog(LOG_EMERG, print_r('zzz', true));
        syslog(LOG_EMERG, print_r($message_text, true));

        $members = $LineBotDAO->getMemberList($keyword);

        // 全員: メッセージ送信
        foreach ((array)$members as $key => $val) {

          syslog(LOG_EMERG, print_r('@', true));
          syslog(LOG_EMERG, print_r($members[$key]['userid'], true));


          $textMessageBuilder = new TemplateMessageBuilder(
            'Confirm alt text',
            new ConfirmTemplateBuilder($question[0]['title'], [
              new MessageTemplateActionBuilder('Yes', '0_true'),
              new MessageTemplateActionBuilder('No', '0_false')
            ])
          );
          $response = $bot->pushMessage($members[$key]['userid'], $textMessageBuilder);

          // データ設定
          $data[] = array(
            'userid' => $members[$key]['userid'],
            'q0' => null,
            'q1' => null,
            'q2' => null,
            'q3' => null,
          );

        }

        // スケジューラ登録
        $ins            = array();
        $ins['keyword'] = $keyword;
        $ins['data']    = json_encode($data);
        $ScheduleDAO->insert($ins);

      } elseif (preg_match('/0_true/', $message_text) || preg_match('/0_false/', $message_text)) {

        // 全員に案内メッセージ
        syslog(LOG_EMERG, print_r('zzz', true));
        syslog(LOG_EMERG, print_r($message_text, true));

//        $members = $LineBotDAO->getMemberList('グループ1');

        $textMessageBuilder = new TemplateMessageBuilder(
          'Confirm alt text',
          new ConfirmTemplateBuilder($question[1]['title'], [
            new MessageTemplateActionBuilder('Yes', '1_true'),
            new MessageTemplateActionBuilder('No', '1_false')
          ])
        );
        $response = $bot->replyMessage($event->replyToken, $textMessageBuilder);

        // 結果登録
        $res = explode('_', $message_text);
        syslog(LOG_EMERG, print_r($res, true));

        $schedule = $ScheduleDAO->findByKeyword("");
        foreach ((array)$schedule['data'] as $key => $val) {
          $userid = $schedule['data'][$key]['userid'];
          if ($userid == $event->source->userId) {
            $schedule['data'][$key]['q'.$res[0]] = $res[1];
          }
        }
        // スケジューラ登録
        $schedule['data'] = json_encode($schedule['data']);
        $ScheduleDAO->update($schedule);

      } elseif (preg_match('/1_true/', $message_text) || preg_match('/1_false/', $message_text)) {

        // 全員に案内メッセージ
        syslog(LOG_EMERG, print_r('zzz', true));
        syslog(LOG_EMERG, print_r($message_text, true));

        $rep_message = "ありがとう。決まったら連絡するね。";

        // メッセージ作成
        //        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($profile['displayName']);
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($rep_message);
        $response = $bot->replyMessage($event->replyToken, $textMessageBuilder);

        // DB更新
        $res = explode('_', $message_text);
        syslog(LOG_EMERG, print_r($res, true));

        $schedule = $ScheduleDAO->findByKeyword("");
        foreach ((array)$schedule['data'] as $key => $val) {
          $userid = $schedule['data'][$key]['userid'];
          if ($userid == $event->source->userId) {
            $schedule['data'][$key]['q'.$res[0]] = $res[1];
          }
        }
        // スケジューラ登録
        $schedule['data'] = json_encode($schedule['data']);
        $ScheduleDAO->update($schedule);

      }

    }
  }
//  syslog(LOG_EMERG, print_r('aaa', true));
//  syslog(LOG_EMERG, print_r($textMessageBuilder, true));


//  syslog(LOG_EMERG, print_r('bb', true));
  //  syslog(LOG_EMERG, print_r($event->replyToken, true));
//  syslog(LOG_EMERG, print_r($response, true));
  return;


}

