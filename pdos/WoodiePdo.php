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

// 특정 숙소의 숙박 최소 금액 출력
function getMinAllDayPrice($isMember, $dayType, $AccomIndex, $AccomType, $endAt)
{
    /*
     * 회원.평일
     * 회원.주말
     * 비회원.평일
     * 비회원.주말
     */

    if ($isMember == true && $dayType == 'weekday')
        return getMemberMinWeekdayPrice($AccomIndex, $AccomType, $endAt);
    else if ($isMember == true && $dayType == 'weekend')
        return getMemberMinWeekendPrice($AccomIndex, $AccomType, $endAt);
    else if ($isMember == false && $dayType == 'weekday')
        return getMinWeekdayPrice($AccomIndex, $AccomType, $endAt);
    else
        return getMinWeekendPrice($AccomIndex, $AccomType, $endAt);

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




// 특정 지역 그룹의 모든 모텔idx 가져오기
function getMotelList($motelGroupIdx){
    $pdo = pdoSqlConnect();
    $query = "
                select AccomIdx
                from Region
                         join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                where AccomType = 'M'
                  and MotelGroup.MotelGroupIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$motelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 지역그룹별 모텔 조회 함수
function getMotels($isMember, $startAt, $endAt, $motelGroupIdx)
{
    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt)) / 86400;

    // 해당 지역의 모든 모텔 불러오기
    $list = getMotelList($motelGroupIdx);

    // 1. 1박인 경우 -> 숙박, 대실 모두 가능
    if($dayDiff == 1){

        for($i = 0; $i < count($list); $i++){

            $list[$i]['AccomName'] = getAccomInfo($list[$i]['AccomIdx'])['AccomName'];
            $list[$i]['AvgRating'] = getAccomInfo($list[$i]['AccomIdx'])['AvgRating'];
            $list[$i]['NumOfReview'] = getAccomInfo($list[$i]['AccomIdx'])['NumOfReview'];
            $list[$i]['AccomThumbnailUrl'] = getAccomInfo($list[$i]['AccomIdx'])['AccomThumbnailUrl'];

            // 숙박 가능 여부 체크 및 추가
            if( isAvailableAllDay($motelGroupIdx, $list[$i]['AccomIdx'], 'M', $endAt) == true){
                $list[$i]['isAvailableAllDay'] = 'T';
                $list[$i]['minAllDayPrice'] = getMinAllDayPrice($isMember, $dayType, $list[$i]['AccomIdx'], 'M', $endAt);
            }
            else{
                $list[$i]['isAvailableAllDay'] = 'F';
                $list[$i]['minAllDayPrice'] = '0';
            }


            // 대실 가능 여부 체크 및 대실 이용 시간 추가
            if (isAvailablePartTime($motelGroupIdx, $list[$i]['AccomIdx'], $startAt, $endAt) == true) {
                $list[$i]['isAvailablePartTime'] = 'T';
                $list[$i]['PartTimeHour'] = getPartTimeInfo($isMember, $dayType, $list[$i]['AccomIdx'])['PartTimeHour'];
            } else {
                $list[$i]['isAvailablePartTime'] = 'F';
                $list[$i]['PartTimeHour'] = '00:00:00';
            }


        }

        return $list;
    }
    // 2. 연박인 경우 -> 숙박만 가능
    else{
        return;
    }

}

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
function getMinWeekdayPrice($AccomIdx, $AccomType, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select RoomPrice.AccomIdx, min(RoomPrice.WeekdayPrice) as minPrice
from (select Room.AccomIdx,
             Room.RoomIdx,
             IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable
      from Region
               join MotelGroup
                    on Region.RegionIdx = MotelGroup.RegionIdx
               join Accommodation
                    on Region.RegionIdx = Accommodation.RegionIdx
               join Room
                    on Accommodation.AccomIdx = Room.AccomIdx
               left join Reservation
                         on Accommodation.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
      where AccomType = ?
        and ReserveType = 'A'
        and Accommodation.AccomIdx = ?) as result
         join RoomPrice
              on result.AccomIdx = RoomPrice.AccomIdx and result.RoomIdx = RoomPrice.RoomIdx
where isAvailable = 'Y'
group by result.AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $AccomType, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['minPrice'];
}

// 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/비회원/주말/최소가격
function getMinWeekendPrice($AccomIdx, $AccomType, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select RoomPrice.AccomIdx, min(RoomPrice.WeekendPrice) as minPrice
from (select Room.AccomIdx,
             Room.RoomIdx,
             IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable
      from Region
               join MotelGroup
                    on Region.RegionIdx = MotelGroup.RegionIdx
               join Accommodation
                    on Region.RegionIdx = Accommodation.RegionIdx
               join Room
                    on Accommodation.AccomIdx = Room.AccomIdx
               left join Reservation
                         on Accommodation.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
      where AccomType = ?
        and ReserveType = 'A'
        and Accommodation.AccomIdx = ?) as result
         join RoomPrice
              on result.AccomIdx = RoomPrice.AccomIdx and result.RoomIdx = RoomPrice.RoomIdx
where isAvailable = 'Y'
group by result.AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $AccomType, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['minPrice'];
}

