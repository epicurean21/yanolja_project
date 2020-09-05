<?php
/*
 * 평일/주말 판단 함수
 */
function getDayType($date){
    $day = date('w', strtotime($date));

    if($day > 0 && $day <5)
        $dayType = 'weekday';
    else
        $dayType = 'weekend';

    return $dayType;
}

/*
 * 비회원용 숙박 가능 숙소 최소금액 출력 함수
 */
function getMinPrice($isMember, $dayType, $motelGroupIdx, $endAt)
{
    /*
     * 회원.평일
     * 회원.주말
     * 비회원.평일
     * 비회원.주말
     */

    if ($isMember == true && $dayType == 'weekday')
        return getMemberMinWeekdayPrice($motelGroupIdx, $endAt);
    else if ($isMember == true && $dayType == 'weekend')
        return getMemberMinWeekendPrice($motelGroupIdx, $endAt);
    else if ($isMember == false && $dayType == 'weekday')
        return getMinWeekdayPrice($motelGroupIdx, $endAt);
    else
        return getMinWeekendPrice($motelGroupIdx, $endAt);

}

/*
 * 지역그룹별 모텔 조회 함수
 */
function getMotels($isMember, $startAt, $endAt, $motelGroupIdx)
{
    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt)) / 86400;

    // 1. 1박인 경우 -> 숙박, 대실 모두 가능
    if($dayDiff == 1){
        //1-1. 숙박 조회
        // 각 숙소 별 최소 가격 가져옴
        $minAllDayPrice = getMinPrice($isMember, $dayType, $motelGroupIdx, $endAt);

        // 회원 여부로 나눔


        //1-2. 대실인 경우


        return ;
    }
    // 2. 연박인 경우 -> 숙박만 가능
    else{
        return;
    }

}
/*
 * 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/비회원/평일/최소가격
 */
function getMinWeekdayPrice($motelGroupIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select AccomIdx, min(WeekdayPrice) as 최소가격
                from (select IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable,
                             Room.AccomIdx,
                             WeekdayPrice
                      from Region
                               join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                               join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
                               join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                               join Room on Accommodation.AccomIdx = Room.AccomIdx
                               join RoomPrice on Accommodation.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                               join AllDayInfo on Room.AccomIdx = AllDayInfo.AccomIdx
                               left join Reservation
                                         on Room.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
                      where AccomType = 'M'
                        and MotelGroup.MotelGroupIdx = ?) as result
                where result.isAvailable = 'Y'
                group by AccomIdx 
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $motelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*
 * 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/비회원/주말/최소가격
 */
function getMinWeekendPrice($motelGroupIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select AccomIdx, min(WeekendPrice) as 최소가격
                from (select IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable,
                             Room.AccomIdx,
                             WeekendPrice
                      from Region
                               join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                               join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
                               join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                               join Room on Accommodation.AccomIdx = Room.AccomIdx
                               join RoomPrice on Accommodation.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                               join AllDayInfo on Room.AccomIdx = AllDayInfo.AccomIdx
                               left join Reservation
                                         on Room.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
                      where AccomType = 'M'
                        and MotelGroup.MotelGroupIdx = ?) as result
                where result.isAvailable = 'Y'
                group by AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $motelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*
 * 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/회원/평일/최소가격
 */
function getMemberMinWeekdayPrice($motelGroupIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select AccomIdx, min(MemberWeekdayPrice) as 최소가격
                from (select IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable,
                             Room.AccomIdx,
                             MemberWeekdayPrice
                      from Region
                               join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                               join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
                               join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                               join Room on Accommodation.AccomIdx = Room.AccomIdx
                               join RoomPrice on Accommodation.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                               join AllDayInfo on Room.AccomIdx = AllDayInfo.AccomIdx
                               left join Reservation
                                         on Room.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
                      where AccomType = 'M'
                        and MotelGroup.MotelGroupIdx = ?) as result
                where result.isAvailable = 'Y'
                group by AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $motelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*
 * 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/회원/주말/최소가격
 */
function getMemberMinWeekendPrice($motelGroupIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select AccomIdx, min(MemberWeekendPrice) as 최소가격
                from (select IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable,
                             Room.AccomIdx,
                             MemberWeekendPrice
                      from Region
                               join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                               join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
                               join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                               join Room on Accommodation.AccomIdx = Room.AccomIdx
                               join RoomPrice on Accommodation.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                               join AllDayInfo on Room.AccomIdx = AllDayInfo.AccomIdx
                               left join Reservation
                                         on Room.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
                      where AccomType = 'M'
                        and MotelGroup.MotelGroupIdx = ?) as result
                where result.isAvailable = 'Y'
                group by AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $motelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*
 * 퇴실일 기준 숙박이 가능한지 체크하는 함수
 */
function isAvailableAllDayReserve($endAt)
{
    $pdo = pdoSqlConnect();
    $query = "select not exists(
            select *
            from Reservation
                     join Accommodation on Accommodation.AccomIdx = Reservation.AccomIdx
            where AccomType = 'M'
              and ReserveType = 'A'
              and CheckInDate < date(?)
              and CheckOutDate > date(?)) as exist;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt,$endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}


//
////READ
//function test()
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM Test;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
////READ
//function testDetail($testNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM Test WHERE no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$testNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//
//function testPost($name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO Test (name) VALUES (?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name]);
//
//    $st = null;
//    $pdo = null;
//
//}
//
//
//function isValidUser($id, $pw){
//    $pdo = pdoSqlConnect();
//    $query = "SELECT EXISTS(SELECT * FROM User WHERE UserId= ? AND UserPwd = ?) AS exist;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$id, $pw]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st=null;$pdo = null;
//
//    return intval($res[0]["exist"]);
//
//}
//
//
//// CREATE
////    function addMaintenance($message){
////        $pdo = pdoSqlConnect();
////        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
////
////        $st = $pdo->prepare($query);
////        $st->execute([$message]);
////
////        $st = null;
////        $pdo = null;
////
////    }
//
//
//// UPDATE
////    function updateMaintenanceStatus($message, $status, $no){
////        $pdo = pdoSqlConnect();
////        $query = "UPDATE MAINTENANCE
////                        SET MESSAGE = ?,
////                            STATUS  = ?
////                        WHERE NO = ?";
////
////        $st = $pdo->prepare($query);
////        $st->execute([$message, $status, $no]);
////        $st = null;
////        $pdo = null;
////    }
//
//// RETURN BOOLEAN
////    function isRedundantEmail($email){
////        $pdo = pdoSqlConnect();
////        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
////
////
////        $st = $pdo->prepare($query);
////        //    $st->execute([$param,$param]);
////        $st->execute([$email]);
////        $st->setFetchMode(PDO::FETCH_ASSOC);
////        $res = $st->fetchAll();
////
////        $st=null;$pdo = null;
////
////        return intval($res[0]["exist"]);
////
////    }
//php