<?php
/*
 * 평일/주말 판단 함수
 */
function getDayType($date)
{
    $day = date('w', strtotime($date));

    if ($day > 0 && $day < 5)
        $dayType = 'weekday';
    else
        $dayType = 'weekend';

    return $dayType;
}

// 특정 숙소의 숙박 최소 금액 출력
function getMinAllDayPrice($isMember, $dayType, $AccomIndex, $endAt)
{
    /*
     * 회원.평일
     * 회원.주말
     * 비회원.평일
     * 비회원.주말
     */

    if ($isMember == true && $dayType == 'weekday')
        return getMemberMinWeekdayPrice($AccomIndex, $endAt);
    else if ($isMember == true && $dayType == 'weekend')
        return getMemberMinWeekendPrice($AccomIndex, $endAt);
    else if ($isMember == false && $dayType == 'weekday')
        return getMinWeekdayPrice($AccomIndex, $endAt);
    else
        return getMinWeekendPrice($AccomIndex, $endAt);

}

// 특정 숙소의 회원/비회원, 평일/주말 대실 이용시간 출력함수
function getPartTimeInfo($isMember, $dayType, $AccomIdx)
{
    /*
     * 회원.평일
     * 회원.주말
     * 비회원.평일
     * 비회원.주말
     */
    if ($isMember == true && $dayType == 'weekday')
        return getMemberWeekdayPartTime($AccomIdx);
    else if ($isMember == true && $dayType == 'weekend')
        return getMemberWeekendPartTime($AccomIdx);
    else if ($isMember == false && $dayType == 'weekday')
        return getWeekdayPartTime($AccomIdx);
    else
        return getWeekendPartTime($AccomIdx);
}

///////////////////////////////////////////////////////////////////
// 특정 지역 그룹의 모텔 수 가져오기
//function getNumOfMotel($motelGroupIdx){
//    $pdo = pdoSqlConnect();
//    $query = "
//                select count(AccomIdx) as num
//                from Region
//                         join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
//                         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
//                where AccomType = 'M'
//                  and MotelGroup.MotelGroupIdx = ?;
//    ";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$motelGroupIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0]['num'];
//}

// 특정 그룹의 검색 인원에 맞는 모든 모텔의 방을 다 불러온다.
function getMotelRoomList($motelGroupIdx, $adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select Accommodation.AccomIdx, RoomIdx
                from Region join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                join MotelGroup on MotelGroup.RegionIdx = Accommodation.RegionIdx
                join Room on Room.AccomIdx = Accommodation.AccomIdx
                where MotelGroupIdx = ?
                  and AccomType = 'M'
                and ? + ? <= MaxCapacity;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$motelGroupIdx, $adult, $child]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 특정 방의 해당 날짜에 숙박이 가능한지 체크한다.
function checkPartTimeReserve($AccomIdx, $RoomIdx, $startAt, $endAt){
    $pdo = pdoSqlConnect();
    $query = "
                select not exists(select *
                from Reservation
                where AccomIdx = ?
                and RoomIdx = ?
                and CheckInDate > date(?)
                and CheckOutDate < date(?)) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// 특정 방의 대실 예약의 체크인, 체크 아웃 시간을 가져온다.
function getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $startAt, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select time(CheckInDate) as CheckIn, time(CheckOutDate) as CheckOut 
                from Reservation
                where AccomIdx = ?
                and RoomIdx = ?
                and CheckInDate > date(?)
                and CheckOutDate < date(?)
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getMotels($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child)
{

    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

//    // 해당 그룹의 모텔의 수 구하기
//    $numOfMotel = getNumOfMotel($motelGroupIdx);

    // 해당 지역 그룹의 모든 방 다 가져오기
    $motelRoomlist = getMotelRoomList($motelGroupIdx, $adult, $child);

    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < count($motelRoomlist); $i++) {

        $nowAccomIdx = $motelRoomlist[$i]['AccomIdx'];
        $nowRoomIdx = $motelRoomlist[$i]['RoomIdx'];

        // 1. 1박인 경우 => 숙박 + 대실
        if ($dayDiff == 1) {
            // 해당 객실의 대실이 가능하다면

            if (checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)) {
                $motelRoomlist[$i]['IsPartTimeAvailable'] = 'T';
                $motelRoomlist[$i]['PartTimeReserveCheckIn'] = '';
                $motelRoomlist[$i]['PartTimeReserveCheckOut'] = '';
            } else {// 해당 객실의 대실이 불가하다면
                $motelRoomlist[$i]['IsPartTimeAvailable'] = 'F';
                $motelRoomlist[$i]['PartTimeReserveCheckIn'] = getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)['CheckIn'];
                $motelRoomlist[$i]['PartTimeReserveCheckOut'] = getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)['CheckOut'];

            }


            //
        } // 2. 연박인 경우
        else {

        }


        $motelRoomlist[$i]['AvgRating'] = getAccomInfo($nowAccomIdx)['avgRating'];
        $motelRoomlist[$i]['AccomName'] = getAccomInfo($nowAccomIdx)['numOfReview'];
        $motelRoomlist[$i]['AccomTag'] = getAccomTag($nowAccomIdx);
        $motelRoomlist[$i]['NumOfUserPick'] = getUserPick($nowAccomIdx);

    }
    return $motelRoomlist;
}

