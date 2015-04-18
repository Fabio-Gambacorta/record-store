<?php
require_once('store.class.php');

$obj = new Store();

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':
    
        if (!isset($_GET['id'])) {
            $return = $obj->load_list();
        }
    
        break;
    case 'POST':
    
        $post = json_decode(file_get_contents('php://input'), true);
    
        $return = $obj->save($post);
    
        break;
    case 'DELETE':
    
        $post = explode("store/", $_SERVER['REQUEST_URI']);
    
        $return = $obj->delete($post[1]);
        
        break;
}

echo json_encode($return);