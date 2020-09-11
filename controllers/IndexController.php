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
        * API Name : 테스트 API
        * 마지막 수정 날짜 : 19.04.29
        */
        case "getMotels":
            http_response_code(200);
            $res->result = getMotels($req->startAt, $req->endAt);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "모텔 도시-지역 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 0
        * API Name : 테스트 API
        * 마지막 수정 날짜 : 19.04.29
        */
        case "getMotelGroupList":
            http_response_code(200);
            $res->result = getMotelGroupList();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "모텔 도시-지역 불러오기 성공";
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
            keyCheck('UserContact', $req);

            // 이미 있는 아이디인지 체크
            if (isValidUserId($req->UserId)) {
                $res->isSuccess = false;
                $res->code = 200;
                $res->message = "이미 존재하는 유저id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 이미 있는 번호인지 체크

            // 비밀번호 정규식으로 체크

            //다른 Validation은 클라이언트 단으로 넘긴다

            createUser($req->UserId, $req->UserPwd, $req->UserContact);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "myYanolja":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if ($jwt == null) { // 비회원
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "비회원 유저 불러오기 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 401;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserId = $data->id;
                    $UserIdx = getUserIdx($UserId);
                    $res->Result->User = myYanolja($UserId, $UserIdx);
                    $res->Result->UserReseration = getUserReservation($UserId);
                    $res->isSuccess = TRUE;
                    $res->code = 200;
                    $res->message = "회원정보불러오기 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            break;

        case "userManage":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if ($jwt == null) { // 비회원
                $res->isSuccess = FALSE;
                $res->code = 401;
                $res->message = "허용되지 않는 요청입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "비밀번호 입력페이지 불러오기 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            break;

        case "isValidPwd":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if ($jwt == null) { // 비회원
                $res->isSuccess = FALSE;
                $res->code = 401;
                $res->message = "허용되지 않는 요청입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if($req->pw == null) {
                    $res->isSuccess = TRUE;
                    $res->code = 401;
                    $res->message = "비밀번호가 입력되지 않았습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserId = $data->id;
                    $UserPwd = $req->pw;
                    if (isValidPwd($UserId, $UserPwd)) {
                        $res->isSuccess = TRUE;
                        $res->code = 200;
                        $res->message = "올바른 비밀번호 입력";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    } else {
                        $res->isSuccess = TRUE;
                        $res->code = 400;
                        $res->message = "입력한 비밀번호가 틀렸습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
            }
            break;
        case "users":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if ($jwt == null) { // 비회원
                $res->isSuccess = FALSE;
                $res->code = 401;
                $res->message = "허용되지 않는 요청입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $UserId = $data->id;
                $UserPwd = $data->pw;
                $res->Result->User = getUserInfo($UserId);
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "회원 정보 관리 불러오기 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            break;

        case "changeName":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if ($jwt == null) { // 비회원
                $res->isSuccess = FALSE;
                $res->code = 401;
                $res->message = "허용되지 않는 요청입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if($req->UserName == null) {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "새로운 이름이 입력되지 않았습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    $UserName = $req->UserName;
                    if(isValidName($UserName)) {
                        $res->isSuccess = FALSE;
                        $res->code = 402;
                        $res->message = "이미 존재하는 이름입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                    else {
                        $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                        $UserId = $data->id;
                        $res->Result = patchUserName($UserId, $UserName);
                        $res->isSuccess = TRUE;
                        $res->code = 200;
                        $res->message = "회원 이름 변경 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
            }
            break;

        case "changePwd":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if ($jwt == null) { // 비회원
                $res->isSuccess = FALSE;
                $res->code = 401;
                $res->message = "허용되지 않는 요청입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if($req->UserOldPwd == null || $req->UserNewPwd1 == null || $req->UserNewPwd2 == null) {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "비밀번호를 입력하지 않았습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserId = $data->id;
                    $UserOldPwd = $req->UserOldPwd;
                    $UserNewPwd1 = $req->UserNewPwd1;
                    $UserNewPwd2 = $req->UserNewPwd2;

                    if(!isValidPwd($UserId, $UserOldPwd)) {
                        $res->isSuccess = FALSE;
                        $res->code = 402;
                        $res->message = "현재 비밀번호가 틀렸습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                    else {
                        if($UserNewPwd1 != $UserNewPwd2) {
                            $res->isSuccess = FALSE;
                            $res->code = 403;
                            $res->message = "새 비밀번호 확인이 틀렸습니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                        else {
                            $num = preg_match('/[0-9]/u', $UserNewPwd1);
                            $eng = preg_match('/[a-z]/u', $UserNewPwd1);
                            $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$UserNewPwd1);

                            if(strlen($UserNewPwd1) < 8 || strlen($UserNewPwd1) > 20 || preg_match("/\s/u", $UserNewPwd1) == true
                             || $num == 0 || $eng == 0 || $spe == 0)
                            {
                                $res->isSuccess = FALSE;
                                $res->code = 405;
                                $res->message = "새 비밀번호 형식이 틀렸습니다 (영문 + 숫자 + 특수 8~20자)";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            }
                            else {
                                if($UserOldPwd == $UserNewPwd1) {
                                    $res->isSuccess = FALSE;
                                    $res->code = 406;
                                    $res->message = "기존 비밀번호와 같습니다";
                                    echo json_encode($res, JSON_NUMERIC_CHECK);
                                    break;
                                }
                                else {
                                    $res->Result = patchUserPwd($UserId, $UserNewPwd1);
                                    $res->isSuccess = TRUE;
                                    $res->code = 200;
                                    $res->message = "회원 비밀번호 변경 성공";
                                    echo json_encode($res, JSON_NUMERIC_CHECK);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
