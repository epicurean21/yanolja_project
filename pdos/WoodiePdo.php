<?php
function getMotels($startAt, $endAt)
{
    $pdo = pdoSqlConnect();
    // 비회원/평일/motel/숙박 전체목록
    $query = "  
select MotelGroup.MotelGroupIdx,
       MotelGroupName.MotelGroupName,
       Region.RegionIdx,
       RegionName,
       Accommodation.AccomIdx,
       AccomName,
       AccomThumbnailUrl,
       MotelRoom.RoomIdx,
       RoomName,
       PartTimeInfo.WeekdayTime,
       AllDayInfo.WeekdayTime,
       StandardCapacity,
       MaxCapacity

from Region
         join MotelGroup on Region.RegionIdx = MotelGroup.RegionIdx
         join MotelGroupName on MotelGroup.MotelGroupIdx = MotelGroupName.MotelGroupIdx
         join Accommodation on Region.RegionIdx = Accommodation.RegionIdx
         join PartTimeInfo on Accommodation.AccomIdx = PartTimeInfo.AccomIdx
         join AllDayInfo on Accommodation.AccomIdx = AllDayInfo.AccomIdx
         join MotelRoom on Accommodation.AccomIdx = MotelRoom.AccomIdx
         join PartTimePrice on MotelRoom.AccomIdx = PartTimePrice.AccomIdx and MotelRoom.RoomIdx = PartTimePrice.RoomIdx
         join AllDayPrice on MotelRoom.AccomIdx = AllDayPrice.AccomIdx and MotelRoom.RoomIdx = AllDayPrice.RoomIdx
         join Reservation on Accommodation.AccomIdx = Reservation.AccomIdx and MotelRoom.RoomIdx = Reservation.RoomIdx
where AccomType = 'M'
  and ReserveType = 'A';
    ";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$startAt, $endAt]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
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