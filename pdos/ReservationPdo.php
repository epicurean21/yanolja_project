<?php

function getUserContact($UserId) {
    $pdo = pdoSqlConnect();
    $query = "SELECT UserContact
FROM User
WHERE UserId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$UserId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserCouponMotel($UserIdx, $CheckInDate, $CheckOutDate) {
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    UserIdx,
    UserCoupon.CouponIdx,
    AccomType,
    CouponName,
    DiscountPrice
FROM
    UserCoupon
        JOIN
    Coupon USING (CouponIdx)
WHERE
    UserIdx = ?
    AND UserCoupon.isDeleted = 'N'
    AND Coupon.isDeleted = 'N'
    AND AccomType = 'M'
    AND ReserveType = 'A'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1) 
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CheckOutDate, $CheckInDate]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getUserCouponCountMotel($UserIdx, $CheckInDate, $CheckOutDate) {
    $pdo = pdoSqlConnect();
    $query = "SELECT 
       count(UserCoupon.CouponIdx) as CouponCount
FROM
    UserCoupon
        JOIN
    Coupon USING (CouponIdx)
WHERE
    UserIdx = ?
    AND UserCoupon.isDeleted = 'N'
    AND Coupon.isDeleted = 'N'
    AND AccomType = 'M'
    AND ReserveType = 'A'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1) 
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CheckOutDate, $CheckInDate]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserCouponMotelPartTime($UserIdx, $CheckInDate, $CheckOutDate) {
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    UserIdx,
    UserCoupon.CouponIdx,
    AccomType,
    CouponName,
    DiscountPrice
FROM
    UserCoupon
        JOIN
    Coupon USING (CouponIdx)
WHERE
    UserIdx = ?
    AND UserCoupon.isDeleted = 'N'
    AND Coupon.isDeleted = 'N'
    AND AccomType = 'M'
    AND ReserveType = 'P'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1) 
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CheckOutDate, $CheckInDate]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getUserCouponCountMotelPartTime($UserIdx, $CheckInDate, $CheckOutDate) {
    $pdo = pdoSqlConnect();
    $query = "SELECT 
       count(UserCoupon.CouponIdx) as CouponCount
FROM
    UserCoupon
        JOIN
    Coupon USING (CouponIdx)
WHERE
    UserIdx = ?
    AND UserCoupon.isDeleted = 'N'
    AND Coupon.isDeleted = 'N'
    AND AccomType = 'M'
    AND ReserveType = 'P'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1) 
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CheckOutDate, $CheckInDate]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserCouponHotel($UserIdx, $CheckInDate, $CheckOutDate) {
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    UserIdx,
    UserCoupon.CouponIdx,
    AccomType,
    CouponName,
    DiscountPrice
FROM
    UserCoupon
        JOIN
    Coupon USING (CouponIdx)
WHERE
    UserIdx = ?
    AND UserCoupon.isDeleted = 'N'
    AND Coupon.isDeleted = 'N'
    AND AccomType = 'H'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1) 
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CheckOutDate, $CheckInDate]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getUserCouponCountHotel($UserIdx, $CheckInDate, $CheckOutDate) {
    $pdo = pdoSqlConnect();
    $query = "SELECT 
       count(UserCoupon.CouponIdx) as CouponCount
FROM
    UserCoupon
        JOIN
    Coupon USING (CouponIdx)
WHERE
    UserIdx = ?
    AND UserCoupon.isDeleted = 'N'
    AND Coupon.isDeleted = 'N'
    AND AccomType = 'H'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1) 
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CheckOutDate, $CheckInDate]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function newReservation($UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
                        $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
                        $Transportation, $UserPointUsed, $CouponIdx, $FinalCost)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Reservation(UserIdx, AccomIdx, RoomIdx, ReserveType,
	CheckInDate, CheckOutDate, ReserveName, ReserveContact, VisiterName, VisiterContact,
    Transportation, PointUsed, CouponUsedIdx, FinalCost) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
        $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
        $Transportation, $UserPointUsed, $CouponIdx, $FinalCost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function PostNewReservationP($UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
                            $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
                            $Transportation, $UserPointUsed, $CouponIdx, $FinalCost, $NewPoint ,$isPointUsed, $isCouponUsed, $startAt, $endAt) {

    $pdo = pdoSqlConnect();

    try {
        $pdo->beginTransaction();

        $query = "select exists(select *
                from Reservation
                where AccomIdx = ?
                and RoomIdx = ?
                and ReserveType = 'P'
                and CheckInDate > date(?)
                and CheckOutDate < date(?)) as exist;";
        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);

        $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        if($res[0]['exist'] == 1) {     // 이미 예약자 존재
            throw new Exception("이미 예약이 가득찬 방입니다");
        }

        $query = "INSERT INTO Reservation(UserIdx, AccomIdx, RoomIdx, ReserveType,
	CheckInDate, CheckOutDate, ReserveName, ReserveContact, VisiterName, VisiterContact,
    Transportation, PointUsed, CouponUsedIdx, FinalCost) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $st = $pdo->prepare($query);
        $st->execute([$UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
            $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
            $Transportation, $UserPointUsed, $CouponIdx, $FinalCost]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        if($isPointUsed == true) {
            $query = "UPDATE User SET UserPoint = ? WHERE (UserIdx = ?);";
            $st = $pdo->prepare($query);
            $st->execute([$NewPoint, $UserIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
        }

        if($isCouponUsed == true) {
            $query = "UPDATE UserCoupon SET isDeleted = 'Y' WHERE (CouponIdx = ?) and (UserIdx = ?);";
            $st = $pdo->prepare($query);
            $st->execute([$CouponIdx, $UserIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
        }
        $query = "select exists(select *
                from Reservation
                where ReserveType = 'A'
                      and AccomIdx = ?
                and RoomIdx = ?
                and CheckInDate < ?
                and CheckOutDate > ?) as exist;";

        $st = $pdo->prepare($query);
        $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        if($res[0]['exist'] == 1) {
            throw new Exception("이미 예약이 가득찬 방입니다");
        }


        $pdo->commit();
    } catch(\Exception $e) {
        $pdo->rollBack();
        $res = (Object)Array();
        $res->IsSuccess = FALSE;
        $res->code = 406;
        $res->Message = "예약에 실패하였습니다.";
        return $res;
    }

    $st = null;
    $pdo = null;
    $res = (Object)Array();
    $res->IsSuccess = TRUE;
    $res->code = 200;
    $res->Message = "대실 예약에 성공하였습니다";
    return $res;
}

function PostNewReservationA($UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
                             $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
                             $Transportation, $UserPointUsed, $CouponIdx, $FinalCost, $NewPoint ,$isPointUsed, $isCouponUsed, $startAt, $endAt) {

    $pdo = pdoSqlConnect();
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    try {
        $pdo->beginTransaction();
        if(!checkAllday($pdo, $AccomIdx, $RoomIdx, $startAt, $dayDiff)) {
            throw  new Exception("이미 예약된 방입니다.");
        }

        $query = "INSERT INTO Reservation(UserIdx, AccomIdx, RoomIdx, ReserveType,
	CheckInDate, CheckOutDate, ReserveName, ReserveContact, VisiterName, VisiterContact,
    Transportation, PointUsed, CouponUsedIdx, FinalCost) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

        $st = $pdo->prepare($query);
        $st->execute([$UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
            $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
            $Transportation, $UserPointUsed, $CouponIdx, $FinalCost]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        if($isPointUsed == true) {
            $query = "UPDATE User SET UserPoint = ? WHERE (UserIdx = ?);";
            $st = $pdo->prepare($query);
            $st->execute([$NewPoint, $UserIdx]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
        }

        if($isCouponUsed == true) {
            $query = "UPDATE UserCoupon SET isDeleted = 'Y' WHERE (CouponIdx = ?) and (UserIdx = ?);";
            $st = $pdo->prepare($query);
            $st->execute([$CouponIdx, $UserIdx]);

            $st->setFetchMode(PDO::FETCH_ASSOC);
        }

        $pdo->commit();
    } catch(\Exception $e) {
        $pdo->rollBack();
        $res = (Object)Array();
        $res->IsSuccess = FALSE;
        $res->code = 406;
        $res->Message = "예약에 실패하였습니다.";
        return $res;
    }

    $st = null;
    $pdo = null;
    $res = (Object)Array();
    $res->IsSuccess = TRUE;
    $res->code = 200;
    $res->Message = "숙박 예약에 성공하였습니다";
    return $res;
}


function patchUserPoint($UserIdx, $UserPoint) {
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET UserPoint = ? WHERE (UserIdx = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$UserPoint, $UserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function patchCouponUsed($UserIdx, $CouponIdx) {
    $pdo = pdoSqlConnect();
    $query = "UPDATE UserCoupon SET isDeleted = 'Y' WHERE (CouponIdx = ?) and (UserIdx = ?);";

    $st = $pdo->prepare($query);
    $st->execute([$CouponIdx, $UserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function transactionStart() {
    $pdo = pdoSqlConnect();
    $query = "set autocommit = FALSE;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function transactionEnd() {
    $pdo = pdoSqlConnect();
    $query = "set autocommit = TRUE;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function commit() {
    $pdo = pdoSqlConnect();
    $query = "commit;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function rollback() {
    $pdo = pdoSqlConnect();
    $query = "rollback;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function getAutoCommit() {
    $pdo = pdoSqlConnect();
    $query = "SELECT @@AUTOCOMMIT as autocommit;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['autocommit'];
}


function getAccomType($AccomIdx) {
    $pdo = pdoSqlConnect();
    $query = "SELECT AccomType From Accommodation WHERE AccomIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['AccomType'];
}

function getUserPoint($UserIdx) {
    $pdo = pdoSqlConnect();
    $query = "SELECT UserPoint From User WHERE UserIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['UserPoint'];
}

function isValidCoupon($CouponIdx, $UserIdx, $CheckInDate, $CheckOutDate, $ReserveType) {
    $pdo = pdoSqlConnect();
    $query = "select exists (
        select * from UserCoupon join Coupon using (CouponIdx)
where UserIdx = ? and UserCoupon.CouponIdx = ?
    AND UserCoupon.isDeleted= 'N'
    AND (timestampdiff(Day, ? , Coupon.EndDate) >= 1)
    AND (timestampdiff(DAY, Coupon.StartingDate, ?) >= 1)     
    AND Coupon.ReserveType = ?
) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $CouponIdx, $CheckOutDate, $CheckInDate, $ReserveType]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function isCouponExists($CouponIdx, $ReserveType) {
    $pdo = pdoSqlConnect();
    $query = "select exists (select * from Coupon Where CouponIdx = ?
 AND Coupon.isDeleted ='N'
 AND Coupon.ReserveType = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$CouponIdx, $ReserveType]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function checkAllday($pdo, $AccomIdx, $RoomIdx, $startAt, $dayDiff){

    // 9 ~ 12 연박 가정하면

    $afterStartAt = date("Y-m-d", strtotime($startAt." +1 day")); // 10;


    for($i=0; $i < $dayDiff - 1 ; $i++){ // 2번 돌고

        // 1. 겹치는 숙박이 없어야 한다.
        $tmp_startAt = date("Y-m-d", strtotime($afterStartAt." +".$i."day")); // 10, 11
        $tmp_endAt = date("Y-m-d", strtotime($tmp_startAt." +".$i."day")); // 11, 12

        if(!getAlldayCheck($pdo, $AccomIdx, $RoomIdx, $tmp_endAt)){ // 11,12 숙박 검사
            // 예약이 있으면
            return false;
        }

        // 2. 중간 날짜에 대실이 없어야 한다. => 10~11 , 11~12 대실 X
        if( !empty(getPartTimeCheck($pdo, $AccomIdx, $RoomIdx, $tmp_startAt, $tmp_endAt)) ){

            // 중간 날짜들의 대실 정보를 불러올 수 있으면
            return false;
        }
    }

    if(!getAlldayCheck($pdo, $AccomIdx, $RoomIdx, $afterStartAt))    // 10일에 숙박이 있으면
        return false;

    return true;

}

function getPartTimeCheck($pdo, $AccomIdx, $RoomIdx, $startAt, $endAt)
{
    $query = "select time(CheckInDate) as CheckIn, time(CheckOutDate) as CheckOut 
                from Reservation
                where ReserveType = 'P'
                and      AccomIdx = ?
                and RoomIdx = ?
                and CheckInDate > date(?)
                and CheckOutDate < date(?)";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res;
}

function getAlldayCheck($pdo, $AccomIdx, $RoomIdx, $endAt){
    $query = "
                select not exists(select *
                  from Reservation
                  where AccomIdx = ?
                    and RoomIdx = ?
                    and ReserveType = 'A'
                    and CheckInDate < date(?)
                    and CheckOutDate > date(?)) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx, $endAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]['exist'];
}