<?php

namespace Chapter2;

require_once 'Database.php';

class Item {

    public $id;
    public $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public static function getItemList() {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT id,name FROM movies');
        $statement->execute();
        $result = array();
        while ($line = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = new Item($line['id'], $line['name']);
        }
        return $result;
    }

    public static function getItemById($id) {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT name FROM movies WHERE id = :id');
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->bindParam(':id', $id);
        $result = $statement->fetch();
        return new Item($id, $result['name']);
    }
}

if (!debug_backtrace()) {
    var_export(Item::getItemList());
}
