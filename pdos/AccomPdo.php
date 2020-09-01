<?php

function testtest_pdo(){
    echo "테스트";
}

function searchAccomByArea($RegionIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    Accommodation.AccomIdx,
    AccomThumbnailUrl,
    AccomName,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            PartTimeInfo.WeekdayTime
        ELSE PartTimeInfo.WeekdayDeadline
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            PartTimePrice.PartTimeWeekdayPrice
        ELSE PartTimePrice.PartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            AllDayInfo.WeekdayTime
        ELSE AllDayInfo.WeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            AllDayPrice.AllDayWeekdayPrice
        ELSE AllDayPrice.AllDayWeekendPrice
    END AS AllDayPrice
FROM
    (Accommodation join RegionGroup using (RegionIdx))
        JOIN
    ((PartTimeInfo
    JOIN PartTimePrice ON (PartTimeInfo.AccomIdx = PartTimePrice.AccomIdx
        AND PartTimeInfo.RoomIdx = PartTimePrice.RoomIdx))
    JOIN (AllDayInfo
    JOIN AllDayPrice ON (AllDayInfo.AccomIdx = AllDayPrice.AccomIdx
        AND AllDayInfo.RoomIdx = AllDayPrice.RoomIdx)) ON (PartTimeInfo.AccomIdx = AllDayInfo.AccomIdx
        AND PartTimeInfo.RoomIdx = AllDayInfo.RoomIdx)) ON (Accommodation.AccomIdx = PartTimeInfo.AccomIdx)
WHERE
    RegionGroupIdx = ?
        AND PartTimeInfo.AccomIdx = AllDayInfo.AccomIdx
        AND Accommodation.AccomType = 'M'
        AND Accommodation.isDeleted = 'N'
        AND PartTimeWeekdayPrice = (SELECT 
            MIN(PartTimeWeekdayPrice)
        FROM
            PartTimeInfo
                JOIN
            PartTimePrice ON (PartTimeInfo.AccomIdx = PartTimePrice.AccomIdx))
        AND AllDayWeekdayPrice = (SELECT 
            MIN(AllDayWeekdayPrice)
        FROM
            (AllDayInfo
            JOIN AllDayPrice ON (AllDayInfo.AccomIdx = AllDayPrice.AccomIdx
                AND AllDayInfo.RoomIdx = AllDayPrice.RoomIdx)));";

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