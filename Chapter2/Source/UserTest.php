<?php

namespace Chapter2;

include_once 'User.php';
include_once 'Item.php';

class UserTest extends \PHPUnit_Framework_TestCase {

    public function testGetUserList() {
        $userList = User::getUserList();
//        var_export($userList);
        $this->assertEquals(count($userList), 6);
        $this->assertTrue($userList[0] instanceof User);
    }

    public function testGetSimilarity() {
        $lisa = User::getUserByName('Lisa Rose');
        $gene = User::getUserByName('Gene Seymour');

        $this->assertEquals($lisa->getSimlarity($gene, User::SIMILARITY_METHOD_EUCLIDEAN), 0.29429805508555);
        $this->assertEquals($lisa->getSimlarity($gene, User::SIMILARITY_METHOD_PEARSON), 0.39605901719067);
    }
    
    public function testGetTopMatches(){
        $toby = User::getUserByName('Toby');
//        var_export($toby->getTopMatches());
        $topMatches = $toby->getTopMatches();
        $this->assertEquals($topMatches[0]->similarity, 0.99124070716193);
        $this->assertEquals($topMatches[0]->user, User::getUserByName('Lisa Rose'));
    }
    
    public function testGetCheckedItemList(){
        $toby = User::getUserByName('Toby');
        $checkedItems = $toby->getCheckedItemList();
//        var_export($checkedItems);
        $this->assertEquals(count($checkedItems), 3);
        $this->assertTrue($checkedItems[0] instanceof Item);
    }
    
    public function testGetUnCheckedItemList(){
        $toby = User::getUserByName('Toby');
        $unCheckedItemList = $toby->getUnCheckedItemList();
//        var_export($unCheckedItemList);
        $this->assertEquals(count($unCheckedItemList), 3);
        $this->assertTrue($unCheckedItemList[0] instanceof Item);
    }
    
    public function testGetRecommendationList(){
        $toby = User::getUserByName('Toby');
        $recommendationList = $toby->getRecommandationList();
//        var_export($recommendationList);
        $this->assertEquals($recommendationList[0]->score, 3.3477895267131);
    }

}

?>
