<?php

// 평일/주말 판단 함수
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
function getPartTime($isMember, $dayType, $AccomIdx)
{
    /*
     * 회원.평일
     * 회원.주말
     * 비회원.평일
     * 비회원.주말
     */
    if ($isMember == true && $dayType == 'weekday')
        return getAvailableMemberWeekdayPartTime($AccomIdx);
    else if ($isMember == true && $dayType == 'weekend')
        return getAvailableMemberWeekendPartTime($AccomIdx);
    else if ($isMember == false && $dayType == 'weekday')
        return getAvailableWeekdayPartTime($AccomIdx);
    else
        return getAvailableWeekendPartTime($AccomIdx);
}

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

// 특정 숙소의 Tag 개수
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

// 특정 숙소의 유저 찜 개수
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

// 특정 방의 해당 날짜에 대실이 가능한지 체크한다.
function checkPartTimeReserve($AccomIdx, $RoomIdx, $startAt, $endAt){
    $pdo = pdoSqlConnect();
    $query = "
                select not exists(select *
                from Reservation
                where AccomIdx = ?
                and RoomIdx = ?
                and ReserveType = 'P' 
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

    // 연박손님이 있으면 대실 불가
    if(!checkLongDayReserve($AccomIdx, $RoomIdx, $startAt, $endAt))
        return false;

    return $res[0]['exist'];
}

// 특정 방의 해당 날짜에 숙박이 가능한지 체크한다.
function checkAllDayReserve($AccomIdx, $RoomIdx, $endAt){
    $pdo = pdoSqlConnect();
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

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// (대실 체크에 종속적)특정 방에 해당 일에 연박을 하고 있는 사람이 있는지 체크한다. => 합치는 것 필요
function checkLongDayReserve($AccomIdx, $RoomIdx, $startAt, $endAt){
    $pdo = pdoSqlConnect();
    $query = "
                select not exists(select *
                from Reservation
                where ReserveType = 'A'
                      and AccomIdx = ?
                and RoomIdx = ?
                and CheckInDate < ?
                and CheckOutDate > ?) as exist;
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

// (실제적인 연박 체크)특정 방에 해당 일에 연박을 하고 있는 사람이 있는지 체크한다. => 합치는 것 필요
function checkConsecutiveStayAvailable($AccomIdx, $RoomIdx, $startAt, $dayDiff){

    // 9 ~ 12 연박 가정하면

    $afterStartAt = date("Y-m-d", strtotime($startAt." +1 day")); // 10;


    for($i=0; $i < $dayDiff - 1 ; $i++){ // 2번 돌고

        // 1. 겹치는 숙박이 없어야 한다.
        $tmp_startAt = date("Y-m-d", strtotime($afterStartAt." +".$i."day")); // 10, 11
        $tmp_endAt = date("Y-m-d", strtotime($tmp_startAt." +".$i."day")); // 11, 12

        if(!checkAllDayReserve($AccomIdx, $RoomIdx, $tmp_endAt)){ // 11,12 숙박 검사
            // 예약이 있으면
            return false;
        }

        // 2. 중간 날짜에 대실이 없어야 한다. => 10~11 , 11~12 대실 X
        if( !empty(getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $tmp_startAt, $tmp_endAt)) ){

            // 중간 날짜들의 대실 정보를 불러올 수 있으면
            return false;
        }
    }

    if(!checkAllDayReserve($AccomIdx, $RoomIdx, $afterStartAt))    // 10일에 숙박이 있으면
        return false;

    return true;

}

// 특정 그룹 검색 인원에 맞는 모든 모텔의 방을 다 불러온다.
function getMotelRoomList($motelGroupIdx, $adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select Accommodation.AccomIdx, RoomIdx, AccomName, AccomThumbnailUrl
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

// 특정 방의 대실 예약의 체크인, 체크 아웃 시간을 가져온다.
function getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $startAt, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
                select time(CheckInDate) as CheckIn, time(CheckOutDate) as CheckOut 
                from Reservation
                where ReserveType = 'P'
                and      AccomIdx = ?
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

    return $res;
}

// 특정 방의 원하는 날짜 이전 날짜에 퇴실한 숙박 예약의 정보를 가져온다.
function getYesterdayAllDayReservation($AccomIdx, $RoomIdx, $beforeEndAt)
{
    // 쿼리의 ?는 퇴실시간 기준으로 나눠야함
    $pdo = pdoSqlConnect();
    $query = "
                select *
                from Reservation
                where ReserveType = 'A'
                  and AccomIdx = ?
                  and RoomIdx = ?
                  and CheckInDate < date(?)
                  and CheckOutDate > date(?);
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx, $beforeEndAt, $beforeEndAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 특정 방의 특정 날짜 당일의 숙박 예약의 정보를 가져온다.
function getTodayAllDayReservation($AccomIdx, $RoomIdx, $startAt, $endAt)
{

    $pdo = pdoSqlConnect();
    $query = "
                select *
                from Reservation
                where ReserveType = 'A'
                  and AccomIdx = ?
                  and RoomIdx = ?
                  and CheckInDate > ?
                  and CheckInDate < ?
                  and CheckOutDate > ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx, $startAt, $endAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//////////////////////////////////////////////////////////////////////////////////////////////////

// 특정 숙소의 대실 마감 시간 정보 가져오기
function getPartTimeDeadline($AccomIdx, $dayType)
{

    $pdo = pdoSqlConnect();
    $query = "
                select WeekdayDeadline, WeekdendDeadline
                from PartTimeInfo
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    // 평일이면
    if(strcmp($dayType, 'weekday') == 0){
        return $res[0]['WeekdayDeadline'];
    }
    else
        return $res[0]['WeekdendDeadline'];
}

// 특정 방의 대실 가격 정보 가져오기
function getPartTimePrice($AccomIdx, $RoomIdx, $isMember, $dayType)
{

    $pdo = pdoSqlConnect();
    $query = "
                select PartTimeWeekdayPrice,
                       PartTimeWeekendPrice,
                       MemberPartTimeWeekdayPrice,
                       MemberPartTimeWeekendPrice
                from PartTimePrice
                where AccomIdx = ?
                  and RoomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    /*
     * 평일,회원
     * 주말,회원
     * 평일,비회원
     * 주말,비회원
     */
    if(strcmp($dayType, 'weekday') == 0 && $isMember == true){
        return $res[0]['MemberPartTimeWeekdayPrice'];
    }
    //
    else if(strcmp($dayType, 'weekend') == 0 && $isMember == true){
        return $res[0]['MemberPartTimeWeekendPrice'];
    }
    else if(strcmp($dayType, 'weekday') == 0 && $isMember == false){
        return $res[0]['PartTimeWeekdayPrice'];
    }
    else
        return $res[0]['PartTimeWeekendPrice'];
}

// 특정 슥소의 대실 이용 시간 정보 가져오기
function getPartTimeHour($AccomIdx, $isMember, $dayType)
{

    $pdo = pdoSqlConnect();
    $query = "
                select WeekdayTime, WeekendTime, MemberWeekdayTime, MemberWeekendTime
                from PartTimeInfo
                where AccomIdx = ?
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    /*
     * 평일,회원
     * 주말,회원
     * 평일,비회원
     * 주말,비회원
     */
    if(strcmp($dayType, 'weekday') == 0 && $isMember == true){
        return $res[0]['MemberWeekdayTime'];
    }
    //
    else if(strcmp($dayType, 'weekend') == 0 && $isMember == true){
        return $res[0]['MemberWeekendTime'];
    }
    else if(strcmp($dayType, 'weekday') == 0 && $isMember == false){
        return $res[0]['WeekdayTime'];
    }
    else
        return $res[0]['WeekendTime'];
}

// 특정 방의 숙박 이용 시작 시간 가져오기
function getAllDayTime($AccomIdx, $isMember, $dayType)
{

    $pdo = pdoSqlConnect();
    $query = "
                select WeekdayTime, WeekendTime, MemberWeekdayTime, MemberWeekendTime
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

    /*
     * 평일,회원
     * 주말,회원
     * 평일,비회원
     * 주말,비회원
     */
    if(strcmp($dayType, 'weekday') == 0 && $isMember == true){
        return $res[0]['MemberWeekdayTime'];
    }
    //
    else if(strcmp($dayType, 'weekend') == 0 && $isMember == true){
        return $res[0]['MemberWeekendTime'];
    }
    else if(strcmp($dayType, 'weekday') == 0 && $isMember == false){
        return $res[0]['WeekdayTime'];
    }
    else
        return $res[0]['WeekendTime'];
}

// 특정 방의 숙박 가격 정보 가져오기
function getAllDayPrice($AccomIdx, $RoomIdx, $isMember, $dayType)
{

    $pdo = pdoSqlConnect();
    $query = "
                select WeekdayPrice, WeekendPrice, MemberWeekdayPrice, MemberWeekendPrice
                from RoomPrice
                where AccomIdx = ?
                and RoomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx, $RoomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    /*
     * 평일,회원
     * 주말,회원
     * 평일,비회원
     * 주말,비회원
     */
    if(strcmp($dayType, 'weekday') == 0 && $isMember == true){
        return $res[0]['MemberWeekdayPrice'];
    }
    //
    else if(strcmp($dayType, 'weekend') == 0 && $isMember == true){
        return $res[0]['MemberWeekendPrice'];
    }
    else if(strcmp($dayType, 'weekday') == 0 && $isMember == false){
        return $res[0]['WeekdayPrice'];
    }
    else
        return $res[0]['WeekendPrice'];
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 모텔 정보 화면 불러오기
function getMotels($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child)
{
    $motelRoomInfo = getMotelRooms($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child);

    // 그룹화 시켜야 함

    return $motelRoomInfo;
}

function getMotelRooms($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child)
{

    // 전날 변수 저장
    $beforeStartAt = date("Y-m-d", strtotime($startAt." -1 day"));
    $beforeEndAt = date("Y-m-d", strtotime($endAt." -1 day"));


    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 해당 지역 그룹의 모든 방 다 가져오기
    $motelRoomlist = getMotelRoomList($motelGroupIdx, $adult, $child);



    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < count($motelRoomlist); $i++) {

        $nowAccomIdx = $motelRoomlist[$i]['AccomIdx'];
        $nowRoomIdx = $motelRoomlist[$i]['RoomIdx'];

        // 1. 1박인 경우 => 숙박 + 대실만 가능
        if ($dayDiff == 1) {

            // 1-1.해당 객실의 대실이 가능하다면
            if (checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)) {

                $motelRoomlist[$i]['IsPartTimeAvailable'] = 'T';

                // 이전 날 숙박이 있었는지 체크 후 입실 가능 시간 배정
                if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $beforeEndAt)){
                    // 이전 날 숙박이 없었다면 => '10:00:00'
                    $motelRoomlist[$i]['AvailablePartTimeCheckIn'] = '10:00:00';
                }else{
                    // 이전 날 숙박이 있었다면 => (이전 숙박 퇴실 시간 + 1시간) 대실 입실 가능 시간
                    $motelRoomlist[$i]['AvailablePartTimeCheckIn'] =  date("H:i:s", strtotime(getYesterdayAllDayReservation($nowAccomIdx, $nowRoomIdx, $beforeEndAt)[0]['CheckOutDate']." +1hours"));
                }

                // 대실 당일 숙박예약이 있는지 체크 후 퇴실 가능 시간 배정
                if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){
                    // 대실 당일 숙박 예약이 없는 경우 = > 대실 퇴실 시간 마감까지 가능
                    $motelRoomlist[$i]['AvailablePartTimeDeadline'] = getPartTimeDeadline($nowAccomIdx, $dayType);
                }
                else{
                    // 대실 당일 숙박 예약이 있는 경우 => 숙박 입실 시간 -1시간 까지 체크 아웃해야함
                    echo $nowAccomIdx.'/'.$nowRoomIdx;
                    $motelRoomlist[$i]['AvailablePartTimeDeadline'] = date("H:i:s", strtotime(getTodayAllDayReservation($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckInDate']." -1hours"));
                }

                // 특정 방의 대실 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['PartTimePrice'] = getPartTimePrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);

                // 특정 방의 대실 이용 시간을 가져 온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['PartTimeHour'] = getPartTimeHour($nowAccomIdx, $isMember, $dayType);

            }
            else {

                $motelRoomlist[$i]['IsPartTimeAvailable'] = 'F';

                // 안되는 이유 => 이유1. 그 날 자는(?)연박 손님이 있는 경우 / 2. 대실이 이미 있는 경우

                // 1. 그 날 자는(?)연박 손님이 있는 경우
                if(!checkLongDayReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)){
                    // 딱히 할게 없네
                }
                else{
                    // 2. 이미 대실이 있어서 안되는 경우 => 그 객실의 대실 체크 인, 아웃 타임 출력
                    $motelRoomlist[$i]['ReservedCheckIn'] = getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckIn'];
                    $motelRoomlist[$i]['ReservedCheckOut'] = getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckOut'];
                }

            }

            // 1-2.당일 숙박이 가능한지 체크한다.
            if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){

                // 해당 객실이 숙박이 가능하다면
                // => 1. 당일 대실 예약이 있는 경우, 대실 퇴실 시간 + 1 부터 입실 가능
                // => 2. 당일 대실 예약이 없는 경우, 규정 숙박 입실 시간 부터 가능

                $motelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 당일 대실 예약이 있는지 체크한다
                if(checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)){
                    // 당일 대실이 없는 경우 => 규정 숙박 입실 시간 부터 가능
                    $motelRoomlist[$i]['AvailableAllDayCheckIn'] = getAllDayTime($nowAccomIdx, $isMember, $dayType);
                }
                else{
                    // 당일 대실이 있는 경우 => (대실 퇴실 시간 + 1 시간) 과 규정 숙박 입실 시간 비교해서 늦은(큰) 시간 부터 입실 가능
                    $todayAvailableAllDayCheckInTime = date("H:i:s", strtotime(getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckOut']." +1hours"));
                    $rule = getAllDayTime($nowAccomIdx, $isMember, $dayType);
//                    echo $nowAccomIdx.'!!'.$nowRoomIdx.'  ';
//                    echo $todayAvailableAllDayCheckInTime.'zz';
//                    echo $rule;
                    // 비교
                    if($todayAvailableAllDayCheckInTime < $rule)
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $rule;
                    else
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $todayAvailableAllDayCheckInTime;
                }

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);

            }
            else{
                // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다. / 이유2. 다음날 일찍 대실 예약 손님이 이미 있다.(가정 상황에서의 문제 발생)
                //
                $motelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }
        // 2. 연박인 경우 => 숙박만 가능
        else {
                // 해당 기간에 연박이 가능한지 본다.
                if(checkConsecutiveStayAvailable($nowAccomIdx, $nowRoomIdx, $startAt, $dayDiff)){
                    // 연박이 가능하다면
                    // 해당 객실이 숙박이 가능하다면
                    // => 1. 당일 대실 예약이 있는 경우, 대실 퇴실 시간 + 1 부터 입실 가능
                    // => 2. 당일 대실 예약이 없는 경우, 규정 숙박 입실 시간 부터 가능

                    $motelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                    // 검사에 활용한 임시 퇴실 시간 변수
                    $temp_endAt = date("Y-m-d", strtotime($startAt." +1day"));

                    // 당일 대실 예약이 있는지 체크한다
                    if(checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $temp_endAt)){
                        // 당일 대실이 없는 경우 => 규정 숙박 입실 시간 부터 가능
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = getAllDayTime($nowAccomIdx, $isMember, $dayType);
                    }
                    else{
                        // 당일 대실이 있는 경우 => (대실 퇴실 시간 + 1 시간) 과 규정 숙박 입실 시간 비교해서 늦은(큰) 시간 부터 입실 가능
                        $todayAvailableAllDayCheckInTime = date("H:i:s", strtotime(getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $temp_endAt)[0]['CheckOut']." +1hours"));
                        $rule = getAllDayTime($nowAccomIdx, $isMember, $dayType);
//                    echo $nowAccomIdx.'!!'.$nowRoomIdx.'  ';
//                    echo $todayAvailableAllDayCheckInTime.'zz';
//                    echo $rule;
                        // 비교
                        if($todayAvailableAllDayCheckInTime < $rule)
                            $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $rule;
                        else
                            $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $todayAvailableAllDayCheckInTime;
                    }

                    // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                    $motelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);


                }
                else{
                    // 연박이 안되면
                    $motelRoomlist[$i]['IsAllDayAvailable'] = 'F';
                }
        }


        $motelRoomlist[$i]['AvgRating'] = getAccomInfo($nowAccomIdx)['avgRating'];
        $motelRoomlist[$i]['NumOfReview'] = getAccomInfo($nowAccomIdx)['numOfReview'];
        $motelRoomlist[$i]['NumOfUserPick'] = getUserPick($nowAccomIdx);
        $motelRoomlist[$i]['AccomTag'] = getAccomTag($nowAccomIdx);

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

// 회원,비회원 / 주말,평일 별 대실 이용 가능시간
function getAvailableWeekdayPartTime($accomIdx)
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

function getAvailableWeekendPartTime($accomIdx)
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

function getAvailableMemberWeekdayPartTime($accomIdx)
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

function getAvailableMemberWeekendPartTime($accomIdx)
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

