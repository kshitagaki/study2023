<?php
require_once('./clsMyDB.php');

class myPoint {

    const TABLE_NAME = 'my_point';
    const COLUMN = [
        ['col' => 'dt', 'type' => PDO::PARAM_STR ],
        ['col' => 'lon', 'type' => PDO::PARAM_STR ],
        ['col' => 'lat', 'type' => PDO::PARAM_STR ],
        ['col' => 'name', 'type' => PDO::PARAM_STR ],
        ['col' => 'url', 'type' => PDO::PARAM_STR ],
        ['col' => 'remarks', 'type' => PDO::PARAM_STR ],
    ];
    const DELETE_FLG_ON = 1;

    function setPoint($json) {
        if (!$json) {
            return false;
        }
        if (!$json['lon'] || !$json['lat']) {
            header("HTTP/1.0 404 Not Found");
            return false;
        }
        $dt = $json['dt'];

        $errorMsg = null;
        if ($dt) {
            // 更新
            $mydb = new myDB();
            $mydb->connect();

            $param = [];

            $sql = 'UPDATE '.self::TABLE_NAME;
            $sql .= ' SET ';
            foreach(self::COLUMN as $val) {
                $column = $val['col'];
                $param[] = array('col' => $column, 'value' => $json[$column], 'type' => $val['type']);

                // dtは更新しない
                if ($column != 'dt') {
                    $sql .= $column.' = :'.$column.',';
                }
            }

            $sql .= ' update_dt = CURRENT_TIMESTAMP';
            $sql .= ' WHERE ';
            $sql .= ' dt = :dt';

            $result = $mydb->execute($sql, $param);
            if (!$result) {
                $e = $mydb->getException();
                $errorMsg = $e->getMessage();
            }
            $mydb->disconnect();


        } else {
            // 受け取ったデータを保存（新規）
            $now = new DateTime();
            $dt = $now->format('YmdHisu');
            $mydb = new myDB();
            $mydb->connect();

            $sql1 = '';
            $sql2 = '';
            $param = [];
            foreach(self::COLUMN as $val) {
                $column = $val['col'];
                if ($sql1) {
                    $sql1 .= ',';
                }
                $sql1 .= $column;
                if ($sql2) {
                    $sql2 .= ',';
                }
                $sql2 .= ':'.$column;
                $param[] = array('col' => $column, 'value' => $json[$column], 'type' => $val['type']);
            }

            $sql = 'INSERT INTO '.self::TABLE_NAME;
            $sql .= ' ('.$sql1.') ';
            $sql .= ' VALUES ';
            $sql .= ' ('.$sql2.') ';
            $result = $mydb->execute($sql, $param);
            if (!$result) {
                $e = $mydb->getException();
                $errorMsg = $e->getMessage();
            }

            $mydb->disconnect();

        }
        
        $data = array("result" => true);
        if ($errorMsg) {
            $data = array("result" => false, "message" => $errorMsg);
        }
        echo json_encode($data);
        return true;
    }

    function getPoints($condition) {
        $list = [];

        $mydb = new myDB();
        $mydb->connect();

        $sql = 'SELECT ';
        $param = [];
        foreach(self::COLUMN as $val) {
            $column = $val['col'];
            $sql .= $column.',';
        }
        $sql .= ' create_dt,';
        $sql .= ' update_dt';
        $sql .= ' FROM '.self::TABLE_NAME;
        $sql .= ' WHERE ';
        $sql .= ' delete_flg != :delete_flg_on ';
        $sql .= ' ORDER BY dt';
        $param = [];
        $param[] = array('col' => 'delete_flg_on', 'value' => self::DELETE_FLG_ON, 'type' => PDO::PARAM_INT);
        $list = $mydb->select($sql, $param);

        $mydb->disconnect();

        return $list;
    }

    function deletePoint($json) {
        if (!$json) {
            return false;
        }
        $dt = $json['dt'];
        if (!$dt) {
            header("HTTP/1.0 404 Not Found");
            return false;
        }

        $errorMsg = null;

        // 削除（論理削除）
        $mydb = new myDB();
        $mydb->connect();

        $sql = 'UPDATE '.self::TABLE_NAME;
        $sql .= ' SET ';
        $sql .= ' delete_flg = :delete_flg_on ';
        $sql .= ',update_dt = CURRENT_TIMESTAMP';
        $sql .= ' WHERE ';
        $sql .= ' dt = :dt';
        $param = [];
        $param[] = array('col' => 'dt', 'value' => $dt, 'type' => PDO::PARAM_STR);
        $param[] = array('col' => 'delete_flg_on', 'value' => self::DELETE_FLG_ON, 'type' => PDO::PARAM_INT);
        $result = $mydb->execute($sql, $param);
        if (!$result) {
            $e = $mydb->getException();
            $errorMsg = $e->getMessage();
        }
        $mydb->disconnect();

        $data = array("result" => true);
        if ($errorMsg) {
            $data = array("result" => false, "message" => $errorMsg);
        }
        echo json_encode($data);
        return true;
    }

}