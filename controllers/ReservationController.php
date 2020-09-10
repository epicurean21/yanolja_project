<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "reserveP" :
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));

            if ($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            else {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }
            else {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if (($CheckOutDate - $CheckInDate) < 1) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($jwt == null) { // 비회원 예약진행 화면
                $res->IsSuccess = TRUE;
                $res->Code = 200;
                $res->Message = "비회원 예약화면 불러오기 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 401;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserId = $data->id;
                    $UserContact = getUserContact($UserId);
                    $UserIdx = getUserIdx($UserId);
                    $res->Result->UserContact = $UserContact;
                    $res->Result->CouponCount = getUserCouponCountMotelPartTime($UserIdx, $CheckInDate, $CheckOutDate);
                    $res->Result->Coupon = getUserCouponMotelPartTime($UserIdx, $CheckInDate, $CheckOutDate);
                    $res->IsSuccess = TRUE;
                    $res->Code = 200;
                    $res->Message = "회원 예약화면 불러오기 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            break;

        case "orderP":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $AccomIdx = $req->AccomIdx;
            $RoomIdx = $req->RoomIdx;
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));
            $ReserveName = "";
            $ReserveContact = "";
            $ReserveType = 'P';
            $VisitExists = 'N';
            $VisitName = null;
            $VisitContact = null;
            $Transportation = "";
            $UserPointUsed = null;
            $CouponIdx = null;
            $FinalCost = 0;
            $UserIdx = 0;

            $isPointUsed = false;
            $isCouponUsed = false;

            if($jwt != null) { // 로그인된 상태이다 회원 예약
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 400;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserIdx = getUserIdx($data->id);

                    if($req->UserPointUsed != null) {
                        $UserPointUsed = $req->UserPointUsed;
                    } // 유저 포인트
                    if($req->CouponIdx != null) {// 유저 쿠폰
                        $CouponIdx = $req->CouponIdx;
                    }
                }
            } // 회원

            if($req->ReserveName == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 407;
                $res->Message = "예약자 이름을 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                $ReserveName = $req->ReserveName;
            }

            if($req->ReserveContact == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 407;
                $res->Message = "예약자 번호를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                $ReserveContact = $req->ReserveContact;
            }

            if($req->VisitExists != null && $req->VisitExists == 'Y') {
                $VisitName = $req->VisitName;
                $VisitContact = $req->VisitContact;
            } // 방문자 존재여부

            if($req->Transportation == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 401;
                $res->Message = "방문 방법 (교통)을 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } // 교통수단 선택여부
            else {
                $Transportation = $req->Transportation;
            }

            if($req->CheckInDate == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } // 체크인 날짜, 시간
            else {
                $CheckInDate = $req->CheckInDate;
            }
            if($req->CheckOutDate == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } // 체크아웃 날짜, 시간
            else {
                $CheckOutDate = $req->CheckOutDate;
            }
            $startAt = date('Y-m-d', strtotime($CheckInDate));
            $endAt = date("Y-m-d", strtotime("+1 day", strtotime($CheckOutDate)));
            // $TimeDiff = (strtotime("$CheckOutDate") - strtotime("$CheckInDate")) / 3600;

            if($req->FinalCost == null || !is_int($req->FinalCost)) {
                $res->IsSuccess = FALSE;
                $res->Code = 403;
                $res->Message = "가격을 정확하게 기제해 주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                $FinalCost = $req->FinalCost;
            }

            if($UserPointUsed != null){
                $CurrentPoint = getUserPoint($UserIdx);
                $NewPoint = $CurrentPoint - $UserPointUsed;
                if($NewPoint < 0) {
                    rollback();
                    transactionEnd();
                    $res->IsSuccess = FALSE;
                    $res->Code = 406;
                    $res->Message = "보유한 포인트 이상을 사용하였습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    $isPointUsed= true;
                }
            }

            if($CouponIdx != null) {
                if(!isCouponExists($CouponIdx, $ReserveType)) {
                    rollback();
                    transactionEnd();
                    $res->IsSuccess = FALSE;
                    $res->Code = 405;
                    $res->Message = "존재하지 않는 쿠폰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    if(!isValidCoupon($CouponIdx, $UserIdx, $CheckInDate, $CheckOutDate, $ReserveType)) {
                        rollback();
                        transactionEnd();
                        $res->IsSuccess = FALSE;
                        $res->Code = 405;
                        $res->Message = "사용할수 없는 쿠폰입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                    else {
                        $isCouponUsed = true;
                    }
                }
            }
            $CurrentPoint = getUserPoint($UserIdx);
            $NewPoint = $CurrentPoint - $UserPointUsed;

            $res = PostNewReservationP($UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
                $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
                $Transportation, $UserPointUsed, $CouponIdx, $FinalCost, $NewPoint ,$isPointUsed, $isCouponUsed, $startAt, $endAt);

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "reserveA" :
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));
            $AccomIdx = "";
            if ($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            else {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }
            else {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if (($CheckOutDate - $CheckInDate) < 1) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($_GET['AccomIdx'] == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 403;
                $res->Message = "올바른 숙소 인덱스를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                $AccomIdx = $_GET['AccomIdx'];
                if(!isValidAccom($AccomIdx)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 403;
                    $res->Message = "올바른 숙소 인덱스를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            $AccomType = getAccomType($AccomIdx);
            if($AccomType == 'M') { // 모텔 숙박
                if ($jwt == null) { // 비회원 예약진행 화면
                    $res->IsSuccess = TRUE;
                    $res->Code = 200;
                    $res->Message = "비회원 예약화면 불러오기 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                } else {
                    if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                        $res->IsSuccess = FALSE;
                        $res->Code = 401;
                        $res->Message = "유효하지 않은 토큰입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    } else {
                        $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                        $UserId = $data->id;
                        $UserContact = getUserContact($UserId);
                        $UserIdx = getUserIdx($UserId);
                        $res->Result->UserContact = $UserContact;
                        $res->Result->CouponCount = getUserCouponCountMotel($UserIdx, $CheckInDate, $CheckOutDate);
                        $res->Result->Coupon = getUserCouponMotel($UserIdx, $CheckInDate, $CheckOutDate);
                        $res->IsSuccess = TRUE;
                        $res->Code = 201;
                        $res->Message = "회원 예약화면 불러오기 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
            }
            else if($AccomType = 'H') { //호텔 예약
                if ($jwt == null) { // 비회원 예약진행 화면
                    $res->IsSuccess = TRUE;
                    $res->Code = 200;
                    $res->Message = "비회원 예약화면 불러오기 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                } else {
                    if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                        $res->IsSuccess = FALSE;
                        $res->Code = 401;
                        $res->Message = "유효하지 않은 토큰입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    } else {
                        $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                        $UserId = $data->id;
                        $UserContact = getUserContact($UserId);
                        $UserIdx = getUserIdx($UserId);
                        $res->Result->UserContact = $UserContact;
                        $res->Result->CouponCount = getUserCouponCountHotel($UserIdx, $CheckInDate, $CheckOutDate);
                        $res->Result->Coupon = getUserCouponHotel($UserIdx, $CheckInDate, $CheckOutDate);
                        $res->IsSuccess = TRUE;
                        $res->Code = 200;
                        $res->Message = "회원 예약화면 불러오기 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
            }
            break;

        case "orderA":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $AccomIdx = $req->AccomIdx;
            $RoomIdx = $req->RoomIdx;
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));
            $ReserveName = "";
            $ReserveContact = "";
            $ReserveType = 'A';
            $VisitExists = 'N';
            $VisitName = null;
            $VisitContact = null;
            $Transportation = "";
            $UserPointUsed = null;
            $CouponIdx = null;
            $FinalCost = 0;
            $UserIdx = 0;

            $isPointUsed = false;
            $isCouponUsed = false;
            if($jwt != null) { // 로그인된 상태이다 회원 예약
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 400;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserIdx = getUserIdx($data->id);

                    if($req->UserPointUsed != null) {
                        $UserPointUsed = $req->UserPointUsed;
                    } // 유저 포인트
                    if($req->CouponIdx != null) {// 유저 쿠폰
                        $CouponIdx = $req->CouponIdx;
                    }
                }
            } // 회원

            if($req->ReserveName == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 407;
                $res->Message = "예약자 이름을 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                $ReserveName = $req->ReserveName;
            }

            if($req->ReserveContact == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 407;
                $res->Message = "예약자 번호를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                $ReserveContact = $req->ReserveContact;
            }

            if($req->VisitExists != null && $req->VisitExists == 'Y') {
                $VisitName = $req->VisitName;
                $VisitContact = $req->VisitContact;
            } // 방문자 존재여부

            if($req->Transportation == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 401;
                $res->Message = "방문 방법 (교통)을 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } // 교통수단 선택여부
            else {
                $Transportation = $req->Transportation;
            }

            if($req->CheckInDate == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } // 체크인 날짜, 시간
            else {
                $CheckInDate = $req->CheckInDate;
            }
            if($req->CheckOutDate == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } // 체크아웃 날짜, 시간
            else {
                $CheckOutDate = $req->CheckOutDate;
            }
            $startAt = date('Y-m-d', strtotime($CheckInDate));
            $endAt = date("Y-m-d",strtotime($CheckOutDate));
            //$TimeDiff = (strtotime("$CheckOutDate") - strtotime("$CheckInDate")) / 3600;

            if($req->FinalCost == null || !is_int($req->FinalCost)) {
                $res->IsSuccess = FALSE;
                $res->Code = 403;
                $res->Message = "가격을 정확하게 기제해 주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                $FinalCost = $req->FinalCost;
            }

            if($UserPointUsed != null){
                $CurrentPoint = getUserPoint($UserIdx);
                $NewPoint = $CurrentPoint - $UserPointUsed;
                if($NewPoint < 0) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 406;
                    $res->Message = "보유한 포인트 이상을 사용하였습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    $isPointUsed= true;
                }
            }

            if($CouponIdx != null) {
                if(!isCouponExists($CouponIdx, $ReserveType)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 405;
                    $res->Message = "존재하지 않는 쿠폰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    if(!isValidCoupon($CouponIdx, $UserIdx, $CheckInDate, $CheckOutDate, $ReserveType)) {
                        $res->IsSuccess = FALSE;
                        $res->Code = 405;
                        $res->Message = "사용할수 없는 쿠폰입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                    else {
                        $isCouponUsed = true;
                    }
                }
            }
            $CurrentPoint = getUserPoint($UserIdx);
            $NewPoint = $CurrentPoint - $UserPointUsed;

            //php에서 transaction 사용하자
            transactionStart(); // transaction start
            // 1. Reservation에 입력
            $CurrentPoint = getUserPoint($UserIdx);
            $NewPoint = $CurrentPoint - $UserPointUsed;
            patchCouponUsed($UserIdx, $CouponIdx);

            $res = PostNewReservationA($UserIdx, $AccomIdx, $RoomIdx, $ReserveType,
                $CheckInDate, $CheckOutDate, $ReserveName, $ReserveContact, $VisitName, $VisitContact,
                $Transportation, $UserPointUsed, $CouponIdx, $FinalCost, $NewPoint ,$isPointUsed, $isCouponUsed, $startAt, $endAt);

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
