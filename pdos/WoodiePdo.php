<?php

// key를 안 갖고 있으면 그냥 프로그램 종료
function isValidKey($key, $arr)
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

// motelGroupIdx 의 유효성 검사
function isValidMotelGroupIdx($motelGroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select exists(select *
                from MotelGroupName
                where MotelGroupIdx = ?) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$motelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// hotelGroupIdx 의 유효성 검사
function isValidHotelGroupIdx($hotelGroupIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select exists(select *
                from HotelGroupName
                where HotelGroupIdx = ?) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$hotelGroupIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// AccomIdx 의 유효성 검사
function isValidAccomIdx($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select exists(select *
                                from Accommodation
                                where AccomIdx = ?) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// roomIdx 의 유효성 검사
function isValidRoomIdx($accomIdx, $roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select exists(select *
                                from Room
                                where AccomIdx = ? and RoomIdx = ?) as exist
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$accomIdx, $roomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


/*
 * *******************************************************************************************
 */
// 무슨 타입의 숙소인지 판단하는 함수
function getTypeOfAccom($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select AccomType
                from Accommodation
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['AccomType'];
}

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

// 특정 idx를 가진 숙소의 이름, 평균평점, 리뷰 수 가져오는 함수
function getAccomInfo($accomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select AccomName,
                       AccomType,
                       AccomThumbnailUrl,
                       If(isnull(t1.avgRating), 0, avgRating) as avgRating,
                       If(isnull(count(*)), 0, count(*)) as numOfReview,
                        GuideFromStation,
                       Accommodation.AccomLatitude,
                       Accommodation.AccomLongitude,
                       Accommodation.AccomAddress,
                       Accommodation.AccomIntroduction,
                       Accommodation.AccomGuide
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
                select AccommodationTag.TagIdx, TagName
                from AccommodationTag join TagList on AccommodationTag.TagIdx = TagList.TagIdx
                where AccomIdx = ?
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

// 특정 숙소의 사진들 가져오기
function getAccomPhotos($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select PhotoType,
                       IF(isnull(AccomodationPhotos.RoomIdx), 0, AccomodationPhotos.RoomIdx) as RoomIdx,
                       IF(isnull(RoomName), 0, RoomName) as RoomName,
                       AccomodationPhotos.PhotoInfo,
                       PhotoUrl
                from AccomodationPhotos
                         left join Room on AccomodationPhotos.AccomIdx = Room.AccomIdx and AccomodationPhotos.RoomIdx = Room.RoomIdx
                where AccomodationPhotos.AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

//    $result =array();
//
//    for($i = 0; $i < count($res); $i++){
//        // 포토 타입
//        if(strcmp($res[$i]['PhotoType'], 'M') == 0){
//            $result['Main'][] = $res[$i]['PhotoUrl'];
//        }
//        // 룸idx
//        if($res[$i]['RoomIdx'] != 0){
//            $result['Room'][$res[$i]['RoomName']][] = $res[$i]['PhotoUrl'];
//        }
//        // 숙소분위기
//        else{
//            $result['Mood'][$res[$i]['PhotoInfo']][] = $res[$i]['PhotoUrl'];
//        }
//    }

    return $res;
}

// 특정 숙소의 리뷰와 리뷰 답글 가져오기
function getAccomReviewWithReply($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "SELECT
    BR.UserIdx,
    BR.ReviewIdx,
    UR.UserName,
    RoomName,
    UR.ReserveType,
    BR.ReviewContent,
    BR.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, BR.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, BR.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, BR.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, BR.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, BR.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, BR.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, BR.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, BR.CreatedAt, now()), '일 전')
								ELSE
									BR.CreatedAt
							END
					END
			END
	END as WrittenTime,
    CASE
        WHEN
            BR.IsPhotoReview = 'Y'
        THEN
            (SELECT
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = BR.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = BR.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = BR.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = BR.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = BR.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    (select AccommodationReview.AccomIdx, AccommodationReview.ReviewIdx,AccommodationReview.UserIdx,AccommodationReview.ReviewContent
 ,AccommodationReview.IsPhotoReview, AccommodationReview.OverallRating, AccommodationReview.KindnessRating, AccommodationReview.CleanlinessRating,
 AccommodationReview.ConvenienceRating, AccommodationReview.LocationRating, AccommodationReview.CreatedAt, AccommodationReview.UpdatedAt, AccommodationReview.isDeleted
 from (AccommodationReview join BestReview
ON (AccommodationReview.AccomIdx = BestReview.AccomIdx and AccommodationReview.ReviewIdx = BestReview.ReviewIdx))) BR
    JOIN
    (SELECT
        UserIdx, UserName, Reservation.AccomIdx, RoomName, ReserveType, ReserveIdx
    FROM
        User
			JOIN (Reservation JOIN Room On (Reservation.AccomIdx = Room.AccomIdx and Reservation.RoomIdx = Room.RoomIdx))
		USING (UserIdx)) UR ON (UR.UserIdx = BR.UserIdx
        AND UR.AccomIdx = BR.AccomIdx)
WHERE
    BR.AccomIdx = ?     AND BR.IsDeleted = 'N'
ORDER BY CreatedAt DESC
LIMIT 2;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 리뷰에 대한 답변 가져오기
function getNumOfReviewReply($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select count(*) as cnt
                from ReviewReply
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

// 숙소의 전화번호  가져오기
function getAccomContact($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select AccomContact as Contact
                from Accommodation
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['Contact'];
}

//  숙소의 주차 가능 여부 가져오기 => 오늘 날짜에만 쓰임
function getAccomParkingStatus($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select IsFullParking
                from Accommodation
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['IsFullParking'];
}

// 숙소의 편의 시설 가져옴
function getAccomFacilities($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select FacilityName
                from AccommodationFacilities
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    $result = array();
    for($i = 0; $i < count($res); $i++){
        $result[] = $res[$i]['FacilityName'];
    }

    return $result;
}

// [호텔] 호텔의 등급 정보를 가져옴
function getHotelGrade($AccomIdx){

    $pdo = pdoSqlConnect();
    $query = "
                select HotelGrade.AccomGrade,
                       Authentication
                from HotelGrade
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// 특정 숙소의 요금 정보 탭을 가져온다.
function getMotelMoneyInfo($AccomIdx){

    $res = array();
    $res['PartTime'] = getMotelPartTimeInfo($AccomIdx);
    $res['AllDay'] = getMotelAllDayInfo($AccomIdx);

    return $res;
}

// 특정 숙소의 요금 정보 탭 중 대실 파트
function getMotelPartTimeInfo($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
                select PartTimePrice.RoomIdx      as PartTime_RoomIdx,
       RoomName                   as PartTime_RoonmName,
       WeekdayTime                as PartTime_Weekday_Time,
       WeekendTime                as PartTime_Weekend_Time,
       WeekdayDeadline            as PartTime_Weekday_Deadline,
       WeekendDeadline            as PartTime_Weekend_Deadline,
       MemberWeekdayTime          as PartTime_Member_Weekday_Time,
       MemberWeekendTime          as PartTime_Member_Weekend_Time,
       MemberWeekdayDeadline      as PartTime_Member_Weekday_Deadline,
       MemberWeekendDeadline      as PartTime_Member_Weekend_Deadline,
       PartTimeWeekdayPrice       as PartTime_Weekday_Price,
       PartTimeWeekendPrice       as PartTime_Weekend_Price,
       MemberPartTimeWeekdayPrice as PartTime_Member_Weekday_Price,
       MemberPartTimeWeekendPrice as PartTime_Member_Weekend_Price
from PartTimeInfo
         join PartTimePrice on PartTimeInfo.AccomIdx = PartTimePrice.AccomIdx
         join Room on PartTimeInfo.AccomIdx = Room.AccomIdx and PartTimePrice.RoomIdx = Room.RoomIdx
where PartTimeInfo.AccomIdx = ?;
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

// 특정 숙소의 요금 정보 탭 중 숙박 파트
function getMotelAllDayInfo($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select Room.RoomIdx             as AllDay_RoomIdx
                     , RoomName                 as AllDay_RoomName
                     , WeekdayTime              as AllDay_Weekday_Time
                     , WeekendTime              as AllDay_Weekend_Time
                     , WeekdayDeadline          as AllDay_Weekday_Deadline
                     , WeekendDeadline          as AllDay_Weekend_Deadline
                     , MemberWeekdayTime        as AllDay_Member_Weekday_Time
                     , MemberWeekendTime        as AllDay_Member_Weekend_Time
                     , MemberWeekdayDeadline    as AllDay_Member_Weekday_Deadline
                     , MemberWeekendDeadline    as AllDay_Member_Weekend_Deadline
                     , AllDayWeekdayPrice       as AllDay_Weekday_Price
                     , AllDayWeekendPrice       as AllDay_Weekend_Price
                     , MemberAllDayWeekdayPrice as AllDay_Member_Weekday_Price
                     , MemberAllDayWeekendPrice as AllDay_Member_Weekend_Price
                from AllDayInfo
                         join AllDayPrice on AllDayInfo.AccomIdx = AllDayPrice.AccomIdx
                         join Room on AllDayInfo.AccomIdx = Room.AccomIdx and AllDayPrice.RoomIdx = Room.RoomIdx
                where AllDayInfo.AccomIdx = ?;
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

// 특정 숙소의 요금 정보 탭 중 숙박 파트
function getAccomSellerInfo($AccomIdx)
{

    $pdo = pdoSqlConnect();
    $query = "
                select SellerName,
                       AccomIdx,
                       BusinessName,
                       BusinessAddress,
                       SellerEmail,
                       SellerContact,
                       SellerCode
                from AccommodationSeller
                where AccomIdx = ?;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

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

// (대실에서의 실제적인 연박 체크)특정 방에 해당 일에 연박을 하고 있는 사람이 있는지 체크한다. => 합치는 것 필요
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

// (호텔용) 연박체크하는 함수
function hotelLongStayChecker($AccomIdx, $RoomIdx, $startAt, $dayDiff)
{
    // 9~12 연박이면, 퇴실일 기준 10일 자정, 11일 자정, 12일 자정에 숙박이 없어야한다.
    $afterStartAt = date("Y-m-d", strtotime($startAt . " +1 day")); // 10;

    for ($i = 0; $i < $dayDiff; $i++) { // 0, 1, 2

        // 1. 연박 기간동안 예약이 있으면 안된다.
        $tmp_endAt = date("Y-m-d", strtotime($afterStartAt . " +" . $i . "day")); // 10, 11, 12

        if (!checkAllDayReserve($AccomIdx, $RoomIdx, $tmp_endAt)) { // 10, 11,12 숙박 검사
            return false;       // 예약이 있으면
        }
        return true;
    }
}

// 특정 그룹 검색 인원에 맞는 모든 모텔의 idx를 가져온다.
function getMotelAccomList($motelGroupIdx, $adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select distinct Accommodation.AccomIdx
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

// 특정 그룹 검색 인원에 맞는 모든 호텔의 idx를 가져온다.
function getHotelAccomList($hotelGroupIdx, $adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select distinct Accommodation.AccomIdx
                from Accommodation
                         join HotelGroup on HotelGroup.RegionIdx = Accommodation.RegionIdx
                         join Room on Room.AccomIdx = Accommodation.AccomIdx
                where HotelGroupIdx = ?
                  and AccomType = 'H'
                  and ? + ? <= MaxCapacity
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$hotelGroupIdx, $adult, $child]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 특정 그룹 검색 인원에 맞는 <<모든 모텔>>의 방을 다 불러온다.
function getAllMotelRoomList($motelGroupIdx, $adult, $child)
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

// 특정 그룹 검색 인원에 맞는 <<특정 모텔>>의 방을 다 불러온다.
function getMotelRoomList($motelGroupIdx, $accomIdx,$adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select Accommodation.AccomIdx, RoomIdx, RoomName
                from Region join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                join MotelGroup on MotelGroup.RegionIdx = Accommodation.RegionIdx
                join Room on Room.AccomIdx = Accommodation.AccomIdx
                where MotelGroupIdx = ?
                  and Accommodation.AccomIdx = ?
                  and AccomType = 'M'
                and ? + ? <= MaxCapacity;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$motelGroupIdx, $accomIdx, $adult, $child]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 특정 그룹 검색 인원에 맞는 <<모든 호텔>>의 방을 다 불러온다.
function getAllHotelRoomList($hotelGroupIdx, $adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select Accommodation.AccomIdx, RoomIdx
                from Region
                         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                         join HotelGroup on HotelGroup.RegionIdx = Accommodation.RegionIdx
                         join Room on Room.AccomIdx = Accommodation.AccomIdx
                where HotelGroupIdx = ?
                  and AccomType = 'H'
                  and ? + ? <= MaxCapacity;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$hotelGroupIdx, $adult, $child]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 특정 그룹 검색 인원에 맞는 <<특정 호텔>>의 방을 다 불러온다.
function getHotelRoomList($hotelGroupIdx, $accomIdx, $adult, $child)
{
    $pdo = pdoSqlConnect();
    $query = "
                select Accommodation.AccomIdx, RoomIdx
                from Region
                         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
                         join HotelGroup on HotelGroup.RegionIdx = Accommodation.RegionIdx
                         join Room on Room.AccomIdx = Accommodation.AccomIdx
                where HotelGroupIdx = ?
                  and Room.AccomIdx = ?
                  and AccomType = 'H'
                  and ? + ? <= MaxCapacity;
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$hotelGroupIdx, $accomIdx, $adult, $child]);
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
                select WeekdayDeadline, WeekendDeadline
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
        return $res[0]['WeekendDeadline'];
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

// 호텔의 지역그룹을 다 가져온다.
function getHotelGroupList()
{
    $pdo = pdoSqlConnect();
    $query = "  
                select distinct cityIdx, cityName, HotelGroup.HotelGroupIdx, HotelGroupName
                from Region
                         join HotelGroup on Region.RegionIdx = HotelGroup.RegionIdx
                         join HotelGroupName on HotelGroup.HotelGroupIdx = HotelGroupName.HotelGroupIdx
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

// [Motel]
// 해당 지역의 조건에 맞는 모든 모텔 방들의 정보를 그룹핑한다.
function getMotels($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child)
{
    // 0.  결과배열 선언 및 초기화
    $motels = array();

    // 1.인원 조건에 맞는 모텔의 객실에 대한 정보를 가져온다.
    $motelRoomInfo = getAllMotelRoomsInfo($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child);

    // 2. 조건을 만족하는 객실의  총 개수 => 구성이 어떤지는 모름
    $numOfTotalRoom = count($motelRoomInfo);

    // 조건에 맞는 방이 하나도 없는 경우 => 빈 문자열 리턴
    if ($numOfTotalRoom == 0) return '';

    // 3. 조건을 만족하는 숙소의 AccomIdx 리스트, 개수
    $AccomList = getMotelAccomList($motelGroupIdx, $adult, $child);
    $numOfAccom = count($AccomList);

    // 4. 조건에 맞는 객실이 숙소마다 몇 개인지 파악한다. => 문자열에서 연속된 같은 숫자 세기
    // AccomList와 대응하는 방들의 개수가 채워진다. => AccomList의 n번째 Accomidx는 roomCount의 n번째 값만큼 방 개수를 가진다.
    $numOfRoomByAccom = array();
    $count = 1;

    // 1개면 루프를 못 돈다.
    if ($numOfAccom == 1) {
        $numOfRoomByAccom[0] = 1;
    } else {

        for ($i = 0; $i < $numOfTotalRoom - 1; $i++) { //9번 돌거임,8까지
            if ($motelRoomInfo[$i]['AccomIdx'] == $motelRoomInfo[$i + 1]['AccomIdx']) {
                // 뒤에 AccomIdx와 같은 경우
                $count++;
            } else {
                // 뒤에 AccomIdx문자와 다른 경우
                $numOfRoomByAccom[] = $count;
                $count = 1;
            }
            // 마지막엔 강제로 넣어준다.
            if ($i == $numOfTotalRoom - 2)
                $numOfRoomByAccom[] = $count;
        }
    }

    // 5. 각 숙소마다 그룹핑한다.
    $roomCount = 0;

    // 숙소 개수만큼 돈다.
    for ($i = 0; $i < $numOfAccom; $i++) {

        // 숙소당 처음 판단하는 값인지 판단.
        $isFirstForPartTime = true;
        $isFirstForAllDay = true;

        // AccomIdx 추가
        $motels[$i]['AccomIdx'] = $motelRoomInfo[$roomCount]['AccomIdx'];
        $motels[$i]['AccomName'] = getAccomInfo($motels[$i]['AccomIdx'])['AccomName'];
        $motels[$i]['AccomThumbnailUrl'] = getAccomInfo($motels[$i]['AccomIdx'])['AccomThumbnailUrl'];
        $motels[$i]['AvgRating'] = getAccomInfo($motels[$i]['AccomIdx'])['avgRating'];
        $motels[$i]['NumOfReview'] = getAccomInfo($motels[$i]['AccomIdx'])['numOfReview'];
        $motels[$i]['NumOfUserPick'] = getUserPick($motels[$i]['AccomIdx']);
        $motels[$i]['GuideFromStation'] = getAccomInfo($motels[$i]['AccomIdx'])['GuideFromStation'];

        // 숙소당 방 개수맘큼 돈다.
        for ($j = 0; $j < $numOfRoomByAccom[$i]; $j++) {

            /* * * * * * * * * * * * *
             *  1. 대실 가능여부 체크   *
             * * * * * * * * * * * * */

            // 1-1.대실이 가능한 경우
            if ($motelRoomInfo[$roomCount]['IsPartTimeAvailable'] == 'T') {

                // 첫 번째 대실 가능한 숙소의 경우 가격 비교 과정 X
                if ($isFirstForPartTime) {
                    // 첫 번째는 그냥 할당.
                    $isFirstForPartTime = false;
                    $motels[$i]['IsPartTimeAvailable'] = $motelRoomInfo[$roomCount]['IsPartTimeAvailable'];
                    $motels[$i]['PartTimePrice'] = $motelRoomInfo[$roomCount]['PartTimePrice'];
                    $motels[$i]['PartTimeHour'] = $motelRoomInfo[$roomCount]['PartTimeHour'];
                }

                // 두 번째 대실 가능한 숙소부터는 가격 비교 시작
                else {
                    // 새로운 대실비 < 기존의 대실비 ===> 결과 배열에 할당
                    if ($motelRoomInfo[$roomCount]['PartTimePrice'] < $motels[$i]['PartTimePrice']) {
                        $motels[$i]['PartTimePrice'] = $motelRoomInfo[$roomCount]['PartTimePrice'];
                        $motels[$i]['PartTimeHour'] = $motelRoomInfo[$roomCount]['PartTimeHour'];
                    }
                }
            }

            /* * * * * * * * * * * * *
             *  2. 숙박 가능여부 체크   *
             * * * * * * * * * * * * */

            // 2-1. 숙박이 가능한 경우
            if ($motelRoomInfo[$roomCount]['IsAllDayAvailable'] == 'T') {

                // 첫 번째 숙박 가능한 숙소의 경우 가격 비교 과정 X
                if ($isFirstForAllDay) {
                    // 첫 번째는 그냥 할당.
                    $isFirstForAllDay = false;
                    $motels[$i]['IsAllDayAvailable'] = $motelRoomInfo[$roomCount]['IsAllDayAvailable'];
                    $motels[$i]['AvailableAllDayCheckIn'] = $motelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                    $motels[$i]['AllDayPrice'] = $motelRoomInfo[$roomCount]['AllDayPrice'];
                }
                // 두 번째 숙박 가능한 숙소부터는 가격 비교 시작
                else {
                    // 새로운 숙박비 < 기존의 숙박비 ===> 결과 배열에 할당
                    if ($motelRoomInfo[$roomCount]['AllDayPrice'] < $motels[$i]['AllDayPrice']) {
                        $motels[$i]['AvailableAllDayCheckIn'] = $motelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                        $motels[$i]['AllDayPrice'] = $motelRoomInfo[$roomCount]['AllDayPrice'];
                    }
                }
            }

            // 다음 방 체크
            $roomCount++;
        }

        // 모두 대실 불가능 경우 ==> 판단이 한 번도 일어나지 않은 경우
        if ($isFirstForPartTime) {
            $motels[$i]['IsPartTimeAvailable'] = 'F';
        }

        // 모두 숙박 불가능 경우 ==> 판단이 한 번도 일어나지 않은 경우
        if ($isFirstForAllDay) {
            $motels[$i]['IsAllDayAvailable'] = 'F';
        }


        $accomTag = getAccomTag($motels[$i]['AccomIdx']);
        if(empty($accomTag)){
            $motels[$i]['AccomTag'] = array();
        }
        else{
            $motels[$i]['AccomTag'] = $accomTag;
        }



    }

    return $motels;
}

// [Motel]
// 해당 지역의 조건에 맞는 모든 모텔들 모든 방 정보 가져오기
function getAllMotelRoomsInfo($isMember, $startAt, $endAt, $motelGroupIdx, $adult, $child)
{

    // 전날 변수 저장
    $beforeStartAt = date("Y-m-d", strtotime($startAt." -1 day"));
    $beforeEndAt = date("Y-m-d", strtotime($endAt." -1 day"));


    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 해당 지역 그룹의 모든 방 다 가져오기
    $motelRoomlist = getAllMotelRoomList($motelGroupIdx, $adult, $child);



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
    }

    return $motelRoomlist;
}

// [Motel]
// 해당 지역의 조건에 맞는 <<특정>> 모텔의 모든 방 정보 가져오기
function getMotelRoomsInfo($isMember, $startAt, $endAt, $adult, $child, $motelGroupIdx, $AccomIdx){


    // 전날 변수 저장
    $beforeStartAt = date("Y-m-d", strtotime($startAt." -1 day"));
    $beforeEndAt = date("Y-m-d", strtotime($endAt." -1 day"));


    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 해당 지역 그룹의 특정 숙소의 모든 방 다 가져오기
    $motelRoomlist = getMotelRoomList($motelGroupIdx, $AccomIdx, $adult, $child);



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

//        $accomTag = getAccomTag($nowAccomIdx);
//        if(empty($accomTag)){
//            $motelRoomlist[$i]['AccomTag'] = array();
//        }
//        else{
//            $motelRoomlist[$i]['AccomTag'] = $accomTag;
//        }

    }

    return $motelRoomlist;
}

// [Motel]
// 해당 지역의 조건에 맞는 <<특정 모텔>>의 <<특정 객실>> 정보 가져오기
function getMotelRoomDetail($isMember, $startAt, $endAt, $AccomIdx, $RoomIdx){


    // 전날 변수 저장
    $beforeStartAt = date("Y-m-d", strtotime($startAt." -1 day"));
    $beforeEndAt = date("Y-m-d", strtotime($endAt." -1 day"));


    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < 1; $i++) {

        // 1. 1박인 경우 => 숙박 + 대실만 가능
        if ($dayDiff == 1) {

            // 1-1.해당 객실의 대실이 가능하다면
            if (checkPartTimeReserve($AccomIdx, $RoomIdx, $startAt, $endAt)) {

                $motelRoomlist[$i]['IsPartTimeAvailable'] = 'T';

                // 이전 날 숙박이 있었는지 체크 후 입실 가능 시간 배정
                if(checkAllDayReserve($AccomIdx, $RoomIdx, $beforeEndAt)){
                    // 이전 날 숙박이 없었다면 => '10:00:00'
                    $motelRoomlist[$i]['AvailablePartTimeCheckIn'] = '10:00:00';
                }else{
                    // 이전 날 숙박이 있었다면 => (이전 숙박 퇴실 시간 + 1시간) 대실 입실 가능 시간
                    $motelRoomlist[$i]['AvailablePartTimeCheckIn'] =  date("H:i:s", strtotime(getYesterdayAllDayReservation($AccomIdx, $RoomIdx, $beforeEndAt)[0]['CheckOutDate']." +1hours"));
                }

                // 대실 당일 숙박예약이 있는지 체크 후 퇴실 가능 시간 배정
                if(checkAllDayReserve($AccomIdx, $RoomIdx, $endAt)){
                    // 대실 당일 숙박 예약이 없는 경우 = > 대실 퇴실 시간 마감까지 가능
                    $motelRoomlist[$i]['AvailablePartTimeDeadline'] = getPartTimeDeadline($AccomIdx, $dayType);
                }
                else{
                    // 대실 당일 숙박 예약이 있는 경우 => 숙박 입실 시간 -1시간 까지 체크 아웃해야함
                    echo $AccomIdx.'/'.$RoomIdx;
                    $motelRoomlist[$i]['AvailablePartTimeDeadline'] = date("H:i:s", strtotime(getTodayAllDayReservation($AccomIdx, $RoomIdx, $startAt, $endAt)[0]['CheckInDate']." -1hours"));
                }

                // 특정 방의 대실 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['PartTimePrice'] = getPartTimePrice($AccomIdx, $RoomIdx, $isMember, $dayType);

                // 특정 방의 대실 이용 시간을 가져 온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['PartTimeHour'] = getPartTimeHour($AccomIdx, $isMember, $dayType);

            }
            else {

                $motelRoomlist[$i]['IsPartTimeAvailable'] = 'F';

                // 안되는 이유 => 이유1. 그 날 자는(?)연박 손님이 있는 경우 / 2. 대실이 이미 있는 경우

                // 1. 그 날 자는(?)연박 손님이 있는 경우
                if(!checkLongDayReserve($AccomIdx, $RoomIdx, $startAt, $endAt)){
                    // 딱히 할게 없네
                }
                else{
                    // 2. 이미 대실이 있어서 안되는 경우 => 그 객실의 대실 체크 인, 아웃 타임 출력
                    $motelRoomlist[$i]['ReservedCheckIn'] = getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $startAt, $endAt)[0]['CheckIn'];
                    $motelRoomlist[$i]['ReservedCheckOut'] = getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $startAt, $endAt)[0]['CheckOut'];
                }

            }

            // 1-2.당일 숙박이 가능한지 체크한다.
            if(checkAllDayReserve($AccomIdx, $RoomIdx, $endAt)){

                // 해당 객실이 숙박이 가능하다면
                // => 1. 당일 대실 예약이 있는 경우, 대실 퇴실 시간 + 1 부터 입실 가능
                // => 2. 당일 대실 예약이 없는 경우, 규정 숙박 입실 시간 부터 가능

                $motelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 당일 대실 예약이 있는지 체크한다
                if(checkPartTimeReserve($AccomIdx, $RoomIdx, $startAt, $endAt)){
                    // 당일 대실이 없는 경우 => 규정 숙박 입실 시간 부터 가능
                    $motelRoomlist[$i]['AvailableAllDayCheckIn'] = getAllDayTime($AccomIdx, $isMember, $dayType);
                }
                else{
                    // 당일 대실이 있는 경우 => (대실 퇴실 시간 + 1 시간) 과 규정 숙박 입실 시간 비교해서 늦은(큰) 시간 부터 입실 가능
                    $todayAvailableAllDayCheckInTime = date("H:i:s", strtotime(getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $startAt, $endAt)[0]['CheckOut']." +1hours"));
                    $rule = getAllDayTime($AccomIdx, $isMember, $dayType);
//                    echo $AccomIdx.'!!'.$RoomIdx.'  ';
//                    echo $todayAvailableAllDayCheckInTime.'zz';
//                    echo $rule;
                    // 비교
                    if($todayAvailableAllDayCheckInTime < $rule)
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $rule;
                    else
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $todayAvailableAllDayCheckInTime;
                }

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($AccomIdx, $RoomIdx, $isMember, $dayType);

            }
            else{
                // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다. / 이유2. 다음날 일찍 대실 예약 손님이 이미 있다.(가정 상황에서의 문제 발생)
                $motelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }
        // 2. 연박인 경우 => 숙박만 가능
        else {
            // 해당 기간에 연박이 가능한지 본다.
            if(checkConsecutiveStayAvailable($AccomIdx, $RoomIdx, $startAt, $dayDiff)){
                // 연박이 가능하다면
                // 해당 객실이 숙박이 가능하다면
                // => 1. 당일 대실 예약이 있는 경우, 대실 퇴실 시간 + 1 부터 입실 가능
                // => 2. 당일 대실 예약이 없는 경우, 규정 숙박 입실 시간 부터 가능

                $motelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 검사에 활용한 임시 퇴실 시간 변수
                $temp_endAt = date("Y-m-d", strtotime($startAt." +1day"));

                // 당일 대실 예약이 있는지 체크한다
                if(checkPartTimeReserve($AccomIdx, $RoomIdx, $startAt, $temp_endAt)){
                    // 당일 대실이 없는 경우 => 규정 숙박 입실 시간 부터 가능
                    $motelRoomlist[$i]['AvailableAllDayCheckIn'] = getAllDayTime($AccomIdx, $isMember, $dayType);
                }
                else{
                    // 당일 대실이 있는 경우 => (대실 퇴실 시간 + 1 시간) 과 규정 숙박 입실 시간 비교해서 늦은(큰) 시간 부터 입실 가능
                    $todayAvailableAllDayCheckInTime = date("H:i:s", strtotime(getPartTimeCheckInOutTime($AccomIdx, $RoomIdx, $startAt, $temp_endAt)[0]['CheckOut']." +1hours"));
                    $rule = getAllDayTime($AccomIdx, $isMember, $dayType);
//                    echo $AccomIdx.'!!'.$RoomIdx.'  ';
//                    echo $todayAvailableAllDayCheckInTime.'zz';
//                    echo $rule;
                    // 비교
                    if($todayAvailableAllDayCheckInTime < $rule)
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $rule;
                    else
                        $motelRoomlist[$i]['AvailableAllDayCheckIn'] = $todayAvailableAllDayCheckInTime;
                }

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($AccomIdx, $RoomIdx, $isMember, $dayType);


            }
            else{
                // 연박이 안되면
                $motelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }

//        $accomTag = getAccomTag($AccomIdx);
//        if(empty($accomTag)){
//            $motelRoomlist[$i]['AccomTag'] = array();
//        }
//        else{
//            $motelRoomlist[$i]['AccomTag'] = $accomTag;
//        }

    }

    return $motelRoomlist[0];
}

// [호텔]
// 해당 지역의 조건에 맞는 모든 호텔 방들의 정보를 그룹핑한다.
function getHotels($isMember, $startAt, $endAt, $hotelGroupIdx, $adult, $child)
{
    // 0.  결과배열 선언 및 초기화
    $hotels = array();

    // 1.인원 조건에 맞는 모텔의 객실에 대한 정보를 가져온다.
    $hotelRoomInfo = getAllHotelRoomsInfo($isMember, $startAt, $endAt, $hotelGroupIdx, $adult, $child);

    // 2. 조건을 만족하는 객실의  총 개수 => 구성이 어떤지는 모름
    $numOfTotalRoom = count($hotelRoomInfo);

    // 조건에 맞는 방이 하나도 없는 경우 => 빈 문자열 리턴
    if ($numOfTotalRoom == 0) return '';

    // 3. 조건을 만족하는 숙소의 AccomIdx 리스트, 개수
    $AccomList = getHotelAccomList($hotelGroupIdx, $adult, $child); // 6, 7
    $numOfAccom = count($AccomList); // 2

    // 4. 조건에 맞는 객실이 숙소마다 몇 개인지 파악한다. => 문자열에서 연속된 같은 숫자 세기
    // AccomList와 대응하는 방들의 개수가 채워진다. => AccomList의 n번째 Accomidx는 roomCount의 n번째 값만큼 방 개수를 가진다.
    $numOfRoomByAccom = array();
    $count = 1;

    // 1개면 루프를 못 돈다.
    if ($numOfAccom == 1) {
        $numOfRoomByAccom[0] = 1;
    } else {

        for ($i = 0; $i < $numOfTotalRoom - 1; $i++) { // 1번 돌거임
            if ($hotelRoomInfo[$i]['AccomIdx'] == $hotelRoomInfo[$i + 1]['AccomIdx']) {
                // 뒤에 AccomIdx와 같은 경우
                $count++;
            } else {
                // 뒤에 AccomIdx문자와 다른 경우
                $numOfRoomByAccom[] = $count;
                $count = 1;
            }
            // 마지막엔 강제로 넣어준다.
            if ($i == $numOfTotalRoom - 2)
                $numOfRoomByAccom[] = $count;
        }
    }

    // 5. 각 숙소마다 그룹핑한다.
    $roomCount = 0;

    // 숙소 개수만큼 돈다.
    for ($i = 0; $i < $numOfAccom; $i++) {

        // 숙소당 처음 판단하는 값인지 판단.
        $isFirstForAllDay = true;

        // AccomIdx 추가
        $hotels[$i]['AccomIdx'] = $hotelRoomInfo[$roomCount]['AccomIdx'];
        $hotels[$i]['AccomName'] = getAccomInfo($hotels[$i]['AccomIdx'])['AccomName'];
        $hotels[$i]['AccomThumbnailUrl'] = getAccomInfo($hotels[$i]['AccomIdx'])['AccomThumbnailUrl'];
        $hotels[$i]['AccomGrade'] = getHotelGrade($hotels[$i]['AccomIdx'])['AccomGrade'];
        $hotels[$i]['Authentication'] = getHotelGrade($hotels[$i]['AccomIdx'])['Authentication'];
        $hotels[$i]['AvgRating'] = getAccomInfo($hotels[$i]['AccomIdx'])['avgRating'];
        $hotels[$i]['NumOfReview'] = getAccomInfo($hotels[$i]['AccomIdx'])['numOfReview'];
        $hotels[$i]['NumOfUserPick'] = getUserPick($hotels[$i]['AccomIdx']);
        $hotels[$i]['GuideFromStation'] = getAccomInfo($hotels[$i]['AccomIdx'])['GuideFromStation'];

        // 숙소당 방 개수맘큼 돈다.
        for ($j = 0; $j < $numOfRoomByAccom[$i]; $j++) {

            /* * * * * * * * * * * * *
             *  2. 숙박 가능여부 체크   *
             * * * * * * * * * * * * */

            // 2-1. 숙박이 가능한 경우
            if ($hotelRoomInfo[$roomCount]['IsAllDayAvailable'] == 'T') {

                // 첫 번째 숙박 가능한 숙소의 경우 가격 비교 과정 X
                if ($isFirstForAllDay) {
                    // 첫 번째는 그냥 할당.
                    $isFirstForAllDay = false;
                    $hotels[$i]['IsAllDayAvailable'] = $hotelRoomInfo[$roomCount]['IsAllDayAvailable'];
                    $hotels[$i]['AvailableAllDayCheckIn'] = $hotelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                    $hotels[$i]['AllDayPrice'] = $hotelRoomInfo[$roomCount]['AllDayPrice'];
                }
                // 두 번째 숙박 가능한 숙소부터는 가격 비교 시작
                else {
                    // 새로운 숙박비 < 기존의 숙박비 ===> 결과 배열에 할당
                    if ($hotelRoomInfo[$roomCount]['AllDayPrice'] < $hotels[$i]['AllDayPrice']) {
                        $hotels[$i]['AvailableAllDayCheckIn'] = $hotelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                        $hotels[$i]['AllDayPrice'] = $hotelRoomInfo[$roomCount]['AllDayPrice'];
                    }
                }
            }

            // 다음 방 체크
            $roomCount++;
        }

        // 모두 숙박 불가능 경우 ==> 판단이 한 번도 일어나지 않은 경우
        if ($isFirstForAllDay) {
            $hotels[$i]['IsAllDayAvailable'] = 'F';
        }

        $accomTag = getAccomTag($hotels[$i]['AccomIdx']);
        if(empty($accomTag)){
            $hotels[$i]['AccomTag'] = array();
        }
        else{
            $hotels[$i]['AccomTag'] = $accomTag;
        }



    }

    return $hotels;
}

// [호텔]
// 해당 지역의 조건에 맞는 모든 호텔들 모든 방 정보 가져오기
function getAllHotelRoomsInfo($isMember, $startAt, $endAt, $hotelGroupIdx, $adult, $child)
{
    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 해당 지역 그룹의 모든 방 다 가져오기
    $hotelRoomlist = getAllHotelRoomList($hotelGroupIdx, $adult, $child);

    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < count($hotelRoomlist); $i++) {

        $nowAccomIdx = $hotelRoomlist[$i]['AccomIdx'];
        $nowRoomIdx = $hotelRoomlist[$i]['RoomIdx'];

        // 1. 1박인 경우
        if ($dayDiff == 1) {

            // 1-1.당일 숙박이 가능한지 체크한다.
            if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){

                // 해당 객실이 숙박이 가능하다면
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AvailableAllDayCheckIn']  = getAllDayTime($nowAccomIdx, $isMember, $dayType);

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);
            }
            else{
                // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다.
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }
        // 2. 연박인 경우
        else {
            // 해당 기간에 연박이 가능한지 본다.
            if(hotelLongStayChecker($nowAccomIdx, $nowRoomIdx, $startAt, $dayDiff)){
                // 연박이 가능하다면
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AvailableAllDayCheckIn']  = getAllDayTime($nowAccomIdx, $isMember, $dayType);

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);
            }
            else{
                // 연박이 안되면
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }
    }

    return $hotelRoomlist;
}

// [호텔]
//  해당 지역의 조건에 맞는 <<특정>> 호텔의 모든 방 정보 가져오기
function getHotelRoomsInfo($isMember, $startAt, $endAt, $adult, $child, $hotelGroupIdx, $AccomIdx)
{
    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 해당 지역 그룹의 모든 방 다 가져오기
    $hotelRoomlist = getHotelRoomList($hotelGroupIdx, $AccomIdx, $adult, $child);

    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < count($hotelRoomlist); $i++) {

        $nowAccomIdx = $hotelRoomlist[$i]['AccomIdx'];
        $nowRoomIdx = $hotelRoomlist[$i]['RoomIdx'];

        // 1. 1박인 경우
        if ($dayDiff == 1) {

            // 1-1.당일 숙박이 가능한지 체크한다.
            if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){

                // 해당 객실이 숙박이 가능하다면
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AvailableAllDayCheckIn']  = getAllDayTime($nowAccomIdx, $isMember, $dayType);

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);
            }
            else{
                // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다.
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }
        // 2. 연박인 경우
        else {
            // 해당 기간에 연박이 가능한지 본다.
            if(hotelLongStayChecker($nowAccomIdx, $nowRoomIdx, $startAt, $dayDiff)){
                // 연박이 가능하다면
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'T';

                // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AvailableAllDayCheckIn']  = getAllDayTime($nowAccomIdx, $isMember, $dayType);

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $hotelRoomlist[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);
            }
            else{
                // 연박이 안되면
                $hotelRoomlist[$i]['IsAllDayAvailable'] = 'F';
            }
        }
    }

    return $hotelRoomlist;
}

// [호텔]
// 해당 지역의 조건에 맞는 <<특정 호텔>>의 <<특정 객실>> 정보 가져오기
function getHotelRoomDetail($isMember, $startAt, $endAt, $AccomIdx, $RoomIdx){

    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;



    // 1. 1박인 경우
    if ($dayDiff == 1) {

        // 1-1.당일 숙박이 가능한지 체크한다.
        if (checkAllDayReserve($AccomIdx, $RoomIdx, $endAt)) {

            // 해당 객실이 숙박이 가능하다면
            $hotelRoom['IsAllDayAvailable'] = 'T';

            // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
            $hotelRoom['AvailableAllDayCheckIn'] = getAllDayTime($AccomIdx, $isMember, $dayType);

            // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
            $hotelRoom['AllDayPrice'] = getAllDayPrice($AccomIdx, $RoomIdx, $isMember, $dayType);
        } else {
            // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다.
            $hotelRoom['IsAllDayAvailable'] = 'F';
        }
    } // 2. 연박인 경우
    else {
        // 해당 기간에 연박이 가능한지 본다.
        if (hotelLongStayChecker($AccomIdx, $RoomIdx, $startAt, $dayDiff)) {
            // 연박이 가능하다면
            $hotelRoom['IsAllDayAvailable'] = 'T';

            // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
            $hotelRoom['AvailableAllDayCheckIn'] = getAllDayTime($AccomIdx, $isMember, $dayType);

            // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
            $hotelRoom['AllDayPrice'] = getAllDayPrice($AccomIdx, $RoomIdx, $isMember, $dayType);
        } else {
            // 연박이 안되면
            $hotelRoom['IsAllDayAvailable'] = 'F';
        }
    }


    return $hotelRoom;
}

// [종합]해당 지역의 조건에 맞는 <<특정 숙소>>의 <<특정 객실>> 정보 가져오기
function getRoomDetail($isMember, $startAt, $endAt, $AccomIdx, $RoomIdx){

    // 모텔인 경우
    if(getTypeOfAccom($AccomIdx) == 'M')
        $res = getMotelRoomDetail($isMember, $startAt, $endAt, $AccomIdx, $RoomIdx);
    // 호텔인 경우
    else
        $res = getHotelRoomDetail($isMember, $startAt, $endAt, $AccomIdx, $RoomIdx);


    return $res;
}

// 하단 네비게이션 바 지역별 버튼 => 지역리스트 출력
function getAreas(){
    $pdo = pdoSqlConnect();
    $query = "
                select distinct cityIdx, cityName, MotelGroup.MotelGroupIdx as GroupIdx , MotelGroupName as GroupName
                from Region
                 join MotelGroup on MotelGroup.RegionIdx = Region.RegionIdx
                 join MotelGroupName on MotelGroupName.MotelGroupIdx = MotelGroup.MotelGroupIdx
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 하단 네비게이션 바 지역별 버튼 => 지역리스트 출력 => 해당 지역 숙소(종류 상관없이) 출력
function getAccomByArea($groupIdx, $isMember, $startAt, $endAt, $adult, $child){

    // 1. 해당 지역의 모텔과 호텔의 모든 룸을 가져온다.
    $motelListByArea = getRoomListByArea($groupIdx, $adult, $child, 'M');
    $hotelListByArea = getRoomListByArea($groupIdx, $adult, $child, 'H');

    $motels = array();
    $hotels = array();

    if(count($motelListByArea))
        $motels = getMotelsByArea($groupIdx, $isMember, $startAt, $endAt, $adult, $child, $motelListByArea);
    if(count($hotelListByArea))
        $hotels = getHotelsByArea($groupIdx, $isMember, $startAt, $endAt, $adult, $child, $hotelListByArea);

    $res['motel'] = $motels;
    $res['hotel'] = $hotels;

    return $res;
}

// 통합 지역에서의 모텔 객실 정보들을 그룹핑한다.
function getMotelsByArea($groupIdx, $isMember, $startAt, $endAt, $adult, $child, $motelListByArea)
{
    // 0.  결과배열 선언 및 초기화
    $motels = array();

    // 1.인원 조건에 맞는 모텔의 객실에 대한 정보를 가져온다.
    $motelRoomInfo = getMotelRoomsInfoByArea($isMember, $startAt, $endAt, $motelListByArea);

    // 2. 조건을 만족하는 객실의  총 개수 => 구성이 어떤지는 모름
    $numOfTotalRoom = count($motelRoomInfo);

    // 조건에 맞는 방이 하나도 없는 경우 => 빈 문자열 리턴
    if ($numOfTotalRoom == 0) return '';

    // 3. 조건을 만족하는 숙소의 AccomIdx 리스트, 개수
    $AccomList = getAccomListByArea($groupIdx, $adult, $child, 'M');
    $numOfAccom = count($AccomList);

    // 4. 조건에 맞는 객실이 숙소마다 몇 개인지 파악한다. => 문자열에서 연속된 같은 숫자 세기
    // AccomList와 대응하는 방들의 개수가 채워진다. => AccomList의 n번째 Accomidx는 roomCount의 n번째 값만큼 방 개수를 가진다.
    $numOfRoomByAccom = array();
    $count = 1;

    // 1개면 루프를 못 돈다.
    if ($numOfAccom == 1) {
        $numOfRoomByAccom[0] = 1;
    } else {
        for ($i = 0; $i < $numOfTotalRoom - 1; $i++) { //9번 돌거임,8까지
            if ($motelRoomInfo[$i]['AccomIdx'] == $motelRoomInfo[$i + 1]['AccomIdx']) {
                // 뒤에 AccomIdx와 같은 경우
                $count++;
            } else {
                // 뒤에 AccomIdx문자와 다른 경우
                $numOfRoomByAccom[] = $count;
                $count = 1;
            }
            // 마지막엔 강제로 넣어준다.
            if ($i == $numOfTotalRoom - 2)
                $numOfRoomByAccom[] = $count;
        }
    }

    // 5. 각 숙소마다 그룹핑한다.
    $roomCount = 0;

    // 숙소 개수만큼 돈다.
    for ($i = 0; $i < $numOfAccom; $i++) {

        // 숙소당 처음 판단하는 값인지 판단.
        $isFirstForPartTime = true;
        $isFirstForAllDay = true;

        // AccomIdx 추가
        $motels[$i]['AccomIdx'] = $motelRoomInfo[$roomCount]['AccomIdx'];
        $motels[$i]['AccomType'] = getAccomInfo($motels[$i]['AccomIdx'])['AccomType'];
        $motels[$i]['AccomName'] = getAccomInfo($motels[$i]['AccomIdx'])['AccomName'];
        $motels[$i]['AccomThumbnailUrl'] = getAccomInfo($motels[$i]['AccomIdx'])['AccomThumbnailUrl'];
        $motels[$i]['AvgRating'] = getAccomInfo($motels[$i]['AccomIdx'])['avgRating'];
        $motels[$i]['NumOfReview'] = getAccomInfo($motels[$i]['AccomIdx'])['numOfReview'];
        $motels[$i]['NumOfUserPick'] = getUserPick($motels[$i]['AccomIdx']);
        $motels[$i]['GuideFromStation'] = getAccomInfo($motels[$i]['AccomIdx'])['GuideFromStation'];

        // 숙소당 방 개수맘큼 돈다.
        for ($j = 0; $j < $numOfRoomByAccom[$i]; $j++) {

            /* * * * * * * * * * * * *
             *  1. 대실 가능여부 체크   *
             * * * * * * * * * * * * */

            // 1-1.대실이 가능한 경우
            if ($motelRoomInfo[$roomCount]['IsPartTimeAvailable'] == 'T') {

                // 첫 번째 대실 가능한 숙소의 경우 가격 비교 과정 X
                if ($isFirstForPartTime) {
                    // 첫 번째는 그냥 할당.
                    $isFirstForPartTime = false;
                    $motels[$i]['IsPartTimeAvailable'] = $motelRoomInfo[$roomCount]['IsPartTimeAvailable'];
                    $motels[$i]['PartTimePrice'] = $motelRoomInfo[$roomCount]['PartTimePrice'];
                    $motels[$i]['PartTimeHour'] = $motelRoomInfo[$roomCount]['PartTimeHour'];
                }

                // 두 번째 대실 가능한 숙소부터는 가격 비교 시작
                else {
                    // 새로운 대실비 < 기존의 대실비 ===> 결과 배열에 할당
                    if ($motelRoomInfo[$roomCount]['PartTimePrice'] < $motels[$i]['PartTimePrice']) {
                        $motels[$i]['PartTimePrice'] = $motelRoomInfo[$roomCount]['PartTimePrice'];
                        $motels[$i]['PartTimeHour'] = $motelRoomInfo[$roomCount]['PartTimeHour'];
                    }
                }
            }

            /* * * * * * * * * * * * *
             *  2. 숙박 가능여부 체크   *
             * * * * * * * * * * * * */

            // 2-1. 숙박이 가능한 경우
            if ($motelRoomInfo[$roomCount]['IsAllDayAvailable'] == 'T') {

                // 첫 번째 숙박 가능한 숙소의 경우 가격 비교 과정 X
                if ($isFirstForAllDay) {
                    // 첫 번째는 그냥 할당.
                    $isFirstForAllDay = false;
                    $motels[$i]['IsAllDayAvailable'] = $motelRoomInfo[$roomCount]['IsAllDayAvailable'];
                    $motels[$i]['AvailableAllDayCheckIn'] = $motelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                    $motels[$i]['AllDayPrice'] = $motelRoomInfo[$roomCount]['AllDayPrice'];
                }
                // 두 번째 숙박 가능한 숙소부터는 가격 비교 시작
                else {
                    // 새로운 숙박비 < 기존의 숙박비 ===> 결과 배열에 할당
                    if ($motelRoomInfo[$roomCount]['AllDayPrice'] < $motels[$i]['AllDayPrice']) {
                        $motels[$i]['AvailableAllDayCheckIn'] = $motelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                        $motels[$i]['AllDayPrice'] = $motelRoomInfo[$roomCount]['AllDayPrice'];
                    }
                }
            }

            // 다음 방 체크
            $roomCount++;
        }

        // 모두 대실 불가능 경우 ==> 판단이 한 번도 일어나지 않은 경우
        if ($isFirstForPartTime) {
            $motels[$i]['IsPartTimeAvailable'] = 'F';
        }

        // 모두 숙박 불가능 경우 ==> 판단이 한 번도 일어나지 않은 경우
        if ($isFirstForAllDay) {
            $motels[$i]['IsAllDayAvailable'] = 'F';
        }


        $accomTag = getAccomTag($motels[$i]['AccomIdx']);
        if(empty($accomTag)){
            $motels[$i]['AccomTag'] = array();
        }
        else{
            $motels[$i]['AccomTag'] = $accomTag;
        }



    }

    return $motels;
}

// 통합 지역에서의 호텔 객실 정보들을 그룹핑한다.
function getHotelsByArea($groupIdx, $isMember, $startAt, $endAt, $adult, $child, $hotelListByArea)
{
    // 0.  결과배열 선언 및 초기화
    $hotels = array();

    // 1.인원 조건에 맞는 모텔의 객실에 대한 정보를 가져온다.
    $hotelRoomInfo = getHotelRoomsInfoByArea($isMember, $startAt, $endAt, $hotelListByArea);

    // 2. 조건을 만족하는 객실의  총 개수 => 구성이 어떤지는 모름
    $numOfTotalRoom = count($hotelRoomInfo);

    // 조건에 맞는 방이 하나도 없는 경우 => 빈 문자열 리턴
    if ($numOfTotalRoom == 0) return '';

    // 3. 조건을 만족하는 숙소의 AccomIdx 리스트, 개수
    $AccomList = getAccomListByArea($groupIdx, $adult, $child, 'H'); // 6, 7
    $numOfAccom = count($AccomList); // 2


    // 4. 조건에 맞는 객실이 숙소마다 몇 개인지 파악한다. => 문자열에서 연속된 같은 숫자 세기
    // AccomList와 대응하는 방들의 개수가 채워진다. => AccomList의 n번째 Accomidx는 roomCount의 n번째 값만큼 방 개수를 가진다.
    $numOfRoomByAccom = array();
    $count = 1;

    // 1개면 루프를 못 돈다. => 할당이 안됨
    if ($numOfAccom == 1) {
        $numOfRoomByAccom[0] = 1;
    } else {


        for ($i = 0; $i < $numOfTotalRoom - 1; $i++) { // 1번 돌거임
            if ($hotelRoomInfo[$i]['AccomIdx'] == $hotelRoomInfo[$i + 1]['AccomIdx']) {
                // 뒤에 AccomIdx와 같은 경우
                $count++;
            } else {
                // 뒤에 AccomIdx문자와 다른 경우
                $numOfRoomByAccom[] = $count;
                $count = 1;
            }
            // 마지막엔 강제로 넣어준다.
            if ($i == $numOfTotalRoom - 2)
                $numOfRoomByAccom[] = $count;
        }
    }
    // 5. 각 숙소마다 그룹핑한다.
    $roomCount = 0;

    // 숙소 개수만큼 돈다.
    for ($i = 0; $i < $numOfAccom; $i++) {

        // 숙소당 처음 판단하는 값인지 판단.
        $isFirstForAllDay = true;

        // AccomIdx 추가
        $hotels[$i]['AccomIdx'] = $hotelRoomInfo[$roomCount]['AccomIdx'];
        $hotels[$i]['AccomType'] = getAccomInfo($hotels[$i]['AccomIdx'])['AccomType'];
        $hotels[$i]['AccomName'] = getAccomInfo($hotels[$i]['AccomIdx'])['AccomName'];
        $hotels[$i]['AccomThumbnailUrl'] = getAccomInfo($hotels[$i]['AccomIdx'])['AccomThumbnailUrl'];
        $hotels[$i]['AccomGrade'] = getHotelGrade($hotels[$i]['AccomIdx'])['AccomGrade'];
        $hotels[$i]['Authentication'] = getHotelGrade($hotels[$i]['AccomIdx'])['Authentication'];
        $hotels[$i]['AvgRating'] = getAccomInfo($hotels[$i]['AccomIdx'])['avgRating'];
        $hotels[$i]['NumOfReview'] = getAccomInfo($hotels[$i]['AccomIdx'])['numOfReview'];
        $hotels[$i]['NumOfUserPick'] = getUserPick($hotels[$i]['AccomIdx']);
        $hotels[$i]['GuideFromStation'] = getAccomInfo($hotels[$i]['AccomIdx'])['GuideFromStation'];

        // 숙소당 방 개수맘큼 돈다.
        for ($j = 0; $j < $numOfRoomByAccom[$i]; $j++) {

            /* * * * * * * * * * * * *
             *  2. 숙박 가능여부 체크   *
             * * * * * * * * * * * * */

            // 2-1. 숙박이 가능한 경우
            if ($hotelRoomInfo[$roomCount]['IsAllDayAvailable'] == 'T') {

                // 첫 번째 숙박 가능한 숙소의 경우 가격 비교 과정 X
                if ($isFirstForAllDay) {
                    // 첫 번째는 그냥 할당.
                    $isFirstForAllDay = false;
                    $hotels[$i]['IsAllDayAvailable'] = $hotelRoomInfo[$roomCount]['IsAllDayAvailable'];
                    $hotels[$i]['AvailableAllDayCheckIn'] = $hotelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                    $hotels[$i]['AllDayPrice'] = $hotelRoomInfo[$roomCount]['AllDayPrice'];
                }
                // 두 번째 숙박 가능한 숙소부터는 가격 비교 시작
                else {
                    // 새로운 숙박비 < 기존의 숙박비 ===> 결과 배열에 할당
                    if ($hotelRoomInfo[$roomCount]['AllDayPrice'] < $hotels[$i]['AllDayPrice']) {
                        $hotels[$i]['AvailableAllDayCheckIn'] = $hotelRoomInfo[$roomCount]['AvailableAllDayCheckIn'];
                        $hotels[$i]['AllDayPrice'] = $hotelRoomInfo[$roomCount]['AllDayPrice'];
                    }
                }
            }

            // 다음 방 체크
            $roomCount++;
        }

        // 모두 숙박 불가능 경우 ==> 판단이 한 번도 일어나지 않은 경우
        if ($isFirstForAllDay) {
            $hotels[$i]['IsAllDayAvailable'] = 'F';
        }

        $accomTag = getAccomTag($hotels[$i]['AccomIdx']);
        if(empty($accomTag)){
            $hotels[$i]['AccomTag'] = array();
        }
        else{
            $hotels[$i]['AccomTag'] = $accomTag;
        }



    }

    return $hotels;
}

// 통합 지역에서 받아온 모텔들의 객실 정보를 가져온다.
function getMotelRoomsInfoByArea($isMember, $startAt, $endAt, $motelListByArea)
{

    // 전날 변수 저장
    $beforeStartAt = date("Y-m-d", strtotime($startAt." -1 day"));
    $beforeEndAt = date("Y-m-d", strtotime($endAt." -1 day"));


    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < count($motelListByArea); $i++) {

        $nowAccomIdx = $motelListByArea[$i]['AccomIdx'];
        $nowRoomIdx = $motelListByArea[$i]['RoomIdx'];

        // 1. 1박인 경우 => 숙박 + 대실만 가능
        if ($dayDiff == 1) {

            // 1-1.해당 객실의 대실이 가능하다면
            if (checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)) {

                $motelListByArea[$i]['IsPartTimeAvailable'] = 'T';

                // 이전 날 숙박이 있었는지 체크 후 입실 가능 시간 배정
                if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $beforeEndAt)){
                    // 이전 날 숙박이 없었다면 => '10:00:00'
                    $motelListByArea[$i]['AvailablePartTimeCheckIn'] = '10:00:00';
                }else{
                    // 이전 날 숙박이 있었다면 => (이전 숙박 퇴실 시간 + 1시간) 대실 입실 가능 시간
                    $motelListByArea[$i]['AvailablePartTimeCheckIn'] =  date("H:i:s", strtotime(getYesterdayAllDayReservation($nowAccomIdx, $nowRoomIdx, $beforeEndAt)[0]['CheckOutDate']." +1hours"));
                }

                // 대실 당일 숙박예약이 있는지 체크 후 퇴실 가능 시간 배정
                if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){
                    // 대실 당일 숙박 예약이 없는 경우 = > 대실 퇴실 시간 마감까지 가능
                    $motelListByArea[$i]['AvailablePartTimeDeadline'] = getPartTimeDeadline($nowAccomIdx, $dayType);
                }
                else{
                    // 대실 당일 숙박 예약이 있는 경우 => 숙박 입실 시간 -1시간 까지 체크 아웃해야함
                    echo $nowAccomIdx.'/'.$nowRoomIdx;
                    $motelListByArea[$i]['AvailablePartTimeDeadline'] = date("H:i:s", strtotime(getTodayAllDayReservation($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckInDate']." -1hours"));
                }

                // 특정 방의 대실 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelListByArea[$i]['PartTimePrice'] = getPartTimePrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);

                // 특정 방의 대실 이용 시간을 가져 온다. 회원/비회원 + 주중/주말
                $motelListByArea[$i]['PartTimeHour'] = getPartTimeHour($nowAccomIdx, $isMember, $dayType);

            }
            else {

                $motelListByArea[$i]['IsPartTimeAvailable'] = 'F';

                // 안되는 이유 => 이유1. 그 날 자는(?)연박 손님이 있는 경우 / 2. 대실이 이미 있는 경우

                // 1. 그 날 자는(?)연박 손님이 있는 경우
                if(!checkLongDayReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)){
                    // 딱히 할게 없네
                }
                else{
                    // 2. 이미 대실이 있어서 안되는 경우 => 그 객실의 대실 체크 인, 아웃 타임 출력
                    $motelListByArea[$i]['ReservedCheckIn'] = getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckIn'];
                    $motelListByArea[$i]['ReservedCheckOut'] = getPartTimeCheckInOutTime($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)[0]['CheckOut'];
                }

            }

            // 1-2.당일 숙박이 가능한지 체크한다.
            if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){

                // 해당 객실이 숙박이 가능하다면
                // => 1. 당일 대실 예약이 있는 경우, 대실 퇴실 시간 + 1 부터 입실 가능
                // => 2. 당일 대실 예약이 없는 경우, 규정 숙박 입실 시간 부터 가능

                $motelListByArea[$i]['IsAllDayAvailable'] = 'T';

                // 당일 대실 예약이 있는지 체크한다
                if(checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $endAt)){
                    // 당일 대실이 없는 경우 => 규정 숙박 입실 시간 부터 가능
                    $motelListByArea[$i]['AvailableAllDayCheckIn'] = getAllDayTime($nowAccomIdx, $isMember, $dayType);
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
                        $motelListByArea[$i]['AvailableAllDayCheckIn'] = $rule;
                    else
                        $motelListByArea[$i]['AvailableAllDayCheckIn'] = $todayAvailableAllDayCheckInTime;
                }

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelListByArea[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);

            }
            else{
                // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다. / 이유2. 다음날 일찍 대실 예약 손님이 이미 있다.(가정 상황에서의 문제 발생)
                $motelListByArea[$i]['IsAllDayAvailable'] = 'F';
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

                $motelListByArea[$i]['IsAllDayAvailable'] = 'T';

                // 검사에 활용한 임시 퇴실 시간 변수
                $temp_endAt = date("Y-m-d", strtotime($startAt." +1day"));

                // 당일 대실 예약이 있는지 체크한다
                if(checkPartTimeReserve($nowAccomIdx, $nowRoomIdx, $startAt, $temp_endAt)){
                    // 당일 대실이 없는 경우 => 규정 숙박 입실 시간 부터 가능
                    $motelListByArea[$i]['AvailableAllDayCheckIn'] = getAllDayTime($nowAccomIdx, $isMember, $dayType);
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
                        $motelListByArea[$i]['AvailableAllDayCheckIn'] = $rule;
                    else
                        $motelListByArea[$i]['AvailableAllDayCheckIn'] = $todayAvailableAllDayCheckInTime;
                }

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $motelListByArea[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);


            }
            else{
                // 연박이 안되면
                $motelListByArea[$i]['IsAllDayAvailable'] = 'F';
            }
        }
    }

    return $motelListByArea;
}

