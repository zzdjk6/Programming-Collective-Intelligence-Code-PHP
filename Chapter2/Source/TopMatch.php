<?php

namespace Chapter2;

class TopMatch {

    /**
     *
     * @var double 
     */
    public $similarity;

    /**
     *
     * @var User 
     */
    public $user;

    public function __construct($similarity, User $user) {
        $this->similarity = $similarity;
        $this->user = $user;
    }

    public static function cmp(TopMatch $a, TopMatch $b) {
        return $a->similarity > $b->similarity ? 1 : -1;
    }

    public static function rcmp(TopMatch $a, TopMatch $b) {
        return 0 - self::cmp($a, $b);
    }

}

?>
