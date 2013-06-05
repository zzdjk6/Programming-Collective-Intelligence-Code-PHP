<?php

namespace Chapter2;

class SharedItem {

    public $itemId;
    public $score1;
    public $score2;

    public function __construct($itemId, $score1, $score2) {
        $this->itemId = $itemId;
        $this->score1 = $score1;
        $this->score2 = $score2;
    }

}