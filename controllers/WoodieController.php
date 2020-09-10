<?php
require 'function.php';


const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";
$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;


        /*
         * 1. 지역별 모든 모텔 조회
         */
        case "getMotels":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){
                 
                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $isMember = true;
            }
            else {
                // 2. 토큰 없으면 비회원
                $isMember = false;
            }

            $res->result = getMotels($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['motelGroupIdx'], $_GET['adult'], $_GET['child']);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "지역 모텔 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * 특정 모텔의 객실 조회
         */
        case "getMotelRooms":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $isMember = true;
            }
            else {
                // 2. 토큰 없으면 비회원
                $isMember = false;
            }

            // PathVariable 할당한다.
            $accomIdx = $vars["accomIdx"];

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "모텔의 모든 방 불러오기 성공";
            $res->info = getAccomInfo($accomIdx);
            $res->info['Contact'] = getAccomContact($accomIdx);
            $res->info['NumOfReviewReply'] = getNumOfReviewReply($accomIdx);

            // 주차장 가능 여부 => 당일 조회에만 적용
            if(strtotime($_GET['startAt']) < strtotime(date('Y-m-d H:i:s')))
                $res->info['IsFullParking'] = getAccomParkingStatus($accomIdx);

            // 태그를 출력 => 없는 경우 빈 배열 출력
            $accomTag = getAccomTag($accomIdx);
            if(empty($accomTag)){
                $res->info['AccomTag'] = array();
            }
            else{
                $res->info['AccomTag'] = $accomTag;
            }

            $res->facility = getAccomFacilities($accomIdx);

            $res->photo = getAccomPhotos($accomIdx);
            $res->result = getMotelRoomsInfo($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['adult'], $_GET['child'], $_GET['motelGroupIdx'], $accomIdx);


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * 특정 모텔의 요금 정보 조회
         */
        case "getMotelMoneyInfo":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $isMember = true;
            }
            else {
                // 2. 토큰 없으면 비회원
                $isMember = false;
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "모텔의 요금정보 불러오기 성공";
            $res->result = getMotelMoneyInfo($_GET['accomIdx']);

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * 특정 모텔의 판매자 정보
         */
        case "getMotelSellerInfo":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $isMember = true;
            }
            else {
                // 2. 토큰 없으면 비회원
                $isMember = false;
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "판매자 정보 불러오기 성공";
            $res->result = getMotelSellerInfo($_GET['accomIdx']);
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getAreas":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $isMember = true;
            }
            else {
                // 2. 토큰 없으면 비회원
                $isMember = false;
            }

            $res->result = getAreas();
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "하단 네비게이션바 지역별 목록 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
