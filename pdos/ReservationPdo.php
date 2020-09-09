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
	CheckInDate, CheckOutDate, ReserveName, ReserveContact, 
    Transportation, PointUsed, CouponUsedIdx, FinalCost) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
        $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
        $Transportation, $UserPointUsed, $CouponIdx, $FinalCost]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}
function transactionStart() {
    $pdo = pdoSqlConnect();
    $query = "set autocommit = 0;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st = null;
    $pdo = null;
}

function transactionEnd() {
    $pdo = pdoSqlConnect();
    $query = "set autocommit = 1;";

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