<?php

//CHECK
function keyCheck($key, $arr){
    if(!array_key_exists($key, $arr)){
        $res = (object)array();
        $res->isSuccess = false;
        $res->code = 350;
        $res->message = $key.' 전달 없음';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        exit;
    }
}
function isMember(){
    if(array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER))
        return true;
    else
        return false;
}
function parsingDate($date){

    $year = substr($date, 0,4);
    $month = substr($date, 4,2);
    $day = substr($date, 6,2);

    return $year.'-'.$month.'-'.$day;
}



//READ
function test()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function testDetail($testNo)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$testNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function testPost($name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Test (name) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$name]);

    $st = null;
    $pdo = null;

}


function isValidUser($id, $pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE UserId= ? AND UserPwd = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidUserId($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM yanolja_test.User WHERE UserId= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getMotelList(){
    $pdo = pdoSqlConnect();
    $query = "  
                select CityName, RegionGroupName, RegionName
                from RegionGroupName
                        join RegionGroup on RegionGroupName.RegionGroupIdx = RegionGroup.RegionGroupIdx
                        join City on City.RegionGroupIdx = RegionGroupName.RegionGroupIdx;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return $res;
}


//CREATE
function createUser($UserId, $UserPwd, $UserName, $UserBirth, $UserContact, $UserGender){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO yanolja_test.User (UserId, UserPwd, UserName, UserBirth, UserContact, UserGender,
                               UserPoint, CreatedAt, UpdatedAt, isDeleted)
VALUES (?, ?, ?, ?, ?, ?, default, default, default, default)";

    $st = $pdo->prepare($query);
    $st->execute([$UserId, $UserPwd, $UserName, $UserBirth, $UserContact, $UserGender]);

    $st = null;
    $pdo = null;

}


// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
