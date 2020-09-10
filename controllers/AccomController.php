<?php
require 'function.php';

const JWT_SECRET_KEY = "Key_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja_TEST_Yanolja";
$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
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
            $order = 0;
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
                if($order < 0 || $order > 3) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 402;
                    $res->Message = "올바르지 않은 정렬 방식입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    if ($jwt == null) { // 비회원
                        if($order == 0) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->BestReviews = getBestReviews($AccomIdx);
                            $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "리뷰 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        }
                        else if ($order == 1) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 201;
                            $res->Message = "리뷰 최근 작성순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 2) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getReviewsRatingHigh($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 202;
                            $res->Message = "리뷰 별점 높은순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 3) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getReviewsRatingLow($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 203;
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
                            if($order == 0) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->BestReviews = getBestReviews($AccomIdx);
                            $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 200;
                            $res->Message = "리뷰 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                            }
                             else if ($order == 1) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsNewOrder($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 201;
                                $res->Message = "리뷰 최근 작성순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 2) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingHigh($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 202;
                                $res->Message = "리뷰 별점 높은순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 3) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingLow($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 203;
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
                            $res->Code = 201;
                            $res->Message = "사진 리뷰 최근 작성순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 2) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getPhotoReviewsRatingHigh($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 202;
                            $res->Message = "사진 리뷰 별점 높은순 불러오기 성공";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            break;
                        } else if ($order == 3) {
                            $res->Result->OverallRating = getAccomReview($AccomIdx);
                            $res->Result->Reviews = getPhotoReviewsRatingLow($AccomIdx);
                            $res->IsSuccess = TRUE;
                            $res->Code = 203;
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
                                $res->Code = 201;
                                $res->Message = "리뷰 최근 작성순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 2) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingHigh($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 202;
                                $res->Message = "리뷰 별점 높은순 불러오기 성공";
                                echo json_encode($res, JSON_NUMERIC_CHECK);
                                break;
                            } else if ($order == 3) {
                                $res->Result->OverallRating = getReviews($AccomIdx);
                                $res->Result->Reviews = getReviewsRatingLow($AccomIdx);
                                $res->IsSuccess = TRUE;
                                $res->Code = 203;
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
