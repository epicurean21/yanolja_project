<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "aroundMotels":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $Latitude = $_GET['Latitude'];
            $Longitude = $_GET['Longitude'];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));

            if($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            if($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }

            if($Latitude == null || $Longitude == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "올바른 위도와 경도를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if(($CheckOutDate - $CheckInDate) < 1) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 402;
                    $res->Message = "올바른 날짜를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    if ($jwt == null) { // 비회원
                        $res->Result = AroundMotel($Latitude, $Longitude, $CheckInDate);
                        $res->IsSuccess = TRUE;
                        $res->Code = 200;
                        $res->Message = "비회원 위치 기반 모텔 불러오기 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    } else { // 회원
                        if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                            $res->IsSuccess = FALSE;
                            $res->Code = 401;
                            $res->Message = "유효하지 않은 토큰입니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            return;
                        } else {
                            $res->Result = AroundMotelMember($Latitude, $Longitude, $CheckInDate);
                            $res->IsSuccess = TRUE;
                            $res->Code = 201;
                            $res->Message = "회원 위치 기반 모텔 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    }
                }
            }
        case "aroundHotels":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $Latitude = $_GET['Latitude'];
            $Longitude = $_GET['Longitude'];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));

            if($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            if($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }

            if($Latitude == null || $Longitude == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "올바른 위도와 경도를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if(($CheckOutDate - $CheckInDate) < 1) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 402;
                    $res->Message = "올바른 날짜를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    if ($jwt == null) { // 비회원
                        $res->Result = AroundHotel($Latitude, $Longitude, $CheckInDate);
                        $res->IsSuccess = TRUE;
                        $res->Code = 200;
                        $res->Message = "비회원 위치 기반 호텔 불러오기 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    } else { // 회원
                        if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                            $res->IsSuccess = FALSE;
                            $res->Code = 401;
                            $res->Message = "유효하지 않은 토큰입니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            return;
                        } else {
                            $res->Result = AroundHotelMember($Latitude, $Longitude, $CheckInDate);
                            $res->IsSuccess = TRUE;
                            $res->Code = 201;
                            $res->Message = "회원 위치 기반 호텔 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    }
                }
            }

        case "aroundMap":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $Latitude = $_GET['Latitude'];
            $Longitude = $_GET['Longitude'];
            $Scope = 1;
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));

            if($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            if($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }
            if($_GET['Scope'] != null) {
                $Scope = $_GET['Scope'];
            }
            if($Latitude == null || $Longitude == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "올바른 위도와 경도를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if(($CheckOutDate - $CheckInDate) < 1) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 402;
                    $res->Message = "올바른 날짜를 입력하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else {
                    if($Scope < 0.1 || $Scope > 30) {
                        $res->IsSuccess = FALSE;
                        $res->Code = 403;
                        $res->Message = "0.1 ~ 30 내의 범위를 검색하세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                    if ($jwt == null) { // 비회원
                        $res->Result = aroundMap($Latitude, $Longitude, $Scope, $CheckInDate);
                        $res->IsSuccess = TRUE;
                        $res->Code = 200;
                        $res->Message = "비회원 위치 기반 지도 불러오기 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    } else { // 회원
                        if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                            $res->IsSuccess = FALSE;
                            $res->Code = 401;
                            $res->Message = "유효하지 않은 토큰입니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            return;
                        } else {
                            $res->Result = aroundMapMember($Latitude, $Longitude, $Scope, $CheckInDate);
                            $res->IsSuccess = TRUE;
                            $res->Code = 201;
                            $res->Message = "회원 위치 기반 지도 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    }
                }
            }
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
