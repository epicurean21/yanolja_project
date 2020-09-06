<?php

function AroundMotelMember($Latitude, $Longtitude, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    AR1.AccomLatitude,
    AR1.AccomLongtitude,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.WeekdayTime
        ELSE F1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.MemberPartTimeWeekdayPrice
        ELSE F1.MemberPartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.MemberAllDayWeekdayTime
        ELSE F1.MemberAllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.MemberAllDayWeekdayPrice
        ELSE F1.MemberAllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted
FROM (Accommodation
    JOIN Region USING (RegionIdx))) as AR1
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
        AR1.AccomType = 'M'
        AND AR1.isDeleted = 'N'
		AND (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(AR1.AccomLatitude)) * COS(RADIANS(AR1.AccomLongtitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(AR1.AccomLatitude)))) < 10
) AS a1 

        JOIN
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
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $Latitude,$Longtitude, $Latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function AroundMotel($Latitude, $Longtitude, $CheckInDate)
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
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.WeekdayTime
        ELSE F1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.PartTimeWeekdayPrice
        ELSE F1.PartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.AllDayWeekdayTime
        ELSE F1.AllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.AllDayWeekdayPrice
        ELSE F1.AllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted
FROM (Accommodation
    JOIN Region USING (RegionIdx))) as AR1
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
		PartTimePrice.AccomIdx as AccomIdx2, min(PartTimeWeekdayPrice) as PartTimeWeekdayPrice, min(PartTimeWeekendPrice) as PartTimeWeekendPrice
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
		AllDayPrice.AccomIdx as AccomIdx4, min(AllDayWeekdayPrice) as AllDayWeekdayPrice, min(AllDayWeekendPrice) as AllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) as A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
GROUP BY A1.AccomIdx3, A1.AllDayWeekdayTime, A1.AllDayWeekendTime, 
		A1.MemberAllDayWeekdayTime, A1.MemberAllDayWeekendTime) T2 ON (T1.AccomIdx = T2.AccomIdx3)) F1
        ON (AR1.AccomIdx = F1.AccomIdx)
 WHERE
		AR1.AccomType = 'M'
        AND AR1.isDeleted = 'N'
		AND (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(AR1.AccomLatitude)) * COS(RADIANS(AR1.AccomLongtitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(AR1.AccomLatitude)))) < 10
) AS a1
JOIN
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
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $Latitude, $Longtitude, $Latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function AroundHotel($Latitude, $Longtitude, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "
SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    AR1.AccomLatitude,
    AR1.AccomLongtitude,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.AllDayWeekdayTime
        ELSE F1.AllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.AllDayWeekdayPrice
        ELSE F1.AllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted
FROM (Accommodation
    JOIN Region USING (RegionIdx))) as AR1
JOIN (SELECT *
FROM
(
SELECT AllDayInfo.AccomIdx as AccomIdx3, AllDayInfo.WeekdayTime as AllDayWeekdayTime, AllDayInfo.WeekendTime as AllDayWeekendTime, 
		AllDayInfo.MemberWeekdayTime as MemberAllDayWeekdayTime, AllDayInfo.MemberWeekendTime as MemberAllDayWeekendTime
FROM AllDayInfo) as A1 
	join 
(Select 
		AllDayPrice.AccomIdx as AccomIdx4, min(AllDayWeekdayPrice) as AllDayWeekdayPrice, min(AllDayWeekendPrice) as AllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) as A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
GROUP BY A1.AccomIdx3, A1.AllDayWeekdayTime, A1.AllDayWeekendTime, 
		A1.MemberAllDayWeekdayTime, A1.MemberAllDayWeekendTime) F1
        ON (AR1.AccomIdx = F1.AccomIdx3)
 WHERE
		AR1.AccomType = 'H'
        AND AR1.isDeleted = 'N'
		AND (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(AR1.AccomLatitude)) * COS(RADIANS(AR1.AccomLongtitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(AR1.AccomLatitude)))) < 10
) AS a1
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
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;
";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $Latitude, $Longtitude, $Latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function AroundHotelMember($Latitude, $Longtitude, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    AR1.AccomLatitude,
    AR1.AccomLongtitude,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.MemberAllDayWeekdayTime
        ELSE F1.MemberAllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(?) = 2
                || DAYOFWEEK(?) = 3
                || DAYOFWEEK(?) = 4
                || DAYOFWEEK(?) = 5
        THEN
            F1.MemberAllDayWeekdayPrice
        ELSE F1.MemberAllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted
FROM (Accommodation
    JOIN Region USING (RegionIdx))) as AR1
JOIN (SELECT *
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
		A1.MemberAllDayWeekdayTime, A1.MemberAllDayWeekendTime) F1
        ON (AR1.AccomIdx = F1.AccomIdx3)
 WHERE
		AR1.AccomType = 'H'
        AND AR1.isDeleted = 'N'
		AND (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(AR1.AccomLatitude)) * COS(RADIANS(AR1.AccomLongtitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(AR1.AccomLatitude)))) < 10
) AS a1
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
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $Latitude, $Longtitude, $Latitude]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function aroundMap($Latitude, $Longtitude, $Scope, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT Accommodation.AccomIdx, AccomLatitude, AccomLongtitude,
	CASE 
		WHEN 
			(DAYOFWEEK(?) > 1 && DAYOFWEEK(?) < 6)
		THEN
			AllDayWeekdayPrice
		ELSE
			AllDayWeekendPrice
	END as Price
FROM Accommodation join (Select 
		AllDayPrice.AccomIdx as AccomIdx, min(AllDayWeekdayPrice) as AllDayWeekdayPrice, min(AllDayWeekendPrice) as AllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) A1 On (Accommodation.AccomIdx = A1.AccomIdx)
WHERE
	(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(AccomLatitude)) * COS(RADIANS(AccomLongtitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(AccomLatitude)))) < ?;";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $Latitude, $Longtitude, $Latitude, $Scope]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function aroundMapMember($Latitude, $Longtitude, $Scope, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT Accommodation.AccomIdx, AccomLatitude, AccomLongtitude,
	CASE 
		WHEN 
			(DAYOFWEEK(?) > 1 && DAYOFWEEK(?) < 6)
		THEN
			MemberAllDayWeekdayPrice
		ELSE
			MemberAllDayWeekendPrice
	END as Price
FROM Accommodation join (Select 
		AllDayPrice.AccomIdx as AccomIdx, min(MemberAllDayWeekdayPrice) as MemberAllDayWeekdayPrice, min(MemberAllDayWeekendPrice) as MemberAllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) A1 On (Accommodation.AccomIdx = A1.AccomIdx)
WHERE
	(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(AccomLatitude)) * COS(RADIANS(AccomLongtitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(AccomLatitude)))) < ?;";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $Latitude, $Longtitude, $Latitude, $Scope]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}