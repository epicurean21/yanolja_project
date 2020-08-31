<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "13.125.205.231";
        $DB_NAME = "yanolja_test";
        $DB_USER = "woodie";
        $DB_PW = "0121";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}