<?php

require_once './Data.php';
require_once './SimilarityCaculator.php';
require_once './Item.php';
require_once './Recommendation.php';
require_once './EvaluteData.php';

class Person {

    public $id;
    public $name;
    public $evaluateData;

    public function __construct($id, $name) {
        $this->id = intval($id);
        $this->name = strval($name);
    }

    public function getId() {
        return intval($this->id);
    }

    public function getName() {
        return strval($this->name);
    }

    public function getEvaluateData() {
        if (!$this->evaluateData) {
            $pdo = Data::getPdo();
            $statement = $pdo->prepare("SELECT movie_id,score FROM criticisms WHERE user_id = :user_id");
            $statement->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute();
            $result = $statement->fetchAll();
            foreach ($result as $line) {
                $this->evaluateData[$line['movie_id']] = $line['score'];
            }
        }

        return $this->evaluateData;
    }

    /**
     * 根据名字产生Person实例
     * @param string $name
     * @return Person
     * @throws Exception
     */
    public static function getUserByName($name) {
        if (!trim($name)) {
            throw new Exception('Name invalid');
        }
        $pdo = Data::getPdo();
        $statement = $pdo->prepare("SELECT id,name FROM users WHERE name = :name");
        $statement->bindParam(':name', $name);
        $statement->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Person', array('id', 'name'));
        $statement->execute();
        return $statement->fetch();
    }

    public static function getEvaluateDataById($id) {
        $pdo = Data::getPdo();
        $statement = $pdo->prepare("SELECT movie_id,score FROM criticisms WHERE user_id = :user_id");
        $statement->bindParam(':user_id', $id);
        $statement->setFetchMode(PDO::FETCH_KEY_PAIR);
        $statement->execute();
        $result = $statement->fetchAll();
        return $result;
    }

    public static function getSharedEvaluatedDataList($person1Id, $person2Id) {
        $evaluateData1 = Person::getEvaluateDataById($person1Id);
        $evaluateData2 = Person::getEvaluateDataById($person2Id);

        $itemsList1 = array_keys($evaluateData1);
        $itemsList2 = array_keys($evaluateData2);
        $shareItemIds = array_intersect($itemsList1, $itemsList2);

        $sharedEvaluateDataList = array();
        foreach ($shareItemIds as $sharedItemId) {
            $sharedEvaluateDataList[$sharedItemId] = array(
                'evaluateByP1' => $evaluateData1[$sharedItemId],
                'evaluateByP2' => $evaluateData2[$sharedItemId],
            );
        }
        return $sharedEvaluateDataList;
    }

    public static function getPersonList() {
        $pdo = Data::getPdo();
        $statement = $pdo->prepare("SELECT id,name FROM users");
        $statement->execute();
        $personList = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
        return $personList;
    }

    public function getTopMatches($limit = 3) {
        $limit = intval($limit);

        $personList = self::getPersonList();
        $matchList = array();
        foreach ($personList as $other) {
            if ($other->getId() === $this->getId()) {
                continue;
            }
            $similarity = SimilarityCaculator::getSimilarity($this, $other, 'Pearson');
            $matchList[] = array($similarity, $other);
        }
        rsort($matchList);
        return array_slice($matchList, 0, $limit);
    }

    public function getRecommendation() {
        $itemList = $this->getUnEvaluatedItemList();
        $itemEvaluateDataList = $this->getItemEvaluateDataList($itemList);
        $similarityData = $this->getSimilarityData();
        $recommendation = array();
        foreach ($itemEvaluateDataList as $itemId => $itemEvaluateData) {
            $total = 0;
            $simSum = 0;
            foreach ($itemEvaluateData as $personEvaluate) {
                $total += $personEvaluate->score * $similarityData[$personEvaluate->userId];
                $simSum += $similarityData[$personEvaluate->userId];
            }
            $score = $total / $simSum;
            $recommendation[$itemId] = new Recommendation($score, $itemList[$itemId]);
        }
        uasort($recommendation, function($obj1, $obj2) {
                    return $obj1->score < $obj2->score ? 1 : -1;
                });
        return $recommendation;
    }

