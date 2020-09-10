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

        case "search":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $CheckInDate = date("Y/m/d");
            $CheckOutDate = date("Y-m-d", strtotime("+1 day", strtotime($CheckInDate)));
            $AdultNum = 2;
            $ChildNum = 0;
            $Keyword = $_GET['Keyword'];
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
            }
            if($_GET['AdultNum'] != null) {
                $AdultNum = $_GET['AdultNum'];
            }
            if($_GET['ChildNum'] != null) {
                $ChildNum = $_GET['ChildNum'];
            }
            if($_GET['Keyword'] == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "검색어를 입력하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $isMember = false;
             //우선 검색기록 저장
            if($jwt != null) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 401;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    $isMember = true;
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserIdx = getUserIdx($data->id);
                    postUserSearchHistory($UserIdx, $CheckInDate, $CheckOutDate, $AdultNum, $ChildNum, $Keyword);
                }
            }

            // 모텔중 숙소명에 검색값을 갖고있는걸 찾는다. 그다음 해당 숙소의 RegionIdx를 가지고 motelGroup에 검색
//            $motelCnt = getMotelCount();
//            $motels = array();
//            while($motelCnt) {
//                if(isSearchMotelExists($Keyword)) { // 숙수 명 존재
//                    $AccomIdx = getSearchAccomIdx($Keyword);
//                    $AccomType = getAccomType($AccomIdx);
//
//                }
//            }

            //$searchKey = preg_replace("/\s+/", "",$Keyword);
            $searchKey = explode(' ', $Keyword);
            $cnt = count($searchKey);

            //일단 지역명 검색.
            $RegionIdx = 0;
            for($i = 0; $i < $cnt; $i++) {
                if(isSearchRegionExists($searchKey[$i])) { // 지역명 검색
                    $RegionIdx = getSearchRegionIdx($searchKey[$i]);
                    break; // 처음 발견된걸로 선택
                }
            }
            $RegionHotelIdx = 0;
            $RegionMotelIdx = 0;
            if($RegionIdx != 0) {
                $RegionHotelIdx = getHotelGroupIdx($RegionIdx);
                $RegionMotelIdx = getMotelGroupIdx($RegionIdx);
                $res->Result->Motels = getMotels($isMember, $CheckInDate, $CheckOutDate, $RegionMotelIdx, $AdultNum, $ChildNum);
                $res->Result->Hotles = getHotels($isMember, $CheckInDate, $CheckOutDate, $RegionHotelIdx, $AdultNum, $ChildNum);
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "검색 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postNewReviews":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $UserIdx = 0;
            if($jwt == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "로그인 후 후기를 작성할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 401;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserIdx = getUserIdx($data->id);
                }
            }
            $ReserveIdx = $_GET['ReserveIdx'];
            if(!isValidReserveIdx($ReserveIdx, $UserIdx)) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "예약기록이 존재하지 않는 숙소입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->Result->ReserveInfo = getReserveInfo($ReserveIdx);
            $res->IsSuccess = TRUE;
            $res->Code = 200;
            $res->Message = "리뷰화면 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postReviews":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $UserIdx = null;
            if($jwt == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 400;
                $res->Message = "로그인 후 후기를 작성할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->IsSuccess = FALSE;
                    $res->Code = 401;
                    $res->Message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $UserIdx = getUserIdx($data->id);
                }
            }
            $AccomIdx = null;
            $ReviewContent = null;
            $IsPhotoReview = 'N';
            $OverallRating = null;
            $KindnessRating = null;
            $CleanlinessRating = null;
            $ConvenienceRating = null;
            $LocationRating = null;
            $PhotoUrl = array();
            $ReserveIdx = $req->ReserveIdx;
            if(!isValidReserveIdx($ReserveIdx, $UserIdx)) {
                $res->IsSuccess = FALSE;
                $res->Code = 402;
                $res->Message = "예약기록이 존재하지 않는 숙소입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->IsPhotoReview == 'Y') {
                $IsPhotoReview = 'Y';
                $PhotoUrl[0] = $req->PhotoUrl1;
                $PhotoUrl[1] = $req->PhotoUrl2;
                $PhotoUrl[2] = $req->PhotoUrl3;
                $PhotoUrl[3] = $req->PhotoUrl4;
                $PhotoUrl[4] = $req->PhotoUrl5;
            }
//            if($req->IsPhotoReview == 'Y') {
//                for($i = 1; $i <= 5; $i++) {
//
//                    $PhotoUrl[$i] = $req->PhotoUrl.$i;
//                    echo $PhotoUrl[$i];
//                }
//            }

            //validation필요
            //사진은 최대 5장까지..

            if($req->AccomIdx == null || $req->ReviewContent == null || $req->OverallRating == null
            || $req->KindnessRating == null || $req->CleanlinessRating == null || $req->ConvenienceRating == null
            || $req->LocationRating == null) {
                $res->IsSuccess = FALSE;
                $res->Code = 403;
                $res->Message = "빠짐없이 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else {
                $AccomIdx = $req->AccomIdx ;
                $ReviewContent = $req->ReviewContent ;
                $OverallRating = $req->OverallRating;
                $KindnessRating = $req->KindnessRating;
                $CleanlinessRating = $req->CleanlinessRating;
                $ConvenienceRating = $req->ConvenienceRating;
                $LocationRating = $req->LocationRating;
            }
            $PhotoCnt = 0;

            for($i = 0; $i < 5; $i++) {
                if($PhotoUrl[$i] != null)
                    $PhotoCnt++;
            }

            addNewReview($AccomIdx, $UserIdx, $ReviewContent, $IsPhotoReview,
                $OverallRating, $KindnessRating, $CleanlinessRating,
                $ConvenienceRating, $LocationRating);
            $ReviewIdx = getReviewIdx($AccomIdx, $UserIdx);
            if($IsPhotoReview == 'Y') {
                addNewReviewPhotos($ReviewIdx, $PhotoUrl, $PhotoCnt);
            }
            $res->IsSuccess = TRUE;
            $res->Code = 200;
            $res->Message = "리뷰를 성공적으로 작성하였습니다";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }

} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
