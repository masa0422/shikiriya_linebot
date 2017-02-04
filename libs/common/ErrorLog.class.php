<?php

class ErrorLog{

  public  $webmaster_email_address = "webmaster+log@fourier.jp";

  private $tablename_log_db   = "mdb_log_error";
  private $tablename_log_mail = "mdb_log_mail";

  function __construct(){

    global $mdb2;

    $this->mdb2 = $mdb2;

  }

  // public function save($result,$file_path,$line_number){

  //   $this->saveDB($result,$file_path,$line_number);

  // }

  public function saveDB($result,$file_path,$line_number){

    // DB登録
    $insert['url']           = "";
//    $insert['url']           = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $insert['file_path']     = $file_path;
    $insert['line_number']   = $line_number;
    $insert['error_content'] = serialize($result);
    $insert['created_by']    = date('Y-m-d H:i:s');
    $affectedRows            = $this->mdb2->extended->autoExecute($this->tablename_log_db, $insert, MDB2_AUTOQUERY_INSERT);

  }

  public function sendMailErrorLog ($type,$email,$body_cs,$flg) {

    $log['mail_type'] = $type;
    $log['email']     = $email;
    $log['body']      = $body_cs;
    $log['insdt']     = date("Y-m-d H:i:s");
    $log['flg']       = $flg; //3=>成功、2=>失敗(顧客)、1=>失敗(店舗)、0=>失敗

    // メール送信ログ登録
    $affectedRows = $this->mdb2->extended->autoExecute($this->tablename_log_mail, $log, MDB2_AUTOQUERY_INSERT);
    if (PEAR::isError($affectedRows)) { die("INSERT ERROR.".$affectedRows->getMessage()); }
  }

}
