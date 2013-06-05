<?php

require_once '../Classes/Person.php';

$personToby = new Person('Toby');
$recommendations= $personToby->getRecommendations();
echo "===Recommendations===", "\n";
$index = 1;
foreach ($recommendations as $itemName => $degree) {
    echo "No.{$index}:{$itemName},degree:{$degree}", "\n";
    $index ++;
}
//Notes: The Book ignored 'Michael Phillips'
//===Recommendations===
//No.1:The Night Listener,degree:3.3477895267131
//No.2:Lady in the Water,degree:2.8325499182642
//No.3:Just My Luck,degree:2.5309807037656