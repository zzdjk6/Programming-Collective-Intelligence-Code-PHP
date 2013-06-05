<?php

class Recommendation {
    public $score;
    public $item;
    
    public function __construct($score,$item) {
        $this->score = $score;
        $this->item = $item;
    }
}