// 통합 지역에서 받아온 호텔들의 객실 정보를 가져온다.
function getHotelRoomsInfoByArea($isMember, $startAt, $endAt, $hotelListByArea)
{
    // 평일,주말 판단
    $dayType = getDayType($startAt);

    // 숙박 이용 날짜 차이 구하기
    $dayDiff = (strtotime($endAt) - strtotime($startAt))/60/60/24;

    // 방 마다 돌면서 조건 체크
    for ($i = 0; $i < count($hotelListByArea); $i++) {

        $nowAccomIdx = $hotelListByArea[$i]['AccomIdx'];
        $nowRoomIdx = $hotelListByArea[$i]['RoomIdx'];

        // 1. 1박인 경우
        if ($dayDiff == 1) {

            // 1-1.당일 숙박이 가능한지 체크한다.
            if(checkAllDayReserve($nowAccomIdx, $nowRoomIdx, $endAt)){

                // 해당 객실이 숙박이 가능하다면
                $hotelListByArea[$i]['IsAllDayAvailable'] = 'T';

                // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
                $hotelListByArea[$i]['AvailableAllDayCheckIn']  = getAllDayTime($nowAccomIdx, $isMember, $dayType);

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $hotelListByArea[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);
            }
            else{
                // 해당 객실의 숙박이 가능하지 않다면 => 이유1. 숙박 손님이 있다.
                $hotelListByArea[$i]['IsAllDayAvailable'] = 'F';
            }
        }
        // 2. 연박인 경우
        else {
            // 해당 기간에 연박이 가능한지 본다.
            if(hotelLongStayChecker($nowAccomIdx, $nowRoomIdx, $startAt, $dayDiff)){
                // 연박이 가능하다면
                $hotelListByArea[$i]['IsAllDayAvailable'] = 'T';

                // 특정 방의 입실 가능 시간을 가져온다.회원/비회원 + 주중/주말
                $hotelListByArea[$i]['AvailableAllDayCheckIn']  = getAllDayTime($nowAccomIdx, $isMember, $dayType);

                // 특정 방의 숙박 가격을 가져온다. 회원/비회원 + 주중/주말
                $hotelListByArea[$i]['AllDayPrice'] = getAllDayPrice($nowAccomIdx, $nowRoomIdx, $isMember, $dayType);
            }
            else{
                // 연박이 안되면
                $hotelListByArea[$i]['IsAllDayAvailable'] = 'F';
            }
        }
    }

    return $hotelListByArea;
}

