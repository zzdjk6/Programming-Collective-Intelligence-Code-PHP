<?php

namespace Chapter2;

class Database {

    const PDO_CONNECTION_STRING = 'sqlite:./data.db3';

    protected static $pdo;
    //书中的数据
    protected static $data = array(
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
//        书中忽略了这个人
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

    /**
     * 
     * @return \PDO
     */
    public static function getPdo() {
        if (!self::$pdo) {
            self::$pdo = new \PDO(self::PDO_CONNECTION_STRING);
        }
        return self::$pdo;
    }

    public static function buildRawData() {
        $pdo = new \PDO(self::PDO_CONNECTION_STRING);

        $pdo->exec("DROP TABLE IF EXISTS criticisms");
        $pdo->exec("DROP TABLE IF EXISTS movies");
        $pdo->exec("DROP TABLE IF EXISTS users");

        $pdo->exec("
            CREATE TABLE [criticisms] (
            [id] INTEGER PRIMARY KEY AUTOINCREMENT, 
            [user_id] INTEGER, 
            [movie_id] INTEGER, 
            [score] NUMERIC);");
        $pdo->exec("
            CREATE TABLE [movies] (
            [id] INTEGER PRIMARY KEY AUTOINCREMENT, 
            [name] VARCHAR (64));");
        $pdo->exec("
            CREATE TABLE [users] (
            [id] INTEGER PRIMARY KEY AUTOINCREMENT, 
            [name] VARCHAR (64));");

        $statement = $pdo->prepare("INSERT INTO users(name) VALUES(:name)");
        foreach (self::$data as $userName => $criticisms) {
            $statement->bindParam(":name", $userName);
            $statement->execute();
        }

        $statement = $pdo->prepare("INSERT INTO movies(name) VALUES(:name)");
        foreach (self::$data['Lisa Rose'] as $movieName => $score) {
            $statement->bindParam(":name", $movieName);
            $statement->execute();
        }

        $statement = $pdo->prepare("
            INSERT INTO criticisms(user_id,movie_id,score) 
            VALUES(:user_id,:movie_id,:score)");
        foreach (self::$data as $userName => $criticisms) {
            $userID = $pdo
                    ->query("SELECT id FROM users WHERE name='{$userName}'")
                    ->fetchColumn();
            foreach ($criticisms as $movieName => $score) {
                $movieID = $pdo
                        ->query("SELECT id FROM movies WHERE name='{$movieName}'")
                        ->fetchColumn();
                $statement->bindParam(":user_id", $userID);
                $statement->bindParam(":movie_id", $movieID);
                $statement->bindParam(":score", $score);
                $statement->execute();
            }
        }
    }

}

// 如果是直接运行
if (!debug_backtrace()) {
    Database::buildRawData();
}
