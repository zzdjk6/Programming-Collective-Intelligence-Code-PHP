<?php

namespace Chapter2;

require_once 'Item.php';

class Recommendation {

    /**
     *
     * @var Item
     */
    public $item;

    /**
     *
     * @var double
     */
    public $score;

    public function __construct(Item $item, $score) {
        $this->item = $item;
        $this->score = $score;
    }

    public static function cmp(Recommendation $a, Recommendation $b) {
        return $a->score > $b->score ? 1 : -1;
    }

    public static function rcmp(Recommendation $a, Recommendation $b) {
        return 0 - self::cmp($a, $b);
    }

}
