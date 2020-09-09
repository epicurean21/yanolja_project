<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";
$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
//        case "searchMotelByArea":
//            http_response_code(200);
//
//            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//            if ($jwt == null) { // 비회원
////                $RegionGroupIdx = $_GET['RegionGroupIdx'];
////                $startDate = date("Y/m/d");
////                $endDate = date("Y/m/d");
////                $adultNum = 2;
////                $childNum = 0;
////
////                if ($_GET['startDate'] != null)
////                    $startDate = $_GET['startDate'];
////                if ($_GET['endDate'] != null)
////                    $endDate = $_GET['endDate'];
////                if ($_GET['adultNum'] != null)
////                    $adultNum = $_GET['adultNum'];
////                if ($_GET['childNum'] != null)
////                    $childNum = $_GET['childNum'];
////                $peopleNum = $adultNum + $childNum;
////               $StartTimeChk = strtotime($startDate);
////                $EndTimeChk = strtotime($endDate);
//
//                if (!isValidRegion($RegionGroupIdx)) {
//                    $res->isSuccess = FALSE;
//                    $res->code = 400;
//                    $res->message = "존재하지 않는 지역입니다";
//                    echo json_encode($res, JSON_NUMERIC_CHECK);
//                    addErrorLogs($errorLogs, $res, $req);
//                    return;
//                } else {
//                    if ($StartTimeChk > $EndTimeChk) {
//                        $res->isSuccess = FALSE;
//                        $res->code = 400;
//                        $res->message = "입실/퇴실 설정이 비정상적으로 입력되었습니다";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        addErrorLogs($errorLogs, $res, $req);
//                        return;
//                    } else {
//                        $res->Result->ResultAccommodation = SearchMotelByArea($RegionGroupIdx, $startDate, $endDate, $peopleNum);
//                        $res->isSuccess = TRUE;
//                        $res->code = 200;
//                        $res->message = "불러오기 성공";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        break;
//                    }
//
//                }
//            } else {
//                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
//                $startDate = $data->startDate;
//                $endDate = $data->endDate;
//                $adultNum = $data->adult;
//                $childNum = $data->child;
//
//                $RegionGroupIdx = $_GET['RegionGroupIdx'];
//                // startdate, endDate 날짜 입력 올바른지  validation..
//                if ($data->userId != null) { // 로그인 되어있는경우
//                    if (!isValidUserId($data->userID)) {
//                        $res->isSuccess = false;
//                        $res->code = 202;
//                        $res->message = "잘못된 유저id입니다.";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        return;
//                    } else {
//                        if (!isValidRegion($RegionGroupIdx)) {
//                            $res->isSuccess = FALSE;
//                            $res->code = 203;
//                            $res->message = "존재하지 않는 지역입니다";
//                            echo json_encode($res, JSON_NUMERIC_CHECK);
//                            addErrorLogs($errorLogs, $res, $req);
//                            return;
//                        } else {
//                            $res->resultAccommodation = MemberSearchMotelByArea($RegionGroupIdx, $startDate, $endDate);
//                            $res->isSuccess = TRUE;
//                            $res->code = 100;
//                            $res->message = "불러오기 성공";
//                            echo json_encode($res, JSON_NUMERIC_CHECK);
//                            break;
//                        }
//                    }
//                } else { // 비회원
//                    if (!isValidRegion($RegionGroupIdx)) {
//                        $res->isSuccess = FALSE;
//                        $res->code = 203;
//                        $res->message = "존재하지 않는 지역입니다";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        addErrorLogs($errorLogs, $res, $req);
//                        return;
//                    } else {
//                        $res->Result->ResultAccommodation = SearchMotelByArea($RegionGroupIdx, $startDate, $endDate);
//                        $res->isSuccess = TRUE;
//                        $res->code = 100;
//                        $res->message = "불러오기 성공";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        break;
//                    }
//                }
//                break;
//            }
//
//        case "searchHotelByArea":
//            http_response_code(200);
//
//            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
//
//            if ($jwt == null) {
//                $res->isSuccess = FALSE;
//                $res->code = 201;
//                $res->message = "토큰이 존재하지 않습니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//            } else {
//                $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
//                $startDate = $data->startDate;
//                $endDate = $data->endDate;
//                $adultNum = $data->adult;
//                $childNum = $data->child;
//                $RegionGroupIdx = $_GET["RegionGroupIdx"];
//                // startdate, endDate 날짜 입력 올바른지  validation..
//                if ($data->userId != null) { // 로그인 되어있는경우
//                    if (!isValidUserId($data->userID)) {
//                        $res->isSuccess = false;
//                        $res->code = 202;
//                        $res->message = "잘못된 유저id입니다.";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        return;
//                    } else {
//                        if (!isValidRegion($RegionGroupIdx)) {
//                            $res->isSuccess = FALSE;
//                            $res->code = 203;
//                            $res->message = "존재하지 않는 지역입니다";
//                            echo json_encode($res, JSON_NUMERIC_CHECK);
//                            addErrorLogs($errorLogs, $res, $req);
//                            return;
//                        } else {
//                            $res->resultAccommodation = MemberSearchHotelByArea($RegionGroupIdx, $startDate, $endDate);
//                            $res->isSuccess = TRUE;
//                            $res->code = 100;
//                            $res->message = "불러오기 성공";
//                            echo json_encode($res, JSON_NUMERIC_CHECK);
//                            break;
//                        }
//                    }
//                } else { // 비회원
//                    if (!isValidRegion($RegionGroupIdx)) {
//                        $res->isSuccess = FALSE;
//                        $res->code = 203;
//                        $res->message = "존재하지 않는 지역입니다";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        addErrorLogs($errorLogs, $res, $req);
//                        return;
//                    } else {
//                        $res->resultAccommodation = SearchHotelByArea($RegionGroupIdx, $startDate, $endDate);
//                        $res->isSuccess = TRUE;
//                        $res->code = 100;
//                        $res->message = "불러오기 성공";
//                        echo json_encode($res, JSON_NUMERIC_CHECK);
//                        break;
//                    }
//                }
//            }
//
//        case "ACCESS_LOGS":
//            //            header('content-type text/html charset=utf-8');
//            header('Content-Type: text/html; charset=UTF-8');
//            getLogs("./logs/access.log");
//            break;
//        case "ERROR_LOGS":
//            //            header('content-type text/html charset=utf-8');
//            header('Content-Type: text/html; charset=UTF-8');
//            getLogs("./logs/errors.log");
//            break;
//        /*
//         * API No. 0
//         * API Name : 테스트 API
//         * 마지막 수정 날짜 : 19.04.29
//         */
//        case "test":
//            http_response_code(200);
//            $res->result = test();
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
//        /*
//         * API No. 0
//         * API Name : 테스트 Path Variable API
//         * 마지막 수정 날짜 : 19.04.29
//         */
//        case "testDetail":
//            http_response_code(200);
//            $res->result = testDetail($vars["testNo"]);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
//        /*
//         * API No. 0
//         * API Name : 테스트 Body & Insert API
//         * 마지막 수정 날짜 : 19.04.29
//         */
//        case "testPost":
//            http_response_code(200);
//            $res->result = testPost($req->name);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
        case "getMotelDetail":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $AccomIdx = $vars['AccomIdx'];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));
            if ($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            if ($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }
            if (($CheckOutDate - $CheckInDate) < 1) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if (!isValidMotel($AccomIdx)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 400;
                    $res->Message = "존재하지 않는 모텔입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else {
                    if ($jwt == null) { // 비회원
                        $res->Result->PhotoCount = getAccomPhoto($AccomIdx);
                        $res->Result->ReviewReplyCount = getAccomReviewReply($AccomIdx);
                        $res->Result->AccomDetail = getAccomDetail($AccomIdx);
                        $res->Result->MotelRoom = getMotelRoom($AccomIdx, $CheckInDate);
                        $res->Result->OverallReview = getAccomReview($AccomIdx);
                        $res->Result->ReviewDetail = getAccomReviewDetail($AccomIdx);
                        $res->IsSuccess = TRUE;
                        $res->Code = 200;
                        $res->Message = "비회원 모텔 정보 불러오기 성공";
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
                            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

                            $UserIdx = getUserIdx($data->id);
                            $res->Result->PhotoCount = getAccomPhoto($AccomIdx);
                            $res->Result->ReviewReplyCount = getAccomReviewReply($AccomIdx);
                            $res->Result->IsPicked = isAccomPicked($UserIdx, $AccomIdx);
                            $res->Result->AccomDetail = getAccomDetail($AccomIdx);
                            $res->Result->MotelRoom = getMotelRoomMember($AccomIdx, $CheckInDate);
                            $res->Result->OverallReview = getAccomReview($AccomIdx);
                            $res->Result->ReviewDetail = getAccomReviewDetail($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 201;
                            $res->Message = "회원 모텔 정보 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    }
                }
            }

        case "getHotelDetail":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $AccomIdx = $vars['AccomIdx'];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));
            if ($_GET['CheckInDate'] != null) {
                $CheckInDate = $_GET['CheckInDate'];
            }
            if ($_GET['CheckOutDate'] != null) {
                $CheckOutDate = $_GET['CheckOutDate'];
            }
            if (($CheckOutDate - $CheckInDate) < 1) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "올바른 날짜를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else {
                if(!isValidHotel($AccomIdx)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 400;
                    $res->Message = "존재하지 않는 호텔입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    if ($jwt == null) { // 비회원
                        $res->Result->PhotoCount = getAccomPhoto($AccomIdx);
                        $res->Result->ReviewReplyCount = getAccomReviewReply($AccomIdx);
                        $res->Result->AccomDetail = getAccomDetail($AccomIdx);
                        $res->Result->HotelRoom = getHotelRoom($AccomIdx, $CheckInDate, $CheckOutDate);
                        $res->Result->OverallReview = getAccomReview($AccomIdx);
                        $res->Result->ReviewDetail = getAccomReviewDetail($AccomIdx);
                        $res->IsSuccess = TRUE;
                        $res->Code = 200;
                        $res->Message = "비회원 모텔 정보 불러오기 성공";
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
                            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                            $UserIdx = getUserIdx($data->id);
                            $res->Result->PhotoCount = getAccomPhoto($AccomIdx);
                            $res->Result->ReviewReplyCount = getAccomReviewReply($AccomIdx);
                            $res->Result->IsPicked = isAccomPicked($UserIdx, $AccomIdx);
                            $res->Result->AccomDetail = getAccomDetail($AccomIdx);
                            $res->Result->HotelRoom = getHotelRoomMember($AccomIdx, $CheckInDate, $CheckOutDate);
                            $res->Result->OverallReview = getAccomReview($AccomIdx);
                            $res->Result->ReviewDetail = getAccomReviewDetail($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 201;
                            $res->Message = "회원 모텔 정보 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    }
                }
            }

        case "getReviews":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $AccomIdx = $vars['AccomIdx'];
            $order = 1;
            if($_GET['order'] != null)
                $order = $_GET['order'];
            if(!isValidAccom($AccomIdx)) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "존재하지 않는 숙박입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else {
                if($order < 1 || $order > 3) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 402;
                    $res->Message = "올바르지 않은 정렬 방식입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    if ($jwt == null) { // 비회원
                        if ($order == 1) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "리뷰 최근 작성순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 2) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getReviewsRatingHigh($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "리뷰 별점 높은순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 3) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getReviewsRatingLow($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "리뷰 별점 낮은순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    } else { // 회원
                        if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                            $res->IsSuccess = FALSE;
                            $res->Code = 401;
                            $res->Message = "유효하지 않은 토큰입니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            return;
                        } else {
                            if ($order == 1) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 200;
                                $res->Message = "리뷰 최근 작성순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 2) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingHigh($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 200;
                                $res->Message = "리뷰 별점 높은순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 3) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingLow($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 200;
                                $res->Message = "리뷰 별점 낮은순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            }
                        }
                    }
                }
            }

        case "getPhotoReviews":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $AccomIdx = $vars['AccomIdx'];
            $order = 1;
            if($_GET['order'] != null)
                $order = $_GET['order'];
            if(!isValidAccom($AccomIdx)) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "존재하지 않는 숙박입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else {
                if($order < 1 || $order > 3) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 402;
                    $res->Message = "올바르지 않은 정렬 방식입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    if ($jwt == null) { // 비회원
                        if ($order == 1) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getPhotoReviewsNewOrder($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "사진 리뷰 최근 작성순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 2) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getPhotoReviewsRatingHigh($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "사진 리뷰 별점 높은순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 3) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getPhotoReviewsRatingLow($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "사진 리뷰 별점 낮은순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                    } else { // 회원
                        if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                            $res->IsSuccess = FALSE;
                            $res->Code = 401;
                            $res->Message = "유효하지 않은 토큰입니다";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            return;
                        } else {
                            if ($order == 1) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 200;
                                $res->Message = "리뷰 최근 작성순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 2) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingHigh($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 200;
                                $res->Message = "리뷰 별점 높은순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 3) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingLow($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 200;
                                $res->Message = "리뷰 별점 낮은순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            }
                        }
                    }
                }
            }
    }

} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