// 지역그룹별 모텔 조회 함수
//function getMotels($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child)
//{
//    // 평일,주말 판단
//    $dayType = getDayType($startAt);
//
//    // 숙박 이용 날짜 차이 구하기
//    $dayDiff = (strtotime($endAt) - strtotime($startAt)) / 86400;
//
//    // 해당 지역의 모든 모텔 불러오기
//    $list = getMotelList($motelGroupIdx);
//
//    // 1. 1박인 경우 -> 숙박, 대실 모두 가능
//    if($dayDiff == 1){
//
//        for($i = 0; $i < count($list); $i++){
//
//            // 숙소 정보 추가
//            $list[$i]['AccomName'] = getAccomInfo($list[$i]['AccomIdx'])['AccomName'];
//            $list[$i]['AvgRating'] = getAccomInfo($list[$i]['AccomIdx'])['AvgRating'];
//            $list[$i]['NumOfReview'] = getAccomInfo($list[$i]['AccomIdx'])['NumOfReview'];
//            $list[$i]['AccomThumbnailUrl'] = getAccomInfo($list[$i]['AccomIdx'])['AccomThumbnailUrl'];
//            $list[$i]['AccomTag'] = getAccomTag($list[$i]['AccomIdx']);
//            $list[$i]['NumOfUserPick'] = getUserPick($list[$i]['AccomIdx']);
//
//            // 숙박 가능 여부 체크 및 최소 금액 추가
//            if( isAvailableAllDay($list[$i]['AccomIdx'],$endAt)){
//                $list[$i]['isAvailableAllDay'] = 'T';
//                $list[$i]['minAllDayPrice'] = getMinAllDayPrice($isMember, $dayType, $list[$i]['AccomIdx'], $endAt)['minPrice'];
//
//                // 숙박 가능한 최소 금액 객실의 당일 대실이 없다면
//                if(strcmp(checkPartTimeReservation($list[$i]['AccomIdx'], getMinAllDayPrice($isMember, $dayType, $list[$i]['AccomIdx'], $endAt)['RoomIdx'],$startAt, $endAt), 'empty') == 0){
//                    // 숙박 가능, 대실이 없는 경우
//                    $list[$i]['AllDayCheckInHour'] = getAllDayHour($list[$i]['AccomIdx'], $dayType);
//                }
//                else{
//                    // 숙박 가능한 숙소의 최소 가격 객실의 당일 숙박이 있다면, 숙박
//                    $PartTimeCheckOutTime =  checkPartTimeReservation($list[$i]['AccomIdx'], getMinAllDayPrice($isMember, $dayType, $list[$i]['AccomIdx'], $endAt)['RoomIdx'],$startAt, $endAt);
//                    $AllDayCheckInTime = getAllDayHour($list[$i]['AccomIdx'], $dayType);
//                    // 대실 퇴실 시간이 숙박 기준 입실 시간보다 빠르다면
//                    if($PartTimeCheckOutTime < $AllDayCheckInTime)
//                        $list[$i]['AllDayCheckInHour'] = $AllDayCheckInTime;
//                    else
//                        $list[$i]['AllDayCheckInHour'] = $PartTimeCheckOutTime;
//                }
//
//
//
//            }
//            else{
//                $list[$i]['isAvailableAllDay'] = 'F';
//                $list[$i]['minAllDayPrice'] = '0';
//                $list[$i]['AllDayCheckInHour'] = '00:00:00';
//            }
//
//            // 대실 가능 여부 체크 및 대실 이용 시간 추가
//            if (isAvailablePartTime($list[$i]['AccomIdx'], $startAt, $endAt)) {
//                $list[$i]['isAvailablePartTime'] = 'T';
//                $list[$i]['PartTimeHour'] = getPartTimeInfo($isMember, $dayType, $list[$i]['AccomIdx'])['PartTimeHour'];
//            } else {
//                $list[$i]['isAvailablePartTime'] = 'F';
//                $list[$i]['PartTimeHour'] = '00:00:00';
//            }
//
//        }
//
//        return $list;
//    }
//    // 2. 연박인 경우 -> 숙박만 가능
//    else{
//        return;
//    }
//
//}

