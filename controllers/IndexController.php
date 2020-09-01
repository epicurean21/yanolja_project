<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            test_pdo();
            break;
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
        /*
        * API No. 0
        * API Name : 테스트 Body & Insert API
        * 마지막 수정 날짜 : 19.04.29
        */
        case "createUser":
            http_response_code(200);

            // 바디에 필수적으로 들어와야하는 키 체크
            keyCheck('UserId', $req);
            keyCheck('UserPwd', $req);
            keyCheck('UserEmail', $req);
            keyCheck('UserName', $req);
            keyCheck('UserBirth', $req);
            keyCheck('UserContact', $req);
            keyCheck('UserGender', $req);

            // 이미 있는 아이디인지 체크
            if(isValidUserId($req->UserId)){
                $res->isSuccess = false;
                $res->code = 100;
                $res->message = "이미 존재하는 유저id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 비밀번호 정규식으로 체크

            //다른 Validation은 클라이언트 단으로 넘긴다

            createUser($req->UserId, $req->UserPwd, $req->UserEmail, $req->UserName, $req->UserBirth, $req->UserContact, $req->UserGender);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
