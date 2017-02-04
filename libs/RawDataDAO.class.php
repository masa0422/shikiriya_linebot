<?php
/**
 * Project     : FRFW
 * description : RawData DAO / PHP v5.6 / PEAR::MDB2
 * @link       : https://www.fourier.jp/
 * @copyright  : FOURIER Inc.
 * @author     : FOURIER Inc.
 * @package    : -
 * @version    : 1.0.0
 */
class RawDataDAO {

  // テーブル定義
  private $tablename = "lba_rawdata";

  // 選択肢定義
  public $sample       = array();
  public $options      = array();

  // DBテーブル非存在フィールド設定
  public $unset_key = array("submit", "file", "csrf_token");

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
