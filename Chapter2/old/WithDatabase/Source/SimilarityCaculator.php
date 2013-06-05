<?php

require_once './Person.php';

class SimilarityCaculator {
    
    const METHOD_EUCLIDEAN = 1;
    const METHOD_PEARSON = 2;

    /**
     * 
     * @param Person $person1
     * @param Person $person2
     * @param int $method
     * @return double
     * @throws Exception
     */
    public static function getSimilarity($person1Id, $person2Id, $method = self::METHOD_EUCLIDEAN) {
        switch ($method) {
            case self::METHOD_PEARSON:
                return self::getSimilarityByPearsonCorrelation($person1Id, $person2Id);
                break;
            case self::METHOD_EUCLIDEAN:
                return self::getSimilarityByEuclideanDistance($person1Id, $person2Id);
                break;
            default:
                throw new Exception('No matched method to calc similarity');
                break;
        }
    }

    public static function getSimilarityByEuclideanDistance($person1Id, $person2Id) {
        $sharedEvaluatedDataList = Person::getSharedEvaluatedDataList($person1Id, $person2Id);

        if (count($sharedEvaluatedDataList) === 0) {
            return 0;
        }

        $sumOfSquare = 0;
        foreach ($sharedEvaluatedDataList as $itemId => $itemEvaluate) {
            $evaluateScore1 = $itemEvaluate['evaluateByP1'];
            $evaluateScore2 = $itemEvaluate['evaluateByP2'];
            $sumOfSquare += pow($evaluateScore1 - $evaluateScore2, 2);
        }
        $distance = sqrt($sumOfSquare);
        $similarity = 1 / (1 + $distance);
        return $similarity;
    }

    public static function getSimilarityByPearsonCorrelation($person1Id, $person2Id) {
        $sharedEvaluatedDataList = Person::getSharedEvaluatedDataList($person1Id, $person2Id);
        $n = count($sharedEvaluatedDataList);
        if ($n == 0) {
            return 0;
        }

        $sum1 = 0;
        $sum2 = 0;
        $sum1Sq = 0;
        $sum2Sq = 0;
        $pSum = 0;
        foreach ($sharedEvaluatedDataList as $itemId => $itemEvaluate) {
            $sum1 += $itemEvaluate['evaluateByP1'];
            $sum2 += $itemEvaluate['evaluateByP2'];
            $sum1Sq += pow($itemEvaluate['evaluateByP1'], 2);
            $sum2Sq += pow($itemEvaluate['evaluateByP2'], 2);
            $pSum += $itemEvaluate['evaluateByP1'] * $itemEvaluate['evaluateByP2'];
        }
        $num = $pSum - ($sum1*$sum2/$n);
        $den = sqrt(($sum1Sq - pow($sum1, 2) / $n) * ($sum2Sq - pow($sum2, 2) / $n));
        if ($den === 0) {
            return 0;
        }

        $r = $num / $den;
        return $r;
    }

}

// 如果是直接运行
if (!debug_backtrace()) {
    $personLisa = Person::getUserByName('Lisa Rose');
    $personGene = Person::getUserByName('Gene Seymour');

    var_dump(SimilarityCaculator::getSimilarityByEuclideanDistance($personLisa->id, $personGene->id));
    var_dump(SimilarityCaculator::getSimilarityByPearsonCorrelation($personLisa->id, $personGene->id));
}
