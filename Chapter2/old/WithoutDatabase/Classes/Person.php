<?php

require_once 'Critics.php';
require_once 'SimilarityCaculator.php';

class Person {

    protected $evaluateData;
    protected $name;

    public function __construct($name) {
        $this->name = $name;
        $this->evaluateData = Critics::getCritcsData($name);
    }

    public function getName() {
        return $this->name;
    }

    public function getEvaluateData($itemName = null) {
        if ($itemName) {
            return $this->evaluateData[$itemName];
        }
        return $this->evaluateData;
    }

    public function getSimilarityToOthers($method = 'Pearson') {
        $personList = self::getPersonList();
        $similarityToOthers = array();
        foreach ($personList as $person) {
            $myName = $this->getName();
            $hisName = $person->getName();
            if ($hisName === $myName) {
                continue;
            }
            $similarity = SimilarityCaculator::getSimilarity($this, $person, $method);
            $similarityToOthers[$hisName] = $similarity;
        }
        return $similarityToOthers;
    }

    public function getTopMatches($limit = 5, $method = 'Pearson') {
        $similarityToOthers = $this->getSimilarityToOthers($method);
        $allMatches = array();
        foreach ($similarityToOthers as $hisName => $similarity) {
            $allMatches[] = array($similarity,$hisName);
        }
        rsort($allMatches);
        $topMatches = array_slice($allMatches, 0, $limit);
        return $topMatches;
    }

    public function getRecommendations($method = 'Pearson') {
        $similarityToOthers = $this->getSimilarityToOthers($method);
        $itemsData = Critics::getItemsData();
        foreach($this->evaluateData as $itemName => $itemEvalute){
            if(isset($itemsData[$itemName])){
                unset($itemsData[$itemName]);
            }
        }
        
        $simSum = array();
        $total = array();
        foreach($itemsData as $itemName => $personEvaluate){
            $simSum[$itemName] = 0;
            $total[$itemName] = 0;
            foreach ($personEvaluate as $hisName => $evaluate) {
                $similarity = $similarityToOthers[$hisName];
                $simSum[$itemName] += $similarity;
                $total[$itemName] += $similarity * $evaluate;
            }
        }
        
        $result = array();
        foreach($total as $itemName => $itemTotal){
            $result[$itemName] = $itemTotal / $simSum[$itemName];
        }
        
        arsort($result);
        return $result;
    }

    public static function getSharedItems(Person $person1, Person $person2) {
        $items1 = array_keys($person1->getEvaluateData());
        $items2 = array_keys($person2->getEvaluateData());
        $shareItems = array_intersect($items1, $items2);
        return $shareItems;
    }

    public static function getPersonList() {
        $personList = array();
        $personList[] = new Person('Lisa Rose');
        $personList[] = new Person('Gene Seymour');
        //$personList[] = new Person('Michael Phillips');
        $personList[] = new Person('Claudia Puig');
        $personList[] = new Person('Mick LaSalle');
        $personList[] = new Person('Jack Matthews');
        $personList[] = new Person('Toby');
        return $personList;
    }

}