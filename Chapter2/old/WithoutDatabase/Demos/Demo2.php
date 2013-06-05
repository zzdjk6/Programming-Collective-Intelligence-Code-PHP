<?php

require_once '../Classes/Person.php';
require_once '../Classes/SimilarityCaculator.php';

$personLisa = new Person('Lisa Rose');
$personGene = new Person('Gene Seymour');
//$personMichael = new Person('Michael Phillips');
//$personClaudia = new Person('Claudia Puig');
//$personMick = new Person('Mick LaSalle');
//$personJack = new Person('Jack Matthews');
//$personToby = new Person('Toby');

$similarity = SimilarityCaculator::getSimilarityByPearsonCorrelation($personLisa, $personGene);
echo "The similarity between {$personLisa->getName()} and {$personGene->getName()} is {$similarity}";
//The similarity between Lisa Rose and Gene Seymour is 0.39605901719067