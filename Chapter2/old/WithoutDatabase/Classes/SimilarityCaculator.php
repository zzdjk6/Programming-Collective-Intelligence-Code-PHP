<?php

require_once 'Person.php';

class SimilarityCaculator {

    public static function getSimilarity(Person $person1, Person $person2, $method = 'Pearson') {
        switch ($method) {
            case 'Pearson':
                return self::getSimilarityByPearsonCorrelation($person1, $person2);
                break;
            case 'Euclidean':
                return self::getSimilarityByEuclideanDistance($person1, $person2);
                break;
            default:
                throw new Exception('No matched method to calc similarity');
                break;
        }
    }

    public static function getSimilarityByEuclideanDistance(Person $person1, Person $person2) {
        $sharedItems = Person::getSharedItems($person1, $person2);
        if (count($sharedItems) === 0) {
            return 0;
        }
        $sumOfSqures = 0;
        foreach ($sharedItems as $itemName) {
            $division = $person1->getEvaluateData($itemName) - $person2->getEvaluateData($itemName);
            $sumOfSqures += $division * $division;
        }
        $similarity = 1 / (1 + sqrt($sumOfSqures));

        return $similarity;
    }

    public static function getSimilarityByPearsonCorrelation(Person $person1, Person $person2) {
        $sharedItems = Person::getSharedItems($person1, $person2);
        $n = count($sharedItems);
        if ($n === 0) {
            return 0;
        }

        $sum1 = 0;
        $sum2 = 0;
        $sum1Sq = 0;
        $sum2Sq = 0;
        $pSum = 0;
        foreach ($sharedItems as $itemName) {
            $evaluateData1 = $person1->getEvaluateData($itemName);
            $evaluateData2 = $person2->getEvaluateData($itemName);
            $sum1 += $evaluateData1;
            $sum2 += $evaluateData2;
            $sum1Sq += pow($evaluateData1, 2);
            $sum2Sq += pow($evaluateData2, 2);
            $pSum += $evaluateData1 * $evaluateData2;
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