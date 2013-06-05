<?php

namespace Chapter2;

require_once 'Item.php';
require_once 'User.php';
require_once 'Database.php';

class ItemEvalute {

    /**
     *
     * @var Item
     */
    public $item;

    /**
     *
     * @var User
     */
    public $user;

    /**
     *
     * @var double 
     */
    public $score;

    public function __construct(Item $item, User $user, $score) {
        $this->item = $item;
        $this->user = $user;
        $this->score = $score;
    }

    public static function getItemEvaluateListByItem(Item $item) {
        $itemEvaluateList = array();

        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT user_id,score FROM criticisms WHERE movie_id = :movie_id');
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->bindParam(':movie_id', $item->id);
        $statement->execute();
        while ($line = $statement->fetch()) {
            $user = User::getUserById($line['user_id']);
            $score = $line['score'];
            $itemEvaluateList[] = new ItemEvalute($item, $user, $score);
        }

        return $itemEvaluateList;
    }

}

?>
