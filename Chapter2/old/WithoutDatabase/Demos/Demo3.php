<?php

require_once '../Classes/Person.php';

$personToby = new Person('Toby');
$topMatches = $personToby->getTopMatches(3);
echo "===Top matches by similarity===", "\n";
foreach ($topMatches as $index => $matchInfo) {
    $order = $index + 1;
    echo "No.{$order}:{$matchInfo[1]},similarity:{$matchInfo[0]}", "\n";
}
//===Top matches by similarity===
//No.1:Lisa Rose,similarity:0.99124070716193
//No.2:Mick LaSalle,similarity:0.9244734516419
//No.3:Claudia Puig,similarity:0.89340514744156