//function getMotels($isMember, $startAt, $endAt, $motelGroupIdx)
//{
//    // 평일,주말 판단
//    $dayType = getDayType($startAt);
//
//    // 숙박 이용 날짜 차이 구하기
//    $dayDiff = (strtotime($endAt) - strtotime($startAt)) / 86400;
//
//    // 해당 지역의 모든 모텔 불러오기
//    $list = getMotelList($motelGroupIdx);
//
//    // 1. 1박인 경우 -> 숙박, 대실 모두 가능
//    if($dayDiff == 1){
//        //1-1. 숙박 조회
//
//        // 각 숙소 별 최소 가격 가져오기
//        $minAllDayPrice = getMinPrice($isMember, $dayType, $motelGroupIdx, $endAt);
//
//        for($i = 0; $i < count($minAllDayPrice); $i++){
//             $minAllDayPrice[$i]['AccomName'] = getAccomInfo($minAllDayPrice[$i]['AccomIdx'])['AccomName'];
//            $minAllDayPrice[$i]['AvgRating'] = getAccomInfo($minAllDayPrice[$i]['AccomIdx'])['AvgRating'];
//            $minAllDayPrice[$i]['NumOfReview'] = getAccomInfo($minAllDayPrice[$i]['AccomIdx'])['NumOfReview'];
//            $minAllDayPrice[$i]['AccomThumbnailUrl'] = getAccomInfo($minAllDayPrice[$i]['AccomIdx'])['AccomThumbnailUrl'];
//            $minAllDayPrice[$i]['PartTimeHour'] = getPartTimeInfo($isMember, $dayType, $minAllDayPrice[$i]['AccomIdx'])['PartTimeHour'];
//        }
//
//        //$motelInfo = getMotelInfo()
//        // 회원 여부로 나눔
//
//
//        //1-2. 대실인 경우
//
//
//        return $minAllDayPrice;
//    }
//    // 2. 연박인 경우 -> 숙박만 가능
//    else{
//        return;
//    }
//
//}

