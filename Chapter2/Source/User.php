<?php

namespace Chapter2;

require_once 'Database.php';
require_once 'SharedItem.php';
require_once 'TopMatch.php';
require_once 'Item.php';
require_once 'ItemEvalute.php';
require_once 'Recommendation.php';

class User {

    const SIMILARITY_METHOD_EUCLIDEAN = 1;
    const SIMILARITY_METHOD_PEARSON = 2;

    public $id;
    public $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public static function getUserByName($name) {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT id,name FROM users WHERE name = :name');
        $statement->bindParam(':name', $name);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return new User($result['id'], $result['name']);
    }

    public static function getUserList() {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT id,name FROM users');
        $statement->execute();
        $result = array();
        while ($line = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = new User($line['id'], $line['name']);
        }
        return $result;
    }

    public static function getUserById($id) {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT id,name FROM users WHERE id = :id');
        $statement->bindParam(':id', $id);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return new User($result['id'], $result['name']);
    }

    public function getSimlarity(User $other, $method = self::SIMILARITY_METHOD_PEARSON) {
        $sharedItemList = $this->getSharedItemList($other);
        if (count($sharedItemList) == 0) {
            return 0;
        }

        switch ($method) {
            case self::SIMILARITY_METHOD_EUCLIDEAN:
                return $this->_getSimilarityByEuclideanDistance($sharedItemList);
            case self::SIMILARITY_METHOD_PEARSON:
                return $this->_getSimilarityByPearsonCorrelation($sharedItemList);
            default:
                throw new Exception('Wrong Similarity Method');
        }
    }

    public function getSharedItemList(User $other) {
        $pdo = Database::getPdo();
        $sharedItemIds = $this->_getSharedItemIds($other);
        $sharedItemList = array();
        $statement = $pdo->prepare('SELECT score FROM criticisms WHERE user_id = :user_id AND movie_id = :movie_id');
        foreach ($sharedItemIds as $itemId) {
            $statement->bindParam(':movie_id', $itemId);

            $statement->bindParam(':user_id', $this->id);
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            $score1 = $result['score'];

            $statement->bindParam(':user_id', $other->id);
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            $score2 = $result['score'];

            $sharedItemList[] = new SharedItem($itemId, $score1, $score2);
        }

        return $sharedItemList;
    }

    public function getTopMatches() {
        $userList = self::getUserList();
        $topMatches = array();
        foreach ($userList as $user) {
            if ($user->id == $this->id) {
                continue;
            }
            $similarity = $this->getSimlarity($user);
            $topMatches[] = new TopMatch($similarity, $user);
        }
        usort($topMatches, 'Chapter2\TopMatch::rcmp');
        return $topMatches;
    }

    public function getRecommandationList() {
        $recommendationList = array();
        $unCheckedItemList = $this->getUnCheckedItemList();
        foreach ($unCheckedItemList as $item) {
            $itemEvaluteList = ItemEvalute::getItemEvaluateListByItem($item);
            $similaritySum = 0;
            $scoreSum = 0;
            foreach ($itemEvaluteList as $itemEvalute) {
                $similarity = $this->getSimlarity($itemEvalute->user);
                $scoreSum += $itemEvalute->score * $similarity;
                $similaritySum += $similarity;
            }
            $recommendScore = $scoreSum / $similaritySum;
            $recommendationList[] = new Recommendation($item, $recommendScore);
        }
        usort($recommendationList, 'Chapter2\Recommendation::rcmp');
        return $recommendationList;
    }

    public function getUnCheckedItemList() {
        $checkedItemList = $this->getCheckedItemList();
        $itemList = Item::getItemList();
        $unCheckedItemList = array();
        foreach ($itemList as $item) {
            if (!in_array($item, $checkedItemList)) {
                $unCheckedItemList[] = $item;
            }
        }
        return $unCheckedItemList;
    }

    public function getCheckedItemList() {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT t1.movie_id as id,t2.name as name FROM criticisms t1 LEFT JOIN movies t2 ON t1.movie_id = t2.id  WHERE user_id = :user_id');
        $statement->bindParam(':user_id', $this->id);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->execute();
        $result = $statement->fetchAll();
        $checkedItems = array();
        foreach ($result as $line) {
            $checkedItems[] = new Item($line['id'], $line['name']);
        }
        return $checkedItems;
    }

    protected function _getSharedItemIds(User $other) {
        $pdo = Database::getPdo();
        $statement = $pdo->prepare('SELECT movie_id FROM criticisms WHERE user_id = :user_id');

        $statement->bindParam(':user_id', $this->id);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $itemIds1 = array_values($result);

        $statement->bindParam(':user_id', $other->id);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $itemIds2 = array_values($result);

        return array_intersect($itemIds1, $itemIds2);
    }

    protected function _getSimilarityByEuclideanDistance(array $sharedItemList) {
        $sumOfSquares = 0;
        foreach ($sharedItemList as $sharedItem) {
            /* @var $sharedItem SharedItem */
            $sumOfSquares += pow($sharedItem->score1 - $sharedItem->score2, 2);
        }
        $distance = sqrt($sumOfSquares);

        return 1 / (1 + $distance);
    }

    protected function _getSimilarityByPearsonCorrelation(array $sharedItemList) {
        $n = count($sharedItemList);
        $sum1 = 0;
        $sum2 = 0;
        $sum1Sq = 0;
        $sum2Sq = 0;
        $pSum = 0;
        foreach ($sharedItemList as $sharedItem) {
            /* @var $sharedItem SharedItem */
            $sum1 += $sharedItem->score1;
            $sum2 += $sharedItem->score2;
            $sum1Sq += pow($sharedItem->score1, 2);
            $sum2Sq += pow($sharedItem->score2, 2);
            $pSum += $sharedItem->score1 * $sharedItem->score2;
        }
        $num = $pSum - ($sum1 * $sum2 / $n);
        $den = sqrt(($sum1Sq - pow($sum1, 2) / $n) * ($sum2Sq - pow($sum2, 2) / $n));
        if ($den === 0) {
            return 0;
        }

        $r = $num / $den;
        return $r;
    }

}

if (!debug_backtrace()) {
//    $lisa = User::getUserByName('Lisa Rose');
//    $gene = User::getUserByName('Gene Seymour');
//
//    echo $lisa->getSimlarity($gene, User::SIMILARITY_METHOD_EUCLIDEAN), "\n";
//    echo $lisa->getSimlarity($gene, User::SIMILARITY_METHOD_PEARSON), "\n";
//
//    var_dump(User::getUserByName('Toby')->getTopMatches());
    
    var_dump(User::getUserByName('Toby')->getRecommandationList());
}