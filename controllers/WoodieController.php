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