// 통합 지역에서 인원 구성 조건에 맞는 AccomIdx를 가져온다.
function getAccomListByArea($groupIdx, $adult, $child, $AccomType){
    $pdo = pdoSqlConnect();

    // motel 이 지역이 제일 세분화되서 나눠져있기 때문에 통합지역 그룹용으로도 쓴다.
    // 사실 제일 작게 나눈 구역을 모텔이 쓰고 있다고 보는게 맞다.

    $query = "
                select distinct Room.AccomIdx
                from Accommodation
                         join Room on Accommodation.AccomIdx = Room.AccomIdx
                where (RegionIdx) in
                      (select Region.RegionIdx
                       from Region
                                join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                       where MotelGroupIdx = ?)
                  and ? + ? <= MaxCapacity and AccomType = ?

    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$groupIdx, $adult, $child, $AccomType]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 통합 지역에서 인원 구성 조건에 맞는 AccomIdx, RoomIdx를 가져온다.
function getRoomListByArea($groupIdx, $adult, $child, $AccomType){
    $pdo = pdoSqlConnect();

    // motel 이 지역이 제일 세분화되서 나눠져있기 때문에 통합지역 그룹용으로도 쓴다.
    // 사실 제일 작게 나눈 구역을 모텔이 쓰고 있다고 보는게 맞다.

    $query = "
                select Room.AccomIdx, RoomIdx
                from Accommodation
                         join Room on Accommodation.AccomIdx = Room.AccomIdx
                where (RegionIdx) in
                      (select Region.RegionIdx
                       from Region
                                join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
                       where MotelGroupIdx = ?)
                  and ? + ? <= MaxCapacity and AccomType = ?

    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$groupIdx, $adult, $child, $AccomType]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
