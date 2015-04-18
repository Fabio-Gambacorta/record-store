<?php
define("DB_HOST", "localhost");
define("DB_USER", "USERNAME");
define("DB_PASS", "PASSWORD");
define("DB_NAME", "record_store");

class Database {
    
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
 
    private $dbh;
    private $error;
 
    public function __construct()
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            var_dump($this->error);
            exit();
        }
    }
    
    public function selectCount($table, $condition = "", $order = "", $limit = "")
    {
        if (!empty($condition)) {
            $condition = "WHERE " . $condition;
        }
        
        if (!empty($order)) {
            $order = "ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $limit = "LIMIT " . $limit;
        }
        
        $query = $this->dbh->prepare("SELECT COUNT(*) FROM $table $condition $order $limit");
        $query->execute();
        $rowCount = $query->fetch(PDO::FETCH_NUM);
        return $rowCount;
    }
    
    public function selectAll($fields, $table, $condition = "", $order = "", $limit = "")
    {
        $values = array("");
        if (!empty($condition)) {
            $prepared = $this->prepareSelect($condition);
            $keyString = $prepared["keys"];
            $values = $prepared["values"];
            $condition = "WHERE " . $keyString;
        }
        if (!empty($order)) {
            $order = "ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $limit = "LIMIT $limit";
        }
        $sql = "SELECT $fields FROM $table $condition $order $limit";
        $query = $this->dbh->prepare($sql);
        $query->execute($values);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function selectJoinAll($fields, $table, $condition = "", $order = "", $limit = "")
    {
        if (!empty($condition)) {
            $condition = "WHERE " . $condition;
        }
        if (!empty($order)) {
            $order = "ORDER BY " . $order;
        }
        if (!empty($limit)) {
            $limit = "LIMIT $limit";
        }
        
        try {
            $sql = "SELECT $fields FROM $table $condition $order $limit";
            $query = $this->dbh->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $result = $e->getMessage();
        }
        
        
        return $result;
    }
    
    public function selectSingle($fields, $table, $condition)
    {
        if (!empty($condition)) {
            $condition = "WHERE " . $condition;
        }
        $query = $this->dbh->prepare("SELECT $fields FROM $table $condition LIMIT 1");
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
    
    public function insert($table, $data)
    {
        $prepared = $this->prepareInsert($data);
        $keysString = $prepared["keys"];
        $valuesString = $prepared["valuesString"];
        $values = $prepared["values"];
        
        $query = $this->dbh->prepare("INSERT INTO $table ($keysString) VALUES ($valuesString)");
        $res = $query->execute($values);
        if (!$res) {
            return false;
        }
        return true;
    }
    
    public function update($table, $data, $where)
    {
        $prepared = $this->prepareUpdate($data, $where);
        $keys = $prepared["keys"];
        $whereKeys = $prepared["whereKeys"];
        $values = $prepared["values"];
        
        $query = $this->dbh->prepare("UPDATE $table SET $keys WHERE $whereKeys");
        $res = $query->execute($values);
        if (!$res) {
            return false;
        }
        return true;
        
    }
    
    public function delete($table, $condition)
    {
        $prepared = $this->prepareSelect($condition);
        $keyString = $prepared["keys"];
        $values = $prepared["values"];
        $query = $this->dbh->prepare("DELETE FROM $table WHERE $keyString");
        if (!$query->execute($values)) {
            return false;
        }
        return true;
    }
    
    private function prepareSelect($data)
    {
        $dataArray = array();
        $keys = array();
        $values = array();
        if (!strstr($data, " AND ")) {
            $dataArr = explode(" = ", $data);
            $dataArray[] = array($dataArr[0], $dataArr[1]);
        } else {
            $dataArr = explode(" AND ", $data);
            foreach ($dataArr as $key => $value) {
                $dataSubArr = explode(" = ", $value);
                $dataArray[] = array($dataSubArr[0], $dataSubArr[1]);
            }
        }
        
        foreach ($dataArray as $key => $value) {
            $keys[] = $value[0] . " = ?";
            $values[] = $value[1];
        }
        $keysString = implode(" AND ", $keys);
        $returnArray = array('keys' => $keysString, 'values' => $values);
        return $returnArray;
    }
    
    private function prepareInsert($data)
    {
        $keys = array();
        $valuesArray = array();
        $values = array();
        foreach ($data as $key => $value) {
            $keys[] = $key;
            $valuesFields = ":" . $key;
            $valuesArray[] = $valuesFields;
            $values[$valuesFields] = $value;
        }
        $keysString = implode(", ", $keys);
        $valuesString = implode(", ", $valuesArray);
        
        $returnArray = array(
            "keys" => $keysString,
            "valuesString" => $valuesString,
            "values" => $values
        );
        return $returnArray;
    }
    
    private function prepareUpdate($data, $where)
    {
        $fields = $this->prepareUpdateFields($data);
        $keysString = implode(", ", $fields["keys"]);
        $values = $fields["values"];
        
        $wheres = $this->prepareUpdateFields($where);
        $whereKeysString = implode(" AND ", $wheres["keys"]);
        $whereValues = $wheres["values"];
        
        $values_merge = array_merge($values, $whereValues);
        
        $returnArray = array(
            "keys" => $keysString,
            "whereKeys" => $whereKeysString,
            "values" => $values_merge
        );
        return $returnArray;
    }
    
    private function prepareUpdateFields($data)
    {
        $keys = array();
        $values = array();
        foreach ($data as $key => $value) {
            $keys[] = $key . "=?";
            $values[] = $value;
        }
        return array("keys" => $keys, "values" => $values);
    }
    
    public function getLastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
    
}
