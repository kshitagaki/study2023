<?php

class myDB {

    const DB_HOST = '********';
    const DB_NAME = '********';
    const DB_USERID = '********';
    const DB_PASSWD = '********';

    public $mydb = null;
    public $exception = null;

    function connect() {
        // 接続
        try{
            $this->mydb = new PDO('mysql:host='.self::DB_HOST.';dbname='.self::DB_NAME, self::DB_USERID, self::DB_PASSWD);
        } catch(PDOException $e){
            echo "データベース接続失敗" . PHP_EOL;
            echo $e->getMessage();
            exit;
        }
    }        
    function disconnect() {    
        // 切断
        $this->mydb = null;
    }

    function select($sql, $param) {

        // PDOStatementクラスのインスタンスを生成します。
        $prepare = $this->mydb->prepare($sql);

        // PDO::PARAM_INTは、SQL INTEGER データ型を表します。
        // SQL文の「:id」を「3」に置き換えます。つまりはidが3より小さいレコードを取得します。
        foreach($param as $value) {
            $prepare->bindValue(':'.$value['col'], $value['value'], $value['type']);
        }

        // プリペアドステートメントを実行する
        $prepare->execute();

        // PDO::FETCH_ASSOCは、対応するカラム名にふられているものと同じキーを付けた 連想配列として取得します。
        $result = $prepare->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    function execute($sql, $param) {

        try {
            // PDOStatementクラスのインスタンスを生成します。
            $prepare = $this->mydb->prepare($sql);

            // PDO::PARAM_INTは、SQL INTEGER データ型を表します。
            // SQL文の「:id」を「3」に置き換えます。つまりはidが3より小さいレコードを取得します。
            foreach($param as $value) {
                $prepare->bindValue(':'.$value['col'], $value['value'], $value['type']);
            }

            // プリペアドステートメントを実行する
            $prepare->execute();

        } catch(PDOException $e){
            $this->exception = $e;
            return false;
        }
        return true;
    }
    function getException() {
        return $this->exception;
    }

}
