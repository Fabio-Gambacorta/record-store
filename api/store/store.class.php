<?php
require_once('../libs/database.class.php');
class Store {
    private $dbx;
    private $table;
    
    public function __construct()
    {
        $this->dbx = new Database();
        $this->table = 'albums';
    }
    
    public function load_list()
    {
        $result = array();
        
        $res = $this->dbx->selectAll('*', $this->table, "", "title");
        if (!$res) {
            if (count($res) !== 0) {
                return array('error' => 1);
            }
        }
        
        foreach ($res as $row) {
            $rowArray = array (
                'id' => $row['id'],
                'title' => $row['title'],
                'author' => $row['author']
            );
            $result[] = $rowArray;
        }
        return $result;
    }
    
    public function save($post)
    {
        if (!$this->dbx->insert($this->table, $post)) {
            return array('error' => 1, 'message' => "Errore nel salvataggio dei dati!!");
        }
        return array('error' => 0);
    }
    
    public function delete($id)
    {
        if (!$this->dbx->delete($this->table, "id = $id")) {
            return array('error' => 1, 'message' => "Errore!!");
        }
        return array('error' => 0);
    }
    
}