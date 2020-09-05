<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "aroundMotel":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $Latitude = $_GET['Latitude'];
            $Longtitude = $_GET['Longtitude'];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));

            if($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            if($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }

            if($Latitude == null || $Longtitude == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "올바른 위도와 경도를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if($jwt == null) { // 비회원
                    $res->Result = AroundMotel($Latitude, $Longtitude, $CheckInDate);
                    $res->IsSuccess = TRUE;
                    $res->Code = 200;
                    $res->Message = "비회원 위치 기반 모텔 불러오기 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else { // 회원
                    if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                        $res->IsSuccess = FALSE;
                        $res->Code = 201;
                        $res->Message = "유효하지 않은 토큰입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                    else {
                        $res->Result = AroundMotelMember($Latitude, $Longtitude, $CheckInDate);
                        $res->IsSuccess = TRUE;
                        $res->Code = 201;
                        $res->Message = "회원 위치 기반 모텔 불러오기 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
            }
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
