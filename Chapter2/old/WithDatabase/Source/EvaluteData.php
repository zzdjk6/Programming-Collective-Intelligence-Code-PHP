<?php

class EvaluateData {
    public $userId;
    public $itemId;
    public $score;
    
    private $_strMap = array(
        'user_id' => 'userId',
        'movie_id' => 'itemId',
    );


    public function __set($name, $value) {
        $name = $this->_strMap[$name];
        $this->$name = $value;
    }
}
