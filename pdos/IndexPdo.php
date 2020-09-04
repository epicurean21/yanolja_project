<?php

//CHECK
function keyCheck($key, $arr)
{
    if (!array_key_exists($key, $arr)) {
        $res = (object)array();
        $res->isSuccess = false;
        $res->code = 350;
        $res->message = $key . ' 전달 없음';
        echo json_encode($res, JSON_NUMERIC_CHECK);
        exit;
    }
}
function isMember()
{
    if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER))
        return true;
    else
        return false;
}
function parsingDate($date)
{

    $year = substr($date, 0, 4);
    $month = substr($date, 4, 2);
    $day = substr($date, 6, 2);

    return $year . '-' . $month . '-' . $day;
}
function checkAllDayResrveWithCheckOutDate($endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
            select CheckInDate                   as 체크인,
       CheckOutDate                  as 체크아웃,
       MotelGroup.MotelGroupIdx      as 지역,
       MotelGroupName.MotelGroupName as 지역이름,
       Accommodation.AccomIdx        as 숙소,
       AccomName                     as 숙소이름,
       MotelRoom.RoomIdx             as 방,
       RoomName                      as 방이름

from Region
         join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
         join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
         join PartTimeInfo on Accommodation.AccomIdx = PartTimeInfo.AccomIdx
         join AllDayInfo on Accommodation.AccomIdx = AllDayInfo.AccomIdx
         join MotelRoom on Accommodation.AccomIdx = MotelRoom.AccomIdx
         join PartTimePrice on MotelRoom.AccomIdx = PartTimePrice.AccomIdx and MotelRoom.RoomIdx = PartTimePrice.RoomIdx
         join AllDayPrice on MotelRoom.AccomIdx = AllDayPrice.AccomIdx and MotelRoom.RoomIdx = AllDayPrice.RoomIdx
         join Reservation on Accommodation.AccomIdx = Reservation.AccomIdx and MotelRoom.RoomIdx = Reservation.RoomIdx
where AccomType = 'M'
  and ReserveType = 'A'
  and CheckInDate < date(?)
  and CheckOutDate > date(?)
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt,$endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
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


function isValidUser($id, $pw)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE UserId= ? AND UserPwd = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

function isValidUserId($id)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM yanolja_test.User WHERE UserId= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getMotels($startAt, $endAt)
{
    // 1. 1박인 경우 -> 대실 숙박 모두 가능
    if(){

    }
    // 2. 연박인 경우 -> 숙박만 가능
    else{

    }
    return;
}


function getMotelGroupList()
{
    $pdo = pdoSqlConnect();
    $query = "  
                select cityIdx, cityName, MotelGroupName.MotelGroupIdx, MotelGroupName, Region.RegionIdx, RegionName
from Region join MotelGroup on MotelGroup.RegionIdx = Region.RegionIdx
join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


//CREATE
function createUser($UserId, $UserPwd, $UserName, $UserBirth, $UserContact, $UserGender)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO yanolja_test.User (UserId, UserPwd, UserName, UserBirth, UserContact, UserGender,
                               UserPoint, CreatedAt, UpdatedAt, isDeleted)
VALUES (?, ?, ?, ?, ?, ?, default, default, default, default)";

    $st = $pdo->prepare($query);
    $st->execute([$UserId, $UserPwd, $UserName, $UserBirth, $UserContact, $UserGender]);

    $st = null;
    $pdo = null;

}

function myYanolja($UserId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    UserName,
	UserCoupon.UserIdx,
    User.UserPoint,
    COUNT(UserCoupon.CouponIdx) as CouponCount
FROM
    User
        JOIN
    (UserCoupon
    JOIN Coupon USING (CouponIdx)) ON (User.UserIdx = UserCoupon.UserIdx)
WHERE
    DATE(Coupon.EndDate) >= DATE(NOW())
        AND Coupon.isDeleted = 'N'
	AND User.UserId = ?
GROUP BY UserIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidPwd($UserId, $UserPwd)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (
select UserID from User 
where UserId = ?
and UserPwd = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$UserId, $UserPwd]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


function getUserInfo($UserId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT UserName, UserId, UserPwd, UserContact
    FROM User WHERE userId = ?";

    $st = $pdo->prepare($query);
    $st->execute([$UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserReservation($UserId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT count(ReserveIdx) as DomesticAccommodation
FROM 
	User Join Reservation using (UserIdx)
WHERE
	User.UserId = ?
    AND TIMESTAMPDIFF(Month, Reservation.createdAt, NOW()) < 3;";

    $st = $pdo->prepare($query);
    $st->execute([$UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidName($UserName)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS( SELECT UserName from User where UserName = ?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$UserName]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function patchUserName($UserId, $UserName){
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET UserName = ? 
WHERE (UserId = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$UserName, $UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function patchUserPwd($UserId, $UserPwd){
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET UserPwd = ? 
WHERE (UserId = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$UserPwd, $UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

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