// 지역 그룹별/퇴실일 기준/숙박 가능/모텔별/회원/평일/최소가격
function getMemberMinWeekdayPrice($AccomIdx, $AccomType, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select RoomPrice.AccomIdx, min(RoomPrice.MemberWeekdayPrice) as minPrice
from (select Room.AccomIdx,
             Room.RoomIdx,
             IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable
      from Region
               join MotelGroup
                    on Region.RegionIdx = MotelGroup.RegionIdx
               join Accommodation
                    on Region.RegionIdx = Accommodation.RegionIdx
               join Room
                    on Accommodation.AccomIdx = Room.AccomIdx
               left join Reservation
                         on Accommodation.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
      where AccomType = ?
        and ReserveType = 'A'
        and Accommodation.AccomIdx = ?) as result
         join RoomPrice
              on result.AccomIdx = RoomPrice.AccomIdx and result.RoomIdx = RoomPrice.RoomIdx
where isAvailable = 'Y'
group by result.AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $AccomType, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['minPrice'];
}

//지역 그룹별/퇴실일 기준/숙박 가능/모텔별/회원/주말/최소가격
function getMemberMinWeekendPrice($AccomIdx, $AccomType, $endAt)
{
    $pdo = pdoSqlConnect();
    $query = "
               select RoomPrice.AccomIdx, min(RoomPrice.MemberWeekendPrice) as minPrice
from (select Room.AccomIdx,
             Room.RoomIdx,
             IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable
      from Region
               join MotelGroup
                    on Region.RegionIdx = MotelGroup.RegionIdx
               join Accommodation
                    on Region.RegionIdx = Accommodation.RegionIdx
               join Room
                    on Accommodation.AccomIdx = Room.AccomIdx
               left join Reservation
                         on Accommodation.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
      where AccomType = ?
        and ReserveType = 'A'
        and Accommodation.AccomIdx = ?) as result
         join RoomPrice
              on result.AccomIdx = RoomPrice.AccomIdx and result.RoomIdx = RoomPrice.RoomIdx
where isAvailable = 'Y'
group by result.AccomIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $AccomType, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['minPrice'];
}

function getWeekdayPartTime($accomIdx){
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

function getWeekendPartTime($accomIdx){
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

function getMemberWeekdayPartTime($accomIdx){
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

function getMemberWeekendPartTime($accomIdx){
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

// 특정 idx를 가진 숙소의 이름, 평균평점, 리뷰 수 가져오는 함수
function getAccomInfo($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select t1.AccomIdx,
                       AccomName,
                       AvgRating,
                       If(isnull(count(*)), 0, count(*)) as NumOfReview,
                       AccomThumbnailUrl
                from Accommodation
                         join (select AccommodationReview.AccomIdx, avg(OverallRating) as AvgRating
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

function isAvailableAllDay($MotelGroupIdx, $AccomIdx, $AccomType, $endAt){
    $pdo = pdoSqlConnect();
    $query = "
               select Room.AccomIdx,
       Room.RoomIdx,
       IF((CheckInDate < date(?) and CheckOutDate > date(?)), 'N', 'Y') as isAvailable
from Region
         join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
         join Room on Accommodation.AccomIdx = Room.AccomIdx
         left join Reservation
                   on Accommodation.AccomIdx = Reservation.AccomIdx and Room.RoomIdx = Reservation.RoomIdx
where AccomType = ?
  and MotelGroup.MotelGroupIdx = ? and ReserveType =  'A' and Accommodation.AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$endAt, $endAt, $AccomType, $MotelGroupIdx, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    if(empty($res) == true) return true;

    foreach($res as $value){
        if($value['isAvailable'] == 'Y')
            return true;
    }
    return false;
}

function isAvailablePartTime($MotelGroupIdx, $AccomIdx, $startAt, $endAt){
    $pdo = pdoSqlConnect();
    $query = "
                SELECT exists(
               select  Reservation.AccomIdx, Reservation.RoomIdx, Reservation.CheckInDate, Reservation.CheckOutDate
               from Region
                        join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                        join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                        join Room on Accommodation.AccomIdx = Room.AccomIdx
                        left join Reservation
                                  on Accommodation.AccomIdx = Reservation.AccomIdx and
                                     Room.RoomIdx = Reservation.RoomIdx
               where AccomType = 'M'
                 and MotelGroup.MotelGroupIdx = ?
                 and ReserveType = 'P'
                 and Accommodation.AccomIdx = ?
                 and CheckInDate > date(?)
                 and CheckOutDate < date(?)
    ) as isAvailable;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$MotelGroupIdx, $AccomIdx, $startAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    if ($res[0]['isAvailable'] == true){
        return '0';
    }

    return true;
}


