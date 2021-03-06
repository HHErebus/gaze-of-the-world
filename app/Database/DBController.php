<?php

namespace ChristianFratta\GazeOfTheWorld\Database;

use ChristianFratta\GazeOfTheWorld\App;
use PDO;

class DBController
{
    /**
     * @var  PDO  pdo
     */
    private static $pdo;

    public static function saveToDatabase(array $data)
    {
        if(self::$pdo == null) {
            self::$pdo = self::connect();
        }

        $countryCodes = array_keys($data);
        $statement = self::createInsertStatement($countryCodes);
        $prepared = self::$pdo->prepare($statement);

        // Add date in front of the array to complete the dataset.
        $today = date('Y-m-d');
        $completeData = ["date" => $today] + $data;

        $prepared->execute($completeData);
    }

    public static function getByDate($date)
    {
        if(self::$pdo == null) {
            self::$pdo = self::connect();
        }

        $statement = self::createSelectStatement('date=:date');
        $prepared = self::$pdo->prepare($statement);
        $prepared->bindValue(':date', $date);
        $prepared->execute();
        return $prepared->fetchAll();
    }

    public static function connect()
    {
        App::loadEnv();

        $host = getenv('DB_HOST');
        $db = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        return new PDO($dsn, $user, $pass, $opt);
    }

    private static function createInsertStatement(array $data)
    {
        $statement = 'INSERT INTO news_data';
        // (`date`, ... `X`, `Y`, `Z`);
        $keys = '(`date`, `' . implode('`,`', $data) . '`)';
        // VALUES ('date', ... ':X', ':Y', ':Z');
        $values = 'VALUES (:date,:' . implode(',:', $data) . ')';

        return $statement . $keys . $values;
    }

    private static function createSelectStatement(string $where)
    {
        return "SELECT * FROM news_data WHERE $where";
    }
}