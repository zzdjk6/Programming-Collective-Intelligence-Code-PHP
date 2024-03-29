<?php

class Critics {

    //A dictionary of movie critics and their ratings of a small set of movies
    protected static $critics = array(
        'Lisa Rose' => array(
            'Lady in the Water' => 2.5,
            'Snakes on a Plane' => 3.5,
            'Just My Luck' => 3.0,
            'Superman Returns' => 3.5,
            'You, Me and Dupree' => 2.5,
            'The Night Listener' => 3.0
        ),
        'Gene Seymour' => array(
            'Lady in the Water' => 3.0,
            'Snakes on a Plane' => 3.5,
            'Just My Luck' => 1.5,
            'Superman Returns' => 5.0,
            'The Night Listener' => 3.0,
            'You, Me and Dupree' => 3.5
        ),
//        'Michael Phillips' => array(
//            'Lady in the Water' => 2.5,
//            'Snakes on a Plane' => 3.0,
//            'Superman Returns' => 3.5,
//            'The Night Listener' => 4.0
//        ),
        'Claudia Puig' => array(
            'Snakes on a Plane' => 3.5,
            'Just My Luck' => 3.0,
            'The Night Listener' => 4.5,
            'Superman Returns' => 4.0,
            'You, Me and Dupree' => 2.5
        ),
        'Mick LaSalle' => array(
            'Lady in the Water' => 3.0,
            'Snakes on a Plane' => 4.0,
            'Just My Luck' => 2.0,
            'Superman Returns' => 3.0,
            'The Night Listener' => 3.0,
            'You, Me and Dupree' => 2.0
        ),
        'Jack Matthews' => array(
            'Lady in the Water' => 3.0,
            'Snakes on a Plane' => 4.0,
            'The Night Listener' => 3.0,
            'Superman Returns' => 5.0,
            'You, Me and Dupree' => 3.5
        ),
        'Toby' => array(
            'Snakes on a Plane' => 4.5,
            'You, Me and Dupree' => 1.0,
            'Superman Returns' => 4.0
        )
    );
    protected static $itemsData;

    public static function getItemsData() {
        if(self::$itemsData){
            return self::$itemsData;
        }
        
        $itemsData = array();
        foreach (self::$critics as $personName => $itemEvaluateData) {
            foreach ($itemEvaluateData as $itemName => $itemEvaluate) {
                $itemsData[$itemName][$personName] = $itemEvaluate;
            }
        }
        
        self::$itemsData = $itemsData;
        return $itemsData;
    }

    public static function getCritcsData($personName) {
        return self::$critics[$personName];
    }

    public static function getItemEvaluateData($itemName) {
        $itemsData = self::getItemsData();
        return self::$itemsData[$itemName];
    }

}

// direct excute
if (!debug_backtrace()) {
    var_dump(Critics::getItemEvaluateData('The Night Listener'));
}