    public function getRecommendation2() {
        $unEvaluatedItemList = $this->getUnEvaluatedItemList2();
        $unEvaluatedItemEvaluteDataList = $this->getItemEvaluteData2($unEvaluatedItemList);
        $similarityData = $this->getSimilarityData();
        $recommendation = array();
        foreach ($unEvaluatedItemEvaluteDataList as $itemId => $itemEvaluateData) {
            $total = 0;
            $simSum = 0;
            foreach ($itemEvaluateData as $personId => $score) {
                $total += $score * $similarityData[$personId];
                $simSum += $similarityData[$personId];
            }
            $score = $total / $simSum;
            $recommendation[] = array(
                'item_id' => $itemId,
                'item_name' => $unEvaluatedItemList[$itemId],
                'score' => $score
            );
        }

        usort($recommendation, function(array $a, array $b) {
                    return $a['score'] > $b['score'] ? -1 : 1;
                });

        return $recommendation;
    }

    public function getUnEvaluatedItemList2() {
        $pdo = Data::getPdo();
        $statement = $pdo->prepare('SELECT id,name FROM movies WHERE id NOT IN (SELECT movie_id FROM criticisms WHERE user_id=:user_id)');
        $statement->bindParam(':user_id', $this->id);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $itemList = array();
        foreach ($result as $line) {
            $itemList[$line['id']] = $line['name'];
        }
        return $itemList;
    }

    public function getItemEvaluteData2($itemList) {
        $itemEvaluateData = array();
        $pdo = Data::getPdo();
        $statement = $pdo->prepare("SELECT user_id,score FROM criticisms WHERE movie_id = :movie_id");
        foreach ($itemList as $itemId => $itemName) {
            $statement->bindParam(':movie_id', $itemId);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $line) {
                $itemEvaluateData[$itemId][$line['user_id']] = $line['score'];
            }
        }
        return $itemEvaluateData;
    }

    public function getSimilarityData() {
        $personList = self::getPersonList();
        $similarityData = array();
        foreach ($personList as $personId => $personName) {
            if ($personId == $this->id) {
                continue;
            }
            $similarity = SimilarityCaculator::getSimilarity($this->id, $personId, SimilarityCaculator::METHOD_PEARSON);
            $similarityData[$personId] = $similarity;
        }
        return $similarityData;
    }

    public function getUnEvaluatedItemList() {
        $pdo = Data::getPdo();
        $statement = $pdo->prepare('SELECT id,name FROM movies WHERE id NOT IN (SELECT movie_id FROM criticisms WHERE user_id=:user_id)');
        $statement->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Item', array('id', 'name'));
        $statement->bindParam(':user_id', $this->id);
        $statement->execute();
        $items = $statement->fetchAll();
        $itemList = array();
        foreach ($items as $item) {
            $itemList[$item->id] = $item;
        }
        return $itemList;
    }

    public function getItemEvaluateDataList(array $itemList) {
        $itemEvaluateDataList = array();
        $pdo = Data::getPdo();
        $statement = $pdo->prepare("SELECT user_id,movie_id,score FROM criticisms WHERE movie_id = :movie_id");
        $statement->setFetchMode(PDO::FETCH_CLASS, 'EvaluateData');
        foreach ($itemList as $itemId => $item) {
            $statement->bindParam(':movie_id', $itemId);
            $statement->execute();
            $itemEvaluateData = $statement->fetchAll();
            $itemEvaluateDataList[$itemId] = $itemEvaluateData;
        }
        return $itemEvaluateDataList;
    }

}

// 如果是直接运行
if (!debug_backtrace()) {
    $personLisa = Person::getUserByName('Lisa Rose');
//    $personLisa->getEvaluateData();
//    var_dump($personLisa);
//
    $personGene = Person::getUserByName('Gene Seymour');
//    $personGene->getEvaluateData();
//    var_dump($personGene);
//
//    var_dump(Person::getSharedEvaluatedDataList($personLisa->id, $personGene->id));
//    
//    var_dump(Person::getPersonList());

//    $personToby = Person::getUserByName('Toby');
//    var_export($personToby->getTopMatches());
//    var_export($personToby->getRecommendation2());
}