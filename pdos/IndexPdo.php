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
function parsingDate($date)
{

    $year = substr($date, 0, 4);
    $month = substr($date, 4, 2);
    $day = substr($date, 6, 2);

    return $year . '-' . $month . '-' . $day;
}
function getRandomNickname(){
    while(true) {
        $nicknameArr1 = array('족제비', '너구리', '미어캣', '하마', '코끼리', '사자', '호랑이', '토끼', '사슴', '고양이');
        $nicknameArr2 = array('행복한', '배고픈', '신나는', '흥부자', '슬픔이', '하늘', '바다', '바람', '구름', '햇님');
        $randNum1 = rand(0,9);
        $randNum2 = rand(0,9);

        $nickname = (string)$nicknameArr2[$randNum1].$nicknameArr1[$randNum2];

        // 닉네임이 존재 안 하면 리턴.
        if(!isValidUserName($nickname))
            return $nickname;
    }
}


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
    $query = "SELECT EXISTS(SELECT * FROM User WHERE UserId= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isValidUserName($nickname)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE UserName = ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$nickname]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getMotelGroupList()
{
    $pdo = pdoSqlConnect();
    $query = "  
                select distinct cityIdx, cityName, MotelGroupName.MotelGroupIdx, MotelGroupName
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

function createUser($UserId, $UserPwd, $UserContact)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (UserId, UserPwd, UserName, UserBirth, UserContact, UserGender,
                               UserPoint, CreatedAt, UpdatedAt, isDeleted)
VALUES (?, ?, ?, ?, ?, ?, default, default, default, default)";

    $st = $pdo->prepare($query);
    $st->execute([$UserId, $UserPwd, getRandomNickname(), '1990-01-01', $UserContact, 'M']);

    $st = null;
    $pdo = null;

}



function myYanolja($UserId, $UserIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    UserName,
    User.UserIdx,
    User.UserPoint,
    CASE 
		WHEN
			(SELECT EXISTS(
					SELECT *     
					FROM UserCoupon join Coupon Using (CouponIdx)
					WHERE UserCoupon.UserIdx = ?) = 1)
		THEN
			(SELECT
				COUNT(UserCoupon.CouponIdx)
			FROM
				UserCoupon Join Coupon using (CouponIdx)
			WHERE
				DATE(Coupon.EndDate) >= DATE(NOW())
				AND Coupon.isDeleted = 'N'
				AND UserCoupon.UserIdx = ?)
		ELSE
			0
	END as CouponCount
FROM
    User
        JOIN
    (UserCoupon
    JOIN Coupon USING (CouponIdx))
WHERE
	User.UserId = ?
    Limit 1;";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $UserIdx, $UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserIdx($UserId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT UserIdx FROM User Where UserId = ?";

    $st = $pdo->prepare($query);
    $st->execute([$UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['UserIdx'];
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

function isValidMotel($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (
select AccomIdx from Accommodation 
where AccomIdx = ? AND AccomType = 'M') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidHotel($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (
select AccomIdx from Accommodation 
where AccomIdx = ? AND AccomType = 'H') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidAccom($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists (
select AccomIdx from Accommodation 
where AccomIdx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
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
    FROM User WHERE UserId = ?";

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