//지역 그룹별/퇴실일 기준/숙박 가능/모텔별/비회원/평일/최소가격
function getMinWeekdayPrice($AccomIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select Room.AccomIdx, Room.RoomIdx ,WeekdayPrice as minPrice
                from Room
                         left join (select Room.AccomIdx,
                                           Room.RoomIdx
                                    from Room
                                             join Reservation
                                                  on Room.AccomIdx = Reservation.AccomIdx and
                                                     Room.RoomIdx = Reservation.RoomIdx
                                    where ReserveType = 'A'
                                      and Room.AccomIdx = ?
                                      and CheckInDate < date(?)
                                      and CheckOutDate > date(?)) as t1
                                   on Room.AccomIdx = t1.AccomIdx and Room.RoomIdx = t1.RoomIdx
                         join RoomPrice on Room.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                where Room.AccomIdx = ?
                  and t1.RoomIdx is null
                order by WeekdayPrice
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $endAt, $endAt, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/비회원/주말/최소가격
function getMinWeekendPrice($AccomIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select Room.AccomIdx, Room.RoomIdx ,WeekendPrice as minPrice
                from Room
                         left join (select Room.AccomIdx,
                                           Room.RoomIdx
                                    from Room
                                             join Reservation
                                                  on Room.AccomIdx = Reservation.AccomIdx and
                                                     Room.RoomIdx = Reservation.RoomIdx
                                    where ReserveType = 'A'
                                      and Room.AccomIdx = ?
                                      and CheckInDate < date(?)
                                      and CheckOutDate > date(?)) as t1
                                   on Room.AccomIdx = t1.AccomIdx and Room.RoomIdx = t1.RoomIdx
                         join RoomPrice on Room.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                where Room.AccomIdx = ?
                  and t1.RoomIdx is null
                order by WeekdayPrice
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $endAt, $endAt, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/회원/평일/최소가격
function getMemberMinWeekdayPrice($AccomIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select Room.AccomIdx, Room.RoomIdx ,MemberWeekdayPrice as minPrice
                from Room
                         left join (select Room.AccomIdx,
                                           Room.RoomIdx
                                    from Room
                                             join Reservation
                                                  on Room.AccomIdx = Reservation.AccomIdx and
                                                     Room.RoomIdx = Reservation.RoomIdx
                                    where ReserveType = 'A'
                                      and Room.AccomIdx = ?
                                      and CheckInDate < date(?)
                                      and CheckOutDate > date(?)) as t1
                                   on Room.AccomIdx = t1.AccomIdx and Room.RoomIdx = t1.RoomIdx
                         join RoomPrice on Room.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                where Room.AccomIdx = ?
                  and t1.RoomIdx is null
                order by WeekdayPrice
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $endAt, $endAt, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

//지역 그룹별/퇴실일 기준/숙박 가능/모텔별/회원/주말/최소가격
function getMemberMinWeekendPrice($AccomIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select Room.AccomIdx, Room.RoomIdx ,MemberWeekendPrice as minPrice
                from Room
                         left join (select Room.AccomIdx,
                                           Room.RoomIdx
                                    from Room
                                             join Reservation
                                                  on Room.AccomIdx = Reservation.AccomIdx and
                                                     Room.RoomIdx = Reservation.RoomIdx
                                    where ReserveType = 'A'
                                      and Room.AccomIdx = ?
                                      and CheckInDate < date(?)
                                      and CheckOutDate > date(?)) as t1
                                   on Room.AccomIdx = t1.AccomIdx and Room.RoomIdx = t1.RoomIdx
                         join RoomPrice on Room.AccomIdx = RoomPrice.AccomIdx and Room.RoomIdx = RoomPrice.RoomIdx
                where Room.AccomIdx = ?
                  and t1.RoomIdx is null
                order by WeekdayPrice
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $endAt, $endAt, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getWeekdayPartTime($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select  PartTimeInfo.WeekdayTime as PartTimeHour
                from PartTimeInfo
where AccomIdx = ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getWeekendPartTime($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select PartTimeInfo.WeekendTime as PartTimeHour
                from PartTimeInfo
                where AccomIdx = ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getMemberWeekdayPartTime($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select PartTimeInfo.MemberWeekdayTime as PartTimeHour
                from PartTimeInfo
where AccomIdx = ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getMemberWeekendPartTime($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select PartTimeInfo.MemberWeekendTime as PartTimeHour
                from PartTimeInfo
where AccomIdx = ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// 특정 idx를 가진 숙소에 퇴실일 기준 숙박 예약이 있는지 체크
function hasAllDayReservation($accomIdx, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select exists(select Room.AccomIdx,
                       Room.RoomIdx
                from Room
                         join Reservation
                              on Room.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
                where Room.AccomIdx = ?
                  and ReserveType = 'A'
                  and CheckInDate < date(?)
                  and CheckOutDate > date(?)) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx, $endAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// 특정 idx를 가진 숙소에 해당일 기준 대실 예약이 있는지 체크
function hasPartTimeReservation($AccomIdx, $startAt, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select exists(select Room.AccomIdx,
                       Room.RoomIdx
                from Room
                         join Reservation
                              on Room.AccomIdx = Reservation.AccomIdx and
                                 Room.RoomIdx = Reservation.RoomIdx
                where ReserveType = 'P'
                  and Room.AccomIdx = ?
                  and CheckInDate > date(?)
                  and CheckOutDate < date(?)) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $startAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// 특정 idx를 가진 숙소의 퇴실일 기준 숙박 가능한지 체크하는 함수
function isAvailableAllDay($AccomIdx, $endAt)
{

    // 예약이 없으면 true 리턴
    if (hasAllDayReservation($AccomIdx, $endAt) == 0)
        return true;

    $pdo = pdoSqlConnect();
    $query = "
                select exists(select *
                from Room
                         left join (select Room.AccomIdx,
                                      Room.RoomIdx
                               from Room
                                        join Reservation
                                             on Room.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
                               where Room.AccomIdx = ?
                                 and ReserveType = 'A'
                                 and CheckInDate < date(?)
                                 and CheckOutDate > date(?)) as t1
                              on Room.AccomIdx = t1.AccomIdx and Room.RoomIdx = t1.RoomIdx
                where Room.AccomIdx = ?
                and t1.AccomIdx is null) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $endAt, $endAt, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;


    return $res[0]['exist'];
}

// 특정 idx를 가진 숙소의 대실 가능한지 체크하는 함수
function isAvailablePartTime($AccomIdx, $startAt, $endAt)
{

    if (hasPartTimeReservation($AccomIdx, $startAt, $endAt) == 0)
        return true;

    $pdo = pdoSqlConnect();
    $query = "
                select exists(select *
                from Room
                         left join (
                    select Room.AccomIdx,
                           Room.RoomIdx
                    from Room
                             join Reservation
                                  on Room.AccomIdx = Reservation.AccomIdx and
                                     Room.RoomIdx = Reservation.RoomIdx
                    where ReserveType = 'P'
                      and Room.AccomIdx = ?
                      and CheckInDate > date(?)
                      and CheckOutDate < date(?)) as t1
                             on Room.AccomIdx = t1.AccomIdx and Room.RoomIdx = t1.RoomIdx
                where Room.AccomIdx = ?
                  and t1.RoomIdx is null) as exist;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $startAt, $endAt, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

//// 어떤 Room의 당일 대실이 있는지 확인 -> 체크아웃시간= 숙박 이용시간 임을 알려주는 함수
//function checkPartTimeReservation($AccomIdx, $RoomIdx, $startAt, $endAt){
//
//    $pdo = pdoSqlConnect();
//    $query = "
//                select CheckOutDate
//                from Reservation
//                where ReserveType = 'P'
//                  and AccomIdx = ?
//                  and RoomIdx = ?
//                  and CheckInDate > ?
//                  and CheckOutDate < ?
//    ";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    // 어떤 룸의 당일 대실이 있는지 체크
//    if(empty($res[0]))
//        return 'empty';
//    else {
//        return date("H:i:s",strtotime($res[0]['CheckOutDate']));
//    }
//}

// 특정 idx를 가진 숙소의 이름, 평균평점, 리뷰 수 가져오는 함수
function getAccomInfo($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select
                       avgRating,
                       If(isnull(count(*)), 0, count(*)) as numOfReview
                from Accommodation
                         join (select AccommodationReview.AccomIdx, avg(OverallRating) as avgRating
                               from Accommodation
                                        join AccommodationReview on Accommodation.AccomIdx = AccommodationReview.AccomIdx
                               group by Accommodation.AccomIdx) as t1
                              on Accommodation.AccomIdx = t1.AccomIdx
                         join AccommodationReview
                              on AccommodationReview.AccomIdx = Accommodation.AccomIdx
                where Accommodation.AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function getAllDayHour($AccomIdx, $dayType)
{

    $pdo = pdoSqlConnect();
    $query = "
                select WeekdayTime, WeekendTime
                from AllDayInfo
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    if (strcmp('weekday', $dayType))
        return $res[0]['WeekdayTime'];
    else
        return $res[0]['WeekendTime'];
}

function getAccomTag($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select TagIdx, TagName
                from Accommodation join AccommodationTag on Accommodation.AccomIdx = AccommodationTag.AccomIdx
                where Accommodation.AccomIdx = ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserPick($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select count(*) as cnt
                from UserPick
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['cnt'];
}
