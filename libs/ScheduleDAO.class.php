<?php
/**
 * Project     : FRFW
 * description : LineBot DAO / PHP v5.6 / PEAR::MDB2
 * @link       : https://www.fourier.jp/
 * @copyright  : FOURIER Inc.
 * @author     : FOURIER Inc.
 * @package    : -
 * @version    : 1.0.0
 */
class ScheduleDAO {

  // テーブル定義
  private $tablename = "lba_schedule";

  // 選択肢定義
  public $sample       = array();
  public $options      = array();

  // DBテーブル非存在フィールド設定
  public $unset_key = array("submit", "file", "csrf_token");

  public $question = array(
    0 => array('title' => '今から', 'limit' => 5),
    1 => array('title' => '今日の夜', 'limit' => 5),
    2 => array('title' => '今週末', 'limit' => 5),
    3 => array('title' => '今月末', 'limit' => 5),
  );

  /**
   * 初期化
   */
  function __construct(){

    global $mdb2, $elObj;

    $this->mdb2         = $mdb2;
    $this->elObj        = (!isset($elObj)) ? new ErrorLog   : $elObj;

    $this->setSampleData();
    $this->setOptions();
  }

  /**
   * デフォルト値設定
   */
  public function getDefaultValues(){

    $params = array(
      "post_date" => date("Y-m-d"),
      "pickup"    => 0,
      "status"    => 0
    );

    return $params;
  }
  /**
   * 検証用ダミーデータ
   */
  public function setSampleData(){
    $this->sample = array(
      "post_date" => date("Y-m-d"),
      "title"     => "plusstyle アイデア募集のお知らせ",
      "content"   => "吾輩わがはいは猫である。名前はまだ無い。\nどこで生れたかとんと見当けんとうがつかぬ",
      "status"    => 1
    );
  }

  /**
   * selectbox / radio / checkbox 値設定
   */
  public function setOptions(){

    // selectbox

    // radio
    $this->options["category"] = array(1 => "お知らせ", 2 => "更新情報");
    $this->options["status"]   = array(0 => "非公開", 1 => "公開");

    // checkbox

  }

  /**
   * 入力値チェック
   */
  public function check($pa){

    $msg = "";
    $msg.= $this->icObj->date($pa['post_date'], "投稿日", "Posting date",1);
    $msg.= $this->icObj->must($pa['title'],     "タイトル", "Title",1);
    $msg.= $this->icObj->must($pa['content'],   "本文", "Text",1);
    $msg.= $this->icObj->num($pa['status'],     "状態", "Status",1);

    // CSRF対策
//    $result['csrf_error'] = $this->FRFW->checkCSRF($pa['csrf_token']);
    // 結果格納
    $result['error']      = $msg;

    return $result;
  }

  /**
   * 項目設定（不要フィールド削除）
   */
  public function prepare($pa){

    //DB不要フィールド削除
//    $pa = $this->FRFW->unsetArray($pa, $this->unset_key);

    return $pa;
  }

  /**
   * 個別取得
   */
  public function findByKeyword($keyword=""){

    // 初期化
    $sql_where_add = "";
    $result        = "";

    // SQL発行
    if($keyword == ""){
      $sql    = "SELECT * FROM {$this->tablename} where flg = 1 order by created_at DESC limit 1";
    }
//    $sql    = "SELECT * FROM {$this->tablename} where id = {$id} AND flg = 1 {$sql_where_add}";
    $result = $this->mdb2->queryRow($sql);

    // エラー処理
    if(PEAR::isError($result)){
      $this->elObj->saveDB($result,__FILE__,__LINE__);
    }else{

      $result['data'] = json_decode($result['data'], true);
    }

    // 結果
    return $result;

  }

  /**
   * 集計
   */
  public function aggregationAndNotification($status){

    // 1分前の時刻を計算
    $before_date = date("Y-m-d H:i:s", strtotime("-1 minute"));

    // max(id)
    $sql = "SELECT max(id) as max FROM {$this->tablename} WHERE flg = 1";
    $max_id = $this->mdb2->queryRow($sql)['max'];

//    $last_id = $this->mdb2->lastInsertID();
    $sql_where_add = " AND status = 0 AND created_at < '$before_date' AND id = {$max_id}";

    $sql = "SELECT * FROM {$this->tablename} WHERE flg = 1 {$sql_where_add} ORDER BY id DESC limit 1";
    echo 'input $sql:';print_r($sql);echo "<br />";

//    syslog(LOG_EMERG, print_r($sql, true));

    $result = $this->mdb2->queryAll($sql);
    echo 'input $result:';print_r($result);echo "<br />";


    if(PEAR::isError($result)){
      $this->elObj->saveDB($result,__FILE__,__LINE__);
    }else{

      foreach((array)$result as $key => $val){

        // データ変換
        $result[$key]['data'] = json_decode($result[$key]['data'], true);
        echo 'input $result[$key][\'data\']:';print_r($result[$key]['data']);echo "<br />";

        // 集計
        $summary = array(
          'q0' => 0,
          'q1' => 0,
          'q2' => 0,
          'q3' => 0,
        );
        foreach($result[$key]['data'] as $key2 => $val2){

          foreach($summary as $key3 => $val3){
//            if(preg_match('/^p/', $key3) && $result[$key]['data'][$key2][$key3]){
            if(in_array($key3, array_keys($summary)) && $result[$key]['data'][$key2][$key3] == "true"){

//              var_dump($result[$key]['data'][$key2][$key3]);echo "<br />";
              $summary[$key3]++;
            }
          }
        }

        // 最大値取得
        arsort($summary);
        reset($summary);
        $max_question = key($summary);
        print_r($summary);


        // パラメータ設定
        $result[$key]['result'] = $max_question;
        $result[$key]['status']++;

        // DB格納
        $result[$key]['data'] = json_encode($result[$key]['data']);
        $this->update($result[$key]);

        // 通知
        $LineBotDAO = new LineBotDAO;
        $members = $LineBotDAO->getMemberList($result[$key]['keyword']);

        // 結果格納
        $q_index = substr($result[$key]['result'], -1, 1);
        echo 'input $q_index:';print_r($q_index);echo "<br />";
        print_r($q_index);
        print_r($members);

        $rep_message = "じゃあ、{$this->question[$q_index]['title']}で！";
        if($summary[$max_question] == 1){
          $rep_message = "じゃあ、{$this->question[$q_index]['title']}で！（一人飲みでw）";
        }

        // 全員: メッセージ送信
        foreach((array)$members as $key3 => $val3) {

          // パマラータ設定(回答リスト)


          // 送信
          $LineBotDAO->pushMessage($members[$key3]['userid'], $rep_message);

        }

      }

    }

    return $result;
  }


