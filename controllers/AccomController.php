<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";
$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "searchAccomByArea":
            echo "hello";
//            http_response_code(200);
//
//            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            if($jwt == null) {
//                $RegionGroupIdx = $vars["RegionGroupIdx"];
//                if(!isValidRegion($RegionIdx)) {
//                    $res->isSuccess = FALSE;
//                    $res->code = 201;
//                    $res->message = "존재하지 않는 지역입니다";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                }
//                else {
//                    $res->resultAccommodation = searchAccomByArea($RegionGroupIdx);
//                    $res->isSuccess = TRUE;
//                    $res->code = 100;
//                    $res->message = "불러오기 성공";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    break;
//                }
//            }
//            else {
//                $RegionGroupIdx = $vars["RegionGroupIdx"];
//                if (!isValidRegion($RegionIdx)) {
//                    $res->isSuccess = FALSE;
//                    $res->code = 201;
//                    $res->message = "존재하지 않는 지역입니다";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                } else {
//                    $res->resultAccommodation = searchAccomByArea($RegionGroupIdx);
//                    $res->isSuccess = TRUE;
//                    $res->code = 100;
//                    $res->message = "불러오기 성공";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    break;
//                }
//            }

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
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "test":
            http_response_code(200);
            $res->result = test();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testDetail":
            http_response_code(200);
            $res->result = testDetail($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
