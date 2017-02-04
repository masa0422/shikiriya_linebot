<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */


ini_set('display_errors', 'On');

define('__ABSPATH__', dirname(__FILE__) . '/');
include (__ABSPATH__.'vendor/autoload.php');

//use LINE\LINEBot\EchoBot;

require_once(__ABSPATH__.'vendor/linecorp/line-bot-sdk/line-bot-sdk-tiny/LINEBotTiny.php');

$channelAccessToken = 'I1qA639uMnj1oLcjf68TvgIDpw+PqiWmZi0D1cVpW3NdvG9Z+5NYBejZnb61hCxiR+6wCFfhtgJGUSYBXOPfj4kxrD/X/yTqMt7/eHnABNqmS7STh3x3Fd9CNuztS0WKsgSGxSbCS/L2d4OttpFkGgdB04t89/1O/w1cDnyilFU=';
$channelSecret = '1d0fa441e72a430d9715ba1bc51ea1f6';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
  switch ($event['type']) {
    case 'message':
      $message = $event['message'];
      switch ($message['type']) {
        case 'text':
          $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
              array(
                'type' => 'text',
                //                'text' => $message['text']
                'text' => json_encode($event)
              )
            )
          ));
          break;
        default:
          error_log("Unsupporeted message type: " . $message['type']);
          break;
      }
      break;
    default:
      error_log("Unsupporeted event type: " . $event['type']);
      break;
  }
};