  /**
   * 個別取得
   */
  public function findById($id){

    // 初期化
    $sql_where_add = "";
    $result        = "";

    // SQL発行
    $sql    = "SELECT * FROM {$this->tablename} where id = {$id} AND flg = 1 {$sql_where_add}";
    $result = $this->mdb2->queryRow($sql);

    // エラー処理
    if(PEAR::isError($result)){
      $this->elObj->saveDB($result,__FILE__,__LINE__);
    }

    // 結果
    return $result;

  }

  /**
   * 一覧取得
   */
  public function findAll(){
    $sql_where_add = "";

    $pageID  = $this->FRFW->htmlspecialchars_encode_array($_GET["pageID"]);
    if(isset($pageID) && is_numeric($pageID)) $this->pagerOptions['currentPage']= $pageID;

    $sql_where_add .= ($this->is_mdb) ? "" : " AND status = 1 ";

    $sql = "SELECT * FROM {$this->tablename} WHERE flg = 1 {$sql_where_add} ORDER BY post_date DESC, id DESC";
    if($this->is_mdb) {                 // ペイジャーoffモード(公開側:off, CMS側:on)
      $result = Pager_Wrapper_MDB2($this->mdb2, $sql, $this->pagerOptions);
    }else {
      $result['data'] = $this->mdb2->queryAll($sql);
    }

    if(PEAR::isError($result)){
      $this->elObj->saveDB($result,__FILE__,__LINE__);
    } else {
      $result['links'] = str_replace($_SERVER['PHP_SELF'], "",$result['links']);
      return $result;
    }

  }

  public function getGroupList($keyword){

    $sql_where_add = " AND keyword = '{$keyword}'";

    $sql = "SELECT * FROM {$this->tablename} WHERE flg = 1 {$sql_where_add} ORDER BY userid DESC";
    syslog(LOG_EMERG, print_r($sql, true));

    $result = $this->mdb2->queryAll($sql);

    if(PEAR::isError($result)){
      $this->elObj->saveDB($result,__FILE__,__LINE__);
    }

    return $result;
  }

  public function getProfile($usersid)
  {
    $header = array(
      'Content-Type: application/json',
      'Authorization: Bearer ' . CHANNELACCESSTOKEN
    );

    $ch = curl_init('https://api.line.me/v2/bot/profile/'.$usersid);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }

  public function pushMessage($usersid, $msg)
  {
    $format_text = [
      "type" => "text",
      "text" => $msg
    ];

    $post_data = [
      "to" => $usersid,
      "messages" => [$format_text]
    ];

    $header = array(
      'Content-Type: application/json',
      'Authorization: Bearer ' . CHANNELACCESSTOKEN
    );

    $ch = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $result = curl_exec($ch);
    curl_close($ch);
  }

  /**
   * データ登録
   */
  public function insert($pa) {

    // フィールドを追加
    $pa['created_at'] = date("Y-m-d H:i:s");
    $pa['created_by'] = $auth['username'];
    $pa['updated_at'] = date("Y-m-d H:i:s");
    $pa['updated_by'] = $auth['username'];

    $affectedRows = $this->mdb2->extended->autoExecute($this->tablename, $pa, MDB2_AUTOQUERY_INSERT);
    if (PEAR::isError($affectedRows)) { echo $affectedRows->getMessage(); $this->elObj->saveDB($affectedRows,__FILE__,__LINE__); }

    return $this->mdb2->lastInsertID();

  }

  /**
   * データ更新
   */
  public function update($pa) {

    // フィールドを追加
    $pa['updated_at'] = date("Y-m-d H:i:s");
    $pa['updated_by'] = $auth['username'];

    // DBへUPDATEクエリ発行
    $affectedRows = $this->mdb2->extended->autoExecute($this->tablename, $pa, MDB2_AUTOQUERY_UPDATE, 'id = '.$this->mdb2->quote($pa['id'], 'integer'));
    if (PEAR::isError($affectedRows)) { $this->elObj->saveDB($affectedRows,__FILE__,__LINE__); }

  }

  /**
   * データ削除
   */
  public function delete($id){

    // フィールドを追加
    $pa = array();
    $pa['updated_at'] = date("Y-m-d H:i:s");
    $pa['updated_by'] = $auth['username'];
    $pa['flg']    = 0;

    // UPDATE
    $affectedRows = $this->mdb2->extended->autoExecute($this->tablename, $pa, MDB2_AUTOQUERY_UPDATE, 'id = '.$this->mdb2->quote($id, 'integer'));
    if (PEAR::isError($affectedRows)) { $this->elObj->saveDB($affectedRows,__FILE__,__LINE__); }

  }

}
