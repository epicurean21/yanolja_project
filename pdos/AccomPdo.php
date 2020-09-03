<?php

function testtest_pdo(){
    echo "테스트";
}

function searchMotelByArea($RegionIdx, $startDate, $endDate, $peopleNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.WeekdayTime
        ELSE F1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.PartTimeWeekdayPrice
        ELSE F1.PartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.AllDayWeekdayTime
        ELSE F1.AllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.AllDayWeekdayPrice
        ELSE F1.AllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomCity, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted, RegionGroup.RegionGroupIdx
FROM (Accommodation
    JOIN RegionGroup USING (RegionIdx))) as AR1
JOIN (
SELECT * 
FROM
(
SELECT * 
FROM
(SELECT PartTimeInfo.AccomIdx, PartTimeInfo.WeekdayTime, PartTimeInfo.WeekendTime, 
		PartTimeInfo.MemberWeekdayTime, PartTimeInfo.MemberWeekendTime
FROM PartTimeInfo) as P1 join 
(Select 
		PartTimePrice.AccomIdx as AccomIdx2,min(PartTimeWeekdayPrice) as PartTimeWeekdayPrice, min(PartTimeWeekendPrice) as PartTimeWeekendPrice
	From
		PartTimePrice
	GROUP BY PartTimePrice.AccomIdx) as P2 ON (P1.AccomIdx = P2.AccomIdx2)
GROUP BY P1.AccomIdx, P1.WeekdayTime, P1.WeekendTime, 
		P1.MemberWeekdayTime, P1.MemberWeekendTime) T1
JOIN
(SELECT *
FROM
(
SELECT AllDayInfo.AccomIdx as AccomIdx3, AllDayInfo.WeekdayTime as AllDayWeekdayTime, AllDayInfo.WeekendTime as AllDayWeekendTime, 
		AllDayInfo.MemberWeekdayTime as AllDayMemberWeekdayTime, AllDayInfo.MemberWeekendTime as AllDayMemberWeekendTime
FROM AllDayInfo) as A1 
	join 
(Select 
		AllDayPrice.AccomIdx as AccomIdx4, min(AllDayWeekdayPrice) as AllDayWeekdayPrice, min(AllDayWeekendPrice) as AllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) as A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
GROUP BY A1.AccomIdx3, A1.AllDayWeekdayTime, A1.AllDayWeekendTime, 
		A1.AllDayMemberWeekdayTime, A1.AllDayMemberWeekendTime) T2 ON (T1.AccomIdx = T2.AccomIdx3)) F1
        ON (AR1.AccomIdx = F1.AccomIdx)
 WHERE
  RegionGroupIdx = ?
        AND AR1.AccomType = 'M'
        AND AR1.isDeleted = 'N') AS a1 JOIN
        (SELECT 
        Accommodation.AccomIdx,
            AVG(OverallRating) AS OverallRating,
            COUNT(ReviewIdx) AS ReviewCount
    FROM
        Accommodation
    JOIN AccommodationReview ON (Accommodation.AccomIdx = AccommodationReview.AccomIdx)
    WHERE
        Accommodation.Accomtype = 'M'
            AND AccommodationReview.isDeleted = 'N'
            AND Accommodation.isDeleted = 'N'
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;
";

    $st = $pdo->prepare($query);
    $st->execute([$RegionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function MemberSearchMotelByArea($RegionIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.WeekdayTime
        ELSE F1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.MemberPartTimeWeekdayPrice
        ELSE F1.MemberPartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.MemberAllDayWeekdayTime
        ELSE F1.MemberAllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.MemberAllDayWeekdayPrice
        ELSE F1.MemberAllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomCity, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted, RegionGroup.RegionGroupIdx
FROM (Accommodation
    JOIN RegionGroup USING (RegionIdx))) as AR1
JOIN (
SELECT * 
FROM
(
SELECT * 
FROM
(SELECT PartTimeInfo.AccomIdx, PartTimeInfo.WeekdayTime, PartTimeInfo.WeekendTime, 
		PartTimeInfo.MemberWeekdayTime, PartTimeInfo.MemberWeekendTime
FROM PartTimeInfo) as P1 join 
(Select 
		PartTimePrice.AccomIdx as AccomIdx2,min(MemberPartTimeWeekdayPrice) as MemberPartTimeWeekdayPrice, min(MemberPartTimeWeekendPrice) as MemberPartTimeWeekendPrice
	From
		PartTimePrice
	GROUP BY PartTimePrice.AccomIdx) as P2 ON (P1.AccomIdx = P2.AccomIdx2)
GROUP BY P1.AccomIdx, P1.WeekdayTime, P1.WeekendTime, 
		P1.MemberWeekdayTime, P1.MemberWeekendTime) T1
JOIN
(SELECT *
FROM
(
SELECT AllDayInfo.AccomIdx as AccomIdx3, AllDayInfo.WeekdayTime as AllDayWeekdayTime, AllDayInfo.WeekendTime as AllDayWeekendTime, 
		AllDayInfo.MemberWeekdayTime as MemberAllDayWeekdayTime, AllDayInfo.MemberWeekendTime as MemberAllDayWeekendTime
FROM AllDayInfo) as A1 
	join 
(Select 
		AllDayPrice.AccomIdx as AccomIdx4, min(MemberAllDayWeekdayPrice) as MemberAllDayWeekdayPrice, min(MemberAllDayWeekendPrice) as MemberAllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) as A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
GROUP BY A1.AccomIdx3, A1.AllDayWeekdayTime, A1.AllDayWeekendTime, 
		A1.MemberAllDayWeekdayTime, A1.MemberAllDayWeekendTime) T2 ON (T1.AccomIdx = T2.AccomIdx3)) F1
        ON (AR1.AccomIdx = F1.AccomIdx)
 WHERE
  RegionGroupIdx = ?
        AND AR1.AccomType = 'M'
        AND AR1.isDeleted = 'N') AS a1 JOIN
        (SELECT 
        Accommodation.AccomIdx,
            AVG(OverallRating) AS OverallRating,
            COUNT(ReviewIdx) AS ReviewCount
    FROM
        Accommodation
    JOIN AccommodationReview ON (Accommodation.AccomIdx = AccommodationReview.AccomIdx)
    WHERE
        Accommodation.Accomtype = 'M'
            AND AccommodationReview.isDeleted = 'N'
            AND Accommodation.isDeleted = 'N'
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;;
";

    $st = $pdo->prepare($query);
    $st->execute([$RegionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function MemberSearchHotelByArea($RegionIdx, $startDate, $endDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
    (SELECT 
        AR1.AccomIdx,
            AR1.AccomThumbnailUrl,
            AR1.AccomName,
            CASE
                WHEN
                    DAYOFWEEK(NOW()) = 2
                        || DAYOFWEEK(NOW()) = 3
                        || DAYOFWEEK(NOW()) = 4
                        || DAYOFWEEK(NOW()) = 5
                THEN
                    F1.MemberAllDayWeekdayTime
                ELSE F1.MemberAllDayWeekendTime
            END AS AllDayTime,
            CASE
                WHEN
                    DAYOFWEEK(NOW()) = 2
                        || DAYOFWEEK(NOW()) = 3
                        || DAYOFWEEK(NOW()) = 4
                        || DAYOFWEEK(NOW()) = 5
                THEN
                    F1.MemberAllDayWeekdayPrice
                ELSE F1.MemberAllDayWeekendPrice
            END AS AllDayPrice
    FROM
        (SELECT 
        Accommodation.RegionIdx,
            Accommodation.AccomIdx,
            Accommodation.AccomName,
            Accommodation.AccomType,
            Accommodation.AccomIntroduction,
            Accommodation.AccomThumbnailUrl,
            Accommodation.AccomCity,
            Accommodation.AccomAddress,
            Accommodation.AccomTheme,
            Accommodation.AccomGuide,
            Accommodation.ReserveInfo,
            Accommodation.AccomLatitude,
            Accommodation.AccomLongtitude,
            Accommodation.isDeleted,
            RegionGroup.RegionGroupIdx
    FROM
        (Accommodation
    JOIN RegionGroup USING (RegionIdx))) AS AR1
    JOIN (SELECT 
        *
    FROM
        (SELECT 
        *
    FROM
        (SELECT 
        AllDayInfo.AccomIdx AS AccomIdx3,
            AllDayInfo.WeekdayTime AS AllDayWeekdayTime,
            AllDayInfo.WeekendTime AS AllDayWeekendTime,
            AllDayInfo.MemberWeekdayTime AS MemberAllDayWeekdayTime,
            AllDayInfo.MemberWeekendTime AS MemberAllDayWeekendTime
    FROM
        AllDayInfo) AS A1
    JOIN (SELECT 
        AllDayPrice.AccomIdx AS AccomIdx4,
            MIN(MemberAllDayWeekdayPrice) AS MemberAllDayWeekdayPrice,
            MIN(MemberAllDayWeekendPrice) AS MemberAllDayWeekendPrice
    FROM
        AllDayPrice
    GROUP BY AllDayPrice.AccomIdx) AS A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
    GROUP BY A1.AccomIdx3 , A1.AllDayWeekdayTime , A1.AllDayWeekendTime , A1.MemberAllDayWeekdayTime , A1.MemberAllDayWeekendTime) as D1) as F1 ON (AR1.AccomIdx = F1.AccomIdx3)
    WHERE
        RegionGroupIdx = ?
            AND AR1.AccomType = 'H'
            AND AR1.isDeleted = 'N') AS a1
        JOIN
    (SELECT 
        Accommodation.AccomIdx,
            AVG(OverallRating) AS OverallRating,
            COUNT(ReviewIdx) AS ReviewCount
    FROM
        Accommodation
    JOIN AccommodationReview ON (Accommodation.AccomIdx = AccommodationReview.AccomIdx)
    WHERE
        Accommodation.Accomtype = 'H'
            AND AccommodationReview.isDeleted = 'N'
            AND Accommodation.isDeleted = 'N'
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;;
";

    $st = $pdo->prepare($query);
    $st->execute([$RegionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidRegion($RegionGroupIdx) {
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select RegionGroupIdx from RegionGroup where RegionGroupIdx = ? and isDeleted = 'N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$RegionGroupIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}