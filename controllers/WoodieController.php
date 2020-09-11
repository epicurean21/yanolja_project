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
         * 호텔 지역그룹 리스트 조회
         */
        case "getHotelGroupList":
            http_response_code(200);
            $res->result = getHotelGroupList();
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "호텔 도시-지역 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        /*
         * 지역별 모든 모텔 조회
         */
        case "getMotels":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){
                 
                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400;
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

            // 필수키체크
            isValidKey('startAt', $_GET);
            isValidKey('endAt', $_GET);
            isValidKey('motelGroupIdx', $_GET);
            isValidKey('adult', $_GET);
            isValidKey('child', $_GET);


            // 유효성검사
            if(!isValidMotelGroupIdx($_GET['motelGroupIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404 ;
                $res->message = "유효하지 않은 모텔그룹id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "지역 모텔 불러오기 성공";
            $res->result = getMotels($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['motelGroupIdx'], $_GET['adult'], $_GET['child']);
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        /*
         * 지역별 모든 모텔 조회
         */
        case "getHotels":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400 ;
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

            // 필수키체크
            isValidKey('startAt', $_GET);
            isValidKey('endAt', $_GET);
            isValidKey('hotelGroupIdx', $_GET);
            isValidKey('adult', $_GET);
            isValidKey('child', $_GET);

            // 유효성 검사
            if(!isValidHotelGroupIdx($_GET['hotelGroupIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404 ;
                $res->message = "유효하지 않은 hotelGroupIdx 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "지역 모텔 불러오기 성공";
            $res->result = getHotels($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['hotelGroupIdx'], $_GET['adult'], $_GET['child']);
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
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400 ;
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


            // 필수키체크
            isValidKey('startAt', $_GET);
            isValidKey('endAt', $_GET);
            isValidKey('motelGroupIdx', $_GET);
            isValidKey('adult', $_GET);
            isValidKey('child', $_GET);

            // 유효성검사
            if(!isValidMotelGroupIdx($_GET['motelGroupIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404 ;
                $res->message = "유효하지 않은 모텔그룹id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // AccomIdx 유효성 검사
            if(!isValidAccomIdx($vars["accomIdx"])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404 ;
                $res->message = "유효하지 않은 accomIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
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
            if(strtotime($_GET['startAt']) == strtotime(date('Y-m-d')))
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
            $res->ReviewPreview = getAccomReviewWithReply($accomIdx);
            $res->result = getMotelRoomsInfo($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['adult'], $_GET['child'], $_GET['motelGroupIdx'], $accomIdx);


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * 특정 호텔의 객실 조회
         */
        case "getHotelRooms":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400 ;
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

            // 필수키체크
            isValidKey('startAt', $_GET);
            isValidKey('endAt', $_GET);
            isValidKey('hotelGroupIdx', $_GET);
            isValidKey('adult', $_GET);
            isValidKey('child', $_GET);

            // 유효성 검사
            if(!isValidHotelGroupIdx($_GET['hotelGroupIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404 ;
                $res->message = "유효하지 않은 hotelGroupIdx 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // AccomIdx 유효성 검사
            if(!isValidAccomIdx($vars["accomIdx"])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404 ;
                $res->message = "유효하지 않은 accomIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // PathVariable 할당한다.
            $accomIdx = $vars["accomIdx"];

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "특정 호텔의 모든 방 불러오기 성공";
            $res->info = getAccomInfo($accomIdx);
            $res->info['Contact'] = getAccomContact($accomIdx);
            $res->info['NumOfReviewReply'] = getNumOfReviewReply($accomIdx);

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
            $res->ReviewPreview = getAccomReviewWithReply($accomIdx);
            $res->result = getHotelRoomsInfo($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['adult'], $_GET['child'], $_GET['hotelGroupIdx'], $accomIdx);

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         *  특정 모텔 특정 객실 조회
         */
        case "getRoomDetail":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400 ;
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

            // 필수키체크
            isValidKey('startAt', $_GET);
            isValidKey('endAt', $_GET);
            isValidKey('accomIdx', $_GET);
            isValidKey('roomIdx', $_GET);

            // AccomIdx 유효성 검사
            if(!isValidAccomIdx($_GET['accomIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404;
                $res->message = "유효하지 않은 accomIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            // AccomIdx 유효성 검사
            if(!isValidRoomIdx($_GET['accomIdx'], $_GET['roomIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404;
                $res->message = "유효하지 않은 roomIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "특정 숙소 특정 객실 조회 성공";
            $res->result = getRoomDetail($isMember, $_GET['startAt'], $_GET['endAt'], $_GET['accomIdx'], $_GET['roomIdx']);
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * 특정 모텔의 요금 정보 조회
         */
        case "getAccomMoneyInfo":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400 ;
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

            // 필수 키 체크
            isValidKey('accomIdx', $_GET);

            // AccomIdx 유효성 검사
            if(!isValidAccomIdx($_GET['accomIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404;
                $res->message = "유효하지 않은 accomIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 모텔만 요금정보 탭이 있음
            if(getTypeOfAccom($_GET['accomIdx']) != 'M'){
                http_response_code(404);
                $res->isSuccess = false;
                $res->code = 404;
                $res->message = "모텔만이 요금정보를 가지고 있습니다. 숙소인덱스가 잘못되었습니다.";
                $res->result = '';
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
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
        case "getAccomSellerInfo":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400;
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

            // 필수 키 체크
            isValidKey('accomIdx', $_GET);

            // AccomIdx 유효성 검사
            if(!isValidAccomIdx($_GET['accomIdx'])){
                http_response_code(404);
                $res->isSuccess = FALSE;
                $res->code = 404;
                $res->message = "유효하지 않은 accomIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "판매자 정보 불러오기 성공";
            $res->result = getAccomSellerInfo($_GET['accomIdx']);
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * 하단 네비게이션 바 지역별 버튼 클릭시 지역별 그룹리스트 출력
         */
        case "getAreas":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400;
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



        /*
         * 하단 네비게이션 바 지역별 버튼 클릭시 지역별 그룹리스트 출력
         */
        case "getAccomByArea":
            http_response_code(200);

            // 1. 토큰여부로 회원/비회원 검사 => 요금/시간 차등 적용
            if (array_key_exists('HTTP_X_ACCESS_TOKEN', $_SERVER)){

                // 1-1. 토큰있으면, 유효성 검사
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    http_response_code(400);
                    $res->isSuccess = FALSE;
                    $res->code = 400;
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

            // 필수키체크
            isValidKey('groupIdx', $_GET);
            isValidKey('adult', $_GET);
            isValidKey('child', $_GET);
            isValidKey('startAt', $_GET);
            isValidKey('endAt', $_GET);
            
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "하단 네비게이션바 지역별 숙소 불러오기 성공";
            $res->result = getAccomByArea($vars["groupIdx"], $isMember, $_GET['startAt'], $_GET['endAt'], $_GET['adult'], $_GET['child']);

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
