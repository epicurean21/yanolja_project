<?php

function getAccomPhoto($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
	COUNT(AccomodationPhotos.PhotoIdx) as PhotoCount
FROM
	AccomodationPhotos
WHERE
	AccomodationPhotos.AccomIdx = ?
	AND AccomodationPhotos.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getAccomReview($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
	avg(OverallRating) as OverallRating, count(ReviewIdx) as ReviewCount, 
    avg(KindnessRating) as KindnessRating, avg(CleanlinessRating) as CleanlinessRating,
    avg(ConvenienceRating) as ConvenienceRating, avg(LocationRating) as LocationRating
FROM
	AccommodationReview
WHERE
	AccommodationReview.AccomIdx = ?
	AND AccommodationReview.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getAccomReviewDetail($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
    AccommodationReview.CreatedAt,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ? AND AccommodationReview.IsDeleted = 'N'
    LIMIT 2;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getBestReviews($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    BR.UserIdx,
    BR.ReviewIdx,
    UR.UserName,
    RoomName,
    UR.ReserveType,
    BR.ReviewContent,
    BR.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, BR.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, BR.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, BR.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, BR.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, BR.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, BR.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, BR.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, BR.CreatedAt, now()), '일 전')
								ELSE
									BR.CreatedAt
							END
					END
			END
	END as WrittenTime,
    CASE
        WHEN
            BR.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = BR.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = BR.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = BR.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = BR.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = BR.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    (select AccommodationReview.AccomIdx, AccommodationReview.ReviewIdx,AccommodationReview.UserIdx,AccommodationReview.ReviewContent
 ,AccommodationReview.IsPhotoReview, AccommodationReview.OverallRating, AccommodationReview.KindnessRating, AccommodationReview.CleanlinessRating,
 AccommodationReview.ConvenienceRating, AccommodationReview.LocationRating, AccommodationReview.CreatedAt, AccommodationReview.UpdatedAt, AccommodationReview.isDeleted
 from (AccommodationReview join BestReview 
ON (AccommodationReview.AccomIdx = BestReview.AccomIdx and AccommodationReview.ReviewIdx = BestReview.ReviewIdx))) BR 
    JOIN
    (SELECT 
        UserIdx, UserName, Reservation.AccomIdx, RoomName, ReserveType, ReserveIdx
    FROM
        User
			JOIN (Reservation JOIN Room On (Reservation.AccomIdx = Room.AccomIdx and Reservation.RoomIdx = Room.RoomIdx))
		USING (UserIdx)) UR ON (UR.UserIdx = BR.UserIdx
        AND UR.AccomIdx = BR.AccomIdx)
WHERE
    BR.AccomIdx = ?     AND BR.IsDeleted = 'N'
ORDER BY CreatedAt DESC
LIMIT 2;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getReviewsNewOrder($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, AccommodationReview.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, AccommodationReview.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, AccommodationReview.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, AccommodationReview.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, AccommodationReview.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, AccommodationReview.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, AccommodationReview.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, AccommodationReview.CreatedAt, now()), '일 전')
								ELSE
									AccommodationReview.CreatedAt
							END
					END
			END
		END as WrittenTime,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ?     AND AccommodationReview.IsDeleted = 'N'
ORDER BY CreatedAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getReviewsRatingHigh($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, AccommodationReview.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, AccommodationReview.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, AccommodationReview.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, AccommodationReview.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, AccommodationReview.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, AccommodationReview.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, AccommodationReview.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, AccommodationReview.CreatedAt, now()), '일 전')
								ELSE
									AccommodationReview.CreatedAt
							END
					END
			END
		END as WrittenTime,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ?     AND AccommodationReview.IsDeleted = 'N'
ORDER BY OverallRating DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getReviewsRatingLow($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, AccommodationReview.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, AccommodationReview.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, AccommodationReview.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, AccommodationReview.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, AccommodationReview.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, AccommodationReview.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, AccommodationReview.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, AccommodationReview.CreatedAt, now()), '일 전')
								ELSE
									AccommodationReview.CreatedAt
							END
					END
			END
		END as WrittenTime,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ?     AND AccommodationReview.IsDeleted = 'N'
ORDER BY OverallRating;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getPhotoReviewsNewOrder($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, AccommodationReview.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, AccommodationReview.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, AccommodationReview.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, AccommodationReview.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, AccommodationReview.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, AccommodationReview.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, AccommodationReview.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, AccommodationReview.CreatedAt, now()), '일 전')
								ELSE
									AccommodationReview.CreatedAt
							END
					END
			END
		END as WrittenTime,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ?
    AND AccommodationReview.IsPhotoReview = 'Y'
    AND AccommodationReview.IsDeleted = 'N'
ORDER BY CreatedAt DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPhotoReviewsRatingHigh($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, AccommodationReview.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, AccommodationReview.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, AccommodationReview.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, AccommodationReview.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, AccommodationReview.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, AccommodationReview.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, AccommodationReview.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, AccommodationReview.CreatedAt, now()), '일 전')
								ELSE
									AccommodationReview.CreatedAt
							END
					END
			END
		END as WrittenTime,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ?
    AND AccommodationReview.IsPhotoReview = 'Y'
    AND AccommodationReview.IsDeleted = 'N'
ORDER BY OverallRating DESC;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPhotoReviewsRatingLow($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    AccommodationReview.UserIdx,
    AccommodationReview.ReviewIdx,
    UR.UserName,
    UR.ReserveType,
    AccommodationReview.ReviewContent,
    AccommodationReview.OverallRating,
	CASE
		WHEN
			(timestampdiff(second, AccommodationReview.CreatedAt, now()) < 60)
		THEN
			concat(timestampdiff(second, AccommodationReview.CreatedAt, now()), '초 전')
		ELSE
			CASE
				WHEN
					(timestampdiff(minute, AccommodationReview.CreatedAt, now()) < 60)
				THEN
					concat(timestampdiff(minute, AccommodationReview.CreatedAt, now()), '분 전')
				ELSE
					CASE
						WHEN
							(timestampdiff(hour, AccommodationReview.CreatedAt, now()) < 24)
						THEN
							concat(timestampdiff(hour, AccommodationReview.CreatedAt, now()), '시간 전')
						ELSE
							CASE
								WHEN
									(timestampdiff(day, AccommodationReview.CreatedAt, now()) < 8)
								THEN
									concat(timestampdiff(day, AccommodationReview.CreatedAt, now()), '일 전')
								ELSE
									AccommodationReview.CreatedAt
							END
					END
			END
		END as WrittenTime,
    CASE
        WHEN
            AccommodationReview.IsPhotoReview = 'Y'
        THEN
            (SELECT 
                    GROUP_CONCAT(PhotoUrl)
                FROM
                    ReviewPhoto
                WHERE
                    ReviewPhoto.ReviewIdx = AccommodationReview.ReviewIdx
                GROUP BY ReviewIdx)
    END AS ReviewPhoto,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT ReplyText
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)
	END as ReviewReply,
    CASE
		WHEN
			(SELECT EXISTS (Select ReviewIdx FROM ReviewReply WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx)) = 1
		THEN
			(SELECT CreatedAt
				FROM ReviewReply
			WHERE ReviewReply.ReviewIdx = AccommodationReview.ReviewIdx
            AND ReviewReply.IsDeleted = 'N')
	END as ReplyWrittenTime
FROM
    AccommodationReview
        JOIN
    (SELECT 
        UserIdx, UserName, AccomIdx, ReserveType, ReserveIdx
    FROM
        User
    JOIN Reservation USING (UserIdx)) UR ON (UR.UserIdx = AccommodationReview.UserIdx
        AND UR.AccomIdx = AccommodationReview.AccomIdx)
WHERE
    AccommodationReview.AccomIdx = ?
    AND AccommodationReview.IsPhotoReview = 'Y'
    AND AccommodationReview.IsDeleted = 'N'
ORDER BY OverallRating;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getAccomReviewReply($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
	COUNT(ReviewReply.ReviewIdx) as ReplyCount
FROM
	ReviewReply
WHERE
	ReviewReply.AccomIdx = ?
	AND ReviewReply.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getAccomDetail($AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT AccomIdx, AccomName, AccomIntroduction, AccomThumbnailUrl, AccomContact,
AccomLatitude, AccomLongitude, AccomTheme, AccomGuide, ReserveInfo
From Accommodation
WHERE AccomIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isAccomPicked($UserIdx, $AccomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
	SELECT UserIdx
    FROM UserPick
    WHERE AccomIdx = ? AND UserIdx = ? 
    AND isDeleted = 'N') exist";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx, $UserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function getMotelRoom($AccomIdx, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "
SELECT 
    Room.RoomIdx,
    Room.RoomName,
    Room.StandardCapacity,
    Room.MaxCapacity,
    Room.RoomThumbnailUrl,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.WeekdayTime
        ELSE T1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.PartTimeWeekdayPrice
        ELSE T1.PartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.AllDayWeekdayTime
        ELSE T1.AllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.AllDayWeekdayPrice
        ELSE T1.AllDayWeekendPrice
    END AS AllDayPrice
FROM
    Room
        JOIN
    (SELECT 
        PartTimeInfo.AccomIdx,
            PartTimeInfo.WeekdayTime,
            PartTimeInfo.WeekendTime,
            PartTimeInfo.MemberWeekdayTime,
            PartTimeInfo.MemberWeekendTime,
            PartTimePrice.RoomIdx,
            PartTimePrice.PartTimeWeekdayPrice,
            PartTimePrice.PartTimeWeekendPrice,
            PartTimePrice.MemberPartTimeWeekdayPrice,
            PartTimePrice.MemberPartTimeWeekendPrice,
            AllDayInfo.WeekdayTime AS AllDayWeekdayTime,
            AllDayInfo.WeekendTime AS AllDayWeekendTime,
            AllDayInfo.MemberWeekdayTime AS MemberAllDayWeekdayTime,
            AllDayInfo.MemberWeekendTime AS MemberAllDayWeekendTime,
            AllDayPrice.AllDayWeekdayPrice,
            AllDayPrice.AllDayWeekendPrice,
            AllDayPrice.MemberAllDayWeekdayPrice,
            AllDayPrice.MemberAllDayWeekendPrice
    FROM
        (PartTimeInfo
    JOIN PartTimePrice ON PartTimeInfo.AccomIdx = PartTimePrice.AccomIdx)
    JOIN (AllDayInfo
    JOIN AllDayPrice ON AllDayInfo.AccomIdx = AllDayPrice.AccomIdx) ON (PartTimeInfo.AccomIdx = AllDayInfo.AccomIdx
        AND PartTimePrice.RoomIdx = AllDayPrice.RoomIdx)
    WHERE
        PartTimeInfo.AccomIdx = ?) AS T1
WHERE
    Room.RoomIdx = T1.RoomIdx
        AND Room.AccomIdx = T1.AccomIdx
        AND Room.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $CheckInDate, $CheckInDate,$CheckInDate, $CheckInDate,
        $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getMotelRoomMember($AccomIdx, $CheckInDate)
{
    $pdo = pdoSqlConnect();
    $query = "
SELECT 
    Room.RoomIdx,
    Room.RoomName,
    Room.StandardCapacity,
    Room.MaxCapacity,
    Room.RoomThumbnailUrl,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.WeekdayTime
        ELSE T1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.MemberPartTimeWeekdayPrice
        ELSE T1.MemberPartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.AllDayWeekdayTime
        ELSE T1.AllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.MemberAllDayWeekdayPrice
        ELSE T1.MemberAllDayWeekendPrice
    END AS AllDayPrice
FROM
    Room
        JOIN
    (SELECT 
        PartTimeInfo.AccomIdx,
            PartTimeInfo.WeekdayTime,
            PartTimeInfo.WeekendTime,
            PartTimeInfo.MemberWeekdayTime,
            PartTimeInfo.MemberWeekendTime,
            PartTimePrice.RoomIdx,
            PartTimePrice.PartTimeWeekdayPrice,
            PartTimePrice.PartTimeWeekendPrice,
            PartTimePrice.MemberPartTimeWeekdayPrice,
            PartTimePrice.MemberPartTimeWeekendPrice,
            AllDayInfo.WeekdayTime AS AllDayWeekdayTime,
            AllDayInfo.WeekendTime AS AllDayWeekendTime,
            AllDayInfo.MemberWeekdayTime AS MemberAllDayWeekdayTime,
            AllDayInfo.MemberWeekendTime AS MemberAllDayWeekendTime,
            AllDayPrice.AllDayWeekdayPrice,
            AllDayPrice.AllDayWeekendPrice,
            AllDayPrice.MemberAllDayWeekdayPrice,
            AllDayPrice.MemberAllDayWeekendPrice
    FROM
        (PartTimeInfo
    JOIN PartTimePrice ON PartTimeInfo.AccomIdx = PartTimePrice.AccomIdx)
    JOIN (AllDayInfo
    JOIN AllDayPrice ON AllDayInfo.AccomIdx = AllDayPrice.AccomIdx) ON (PartTimeInfo.AccomIdx = AllDayInfo.AccomIdx
        AND PartTimePrice.RoomIdx = AllDayPrice.RoomIdx)
    WHERE
        PartTimeInfo.AccomIdx = ?) AS T1
WHERE
    Room.RoomIdx = T1.RoomIdx
        AND Room.AccomIdx = T1.AccomIdx
        AND Room.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $CheckInDate, $CheckInDate,
        $CheckInDate, $CheckInDate,$CheckInDate, $CheckInDate,
        $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getHotelRoom($AccomIdx, $CheckInDate, $CheckOutDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    Room.RoomIdx,
    Room.RoomName,
    Room.StandardCapacity,
    Room.MaxCapacity,
    Room.RoomThumbnailUrl,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.AllDayWeekdayTime
        ELSE T1.AllDayWeekendTime
    END AS AllDayTime,
        CASE
            WHEN
                (SELECT EXISTS(
                    SELECT *
                    FROM Reservation
                    WHERE Reservation.AccomIdx = ?
                    AND Reservation.RoomIdx = Room.RoomIdx
                    AND Reservation.isDeleted = 'N'
                    AND (
                            ((timestampdiff(DAY, Reservation.CheckInDate, ?) <= 1)
                            AND (timestampdiff(DAY, Reservation.CheckOutDate, ?) <= 1))
                                OR
                            ((timestampdiff(DAY, ?, Reservation.CheckInDate) >= 1)
                            AND (timestampdiff(DAY, ?, Reservation.CheckOutDate) >= 1)))
                    )) = 0
            THEN
                CASE
                    WHEN
                        (DAYOFWEEK(?) > 1
                            && DAYOFWEEK(?) < 6)
                    THEN
                        T1.AllDayWeekdayPrice
                    ELSE T1.AllDayWeekendPrice
                END
            ELSE
                '예약완료'
        END as AllDayPrice
FROM
    Room
        JOIN
    (SELECT 
			AllDayInfo.AccomIdx,
            AllDayPrice.RoomIdx,
            AllDayInfo.WeekdayTime AS AllDayWeekdayTime,
            AllDayInfo.WeekendTime AS AllDayWeekendTime,
            AllDayInfo.MemberWeekdayTime AS MemberAllDayWeekdayTime,
            AllDayInfo.MemberWeekendTime AS MemberAllDayWeekendTime,
            AllDayPrice.AllDayWeekdayPrice,
            AllDayPrice.AllDayWeekendPrice,
            AllDayPrice.MemberAllDayWeekdayPrice,
            AllDayPrice.MemberAllDayWeekendPrice
    FROM
       (AllDayInfo
    JOIN AllDayPrice ON AllDayInfo.AccomIdx = AllDayPrice.AccomIdx) 
    WHERE
        AllDayInfo.AccomIdx = ?) AS T1
WHERE
    Room.RoomIdx = T1.RoomIdx
        AND Room.AccomIdx = T1.AccomIdx
        AND Room.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $AccomIdx, $CheckOutDate, $CheckOutDate, $CheckInDate,
        $CheckInDate, $CheckInDate, $CheckInDate, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function getHotelRoomMember($AccomIdx, $CheckInDate, $CheckOutDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    Room.RoomIdx,
    Room.RoomName,
    Room.StandardCapacity,
    Room.MaxCapacity,
    Room.RoomThumbnailUrl,
    CASE
        WHEN
            (DAYOFWEEK(?) > 1
                && DAYOFWEEK(?) < 6)
        THEN
            T1.AllDayWeekdayTime
        ELSE T1.AllDayWeekendTime
    END AS AllDayTime,
     CASE
		WHEN
			(SELECT EXISTS(
				SELECT *
				FROM Reservation
				WHERE Reservation.AccomIdx = ?
				AND Reservation.RoomIdx = Room.RoomIdx
				AND Reservation.isDeleted = 'N'
				AND (
						((timestampdiff(DAY, Reservation.CheckInDate, ?) <= 1)
						AND (timestampdiff(DAY, Reservation.CheckOutDate, ?) <= 1))
							OR
						((timestampdiff(DAY, ?, Reservation.CheckInDate) >= 1)
						AND (timestampdiff(DAY, ?, Reservation.CheckOutDate) >= 1)))
				)) = 0
		THEN
			CASE
				WHEN
					(DAYOFWEEK(?) > 1
						&& DAYOFWEEK(?) < 6)
				THEN
					T1.MemberAllDayWeekdayPrice
				ELSE T1.MemberAllDayWeekendPrice
			END
		ELSE
			'예약완료'
	END as AllDayPrice
FROM
    Room
        JOIN
    (SELECT 
			AllDayInfo.AccomIdx,
            AllDayPrice.RoomIdx,
            AllDayInfo.WeekdayTime AS AllDayWeekdayTime,
            AllDayInfo.WeekendTime AS AllDayWeekendTime,
            AllDayInfo.MemberWeekdayTime AS MemberAllDayWeekdayTime,
            AllDayInfo.MemberWeekendTime AS MemberAllDayWeekendTime,
            AllDayPrice.AllDayWeekdayPrice,
            AllDayPrice.AllDayWeekendPrice,
            AllDayPrice.MemberAllDayWeekdayPrice,
            AllDayPrice.MemberAllDayWeekendPrice
    FROM
       (AllDayInfo
    JOIN AllDayPrice ON AllDayInfo.AccomIdx = AllDayPrice.AccomIdx) 
    WHERE
        AllDayInfo.AccomIdx = ?) AS T1
WHERE
    Room.RoomIdx = T1.RoomIdx
        AND Room.AccomIdx = T1.AccomIdx
        AND Room.isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$CheckInDate, $CheckInDate, $AccomIdx, $CheckOutDate, $CheckOutDate, $CheckInDate,
        $CheckInDate, $CheckInDate, $CheckInDate, $AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getBestReviewIdx($AccomIdx) {
    $pdo = pdoSqlConnect();
    $query = "SELECT ReviewIdx FROM BestReview 
WHERE AccomIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$AccomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



/*여기부턴 현재 베타
function searchMotelByArea($RegionIdx, $startDate, $endDate, $peopleNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.WeekdayTime
        ELSE F1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.PartTimeWeekdayPrice
        ELSE F1.PartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.AllDayWeekdayTime
        ELSE F1.AllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.AllDayWeekdayPrice
        ELSE F1.AllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomCity, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted, RegionGroup.RegionGroupIdx
FROM (Accommodation
    JOIN RegionGroup USING (RegionIdx))) as AR1
JOIN (
SELECT * 
FROM
(
SELECT * 
FROM
(SELECT PartTimeInfo.AccomIdx, PartTimeInfo.WeekdayTime, PartTimeInfo.WeekendTime, 
		PartTimeInfo.MemberWeekdayTime, PartTimeInfo.MemberWeekendTime
FROM PartTimeInfo) as P1 join 
(Select 
		PartTimePrice.AccomIdx as AccomIdx2,min(PartTimeWeekdayPrice) as PartTimeWeekdayPrice, min(PartTimeWeekendPrice) as PartTimeWeekendPrice
	From
		PartTimePrice
	GROUP BY PartTimePrice.AccomIdx) as P2 ON (P1.AccomIdx = P2.AccomIdx2)
GROUP BY P1.AccomIdx, P1.WeekdayTime, P1.WeekendTime, 
		P1.MemberWeekdayTime, P1.MemberWeekendTime) T1
JOIN
(SELECT *
FROM
(
SELECT AllDayInfo.AccomIdx as AccomIdx3, AllDayInfo.WeekdayTime as AllDayWeekdayTime, AllDayInfo.WeekendTime as AllDayWeekendTime, 
		AllDayInfo.MemberWeekdayTime as AllDayMemberWeekdayTime, AllDayInfo.MemberWeekendTime as AllDayMemberWeekendTime
FROM AllDayInfo) as A1 
	join 
(Select 
		AllDayPrice.AccomIdx as AccomIdx4, min(AllDayWeekdayPrice) as AllDayWeekdayPrice, min(AllDayWeekendPrice) as AllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) as A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
GROUP BY A1.AccomIdx3, A1.AllDayWeekdayTime, A1.AllDayWeekendTime, 
		A1.AllDayMemberWeekdayTime, A1.AllDayMemberWeekendTime) T2 ON (T1.AccomIdx = T2.AccomIdx3)) F1
        ON (AR1.AccomIdx = F1.AccomIdx)
 WHERE
  RegionGroupIdx = ?
        AND AR1.AccomType = 'M'
        AND AR1.isDeleted = 'N') AS a1 JOIN
        (SELECT 
        Accommodation.AccomIdx,
            AVG(OverallRating) AS OverallRating,
            COUNT(ReviewIdx) AS ReviewCount
    FROM
        Accommodation
    JOIN AccommodationReview ON (Accommodation.AccomIdx = AccommodationReview.AccomIdx)
    WHERE
        Accommodation.Accomtype = 'M'
            AND AccommodationReview.isDeleted = 'N'
            AND Accommodation.isDeleted = 'N'
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;
";

    $st = $pdo->prepare($query);
    $st->execute([$RegionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function MemberSearchMotelByArea($RegionIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
(SELECT 
AR1.AccomIdx,
    AR1.AccomThumbnailUrl,
    AR1.AccomName,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.WeekdayTime
        ELSE F1.WeekendTime
    END AS PartTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.MemberPartTimeWeekdayPrice
        ELSE F1.MemberPartTimeWeekendPrice
    END AS PartTimePrice,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.MemberAllDayWeekdayTime
        ELSE F1.MemberAllDayWeekendTime
    END AS AllDayTime,
    CASE
        WHEN
            DAYOFWEEK(NOW()) = 2
                || DAYOFWEEK(NOW()) = 3
                || DAYOFWEEK(NOW()) = 4
                || DAYOFWEEK(NOW()) = 5
        THEN
            F1.MemberAllDayWeekdayPrice
        ELSE F1.MemberAllDayWeekendPrice
    END AS AllDayPrice
FROM 
(SELECT Accommodation.RegionIdx, Accommodation.AccomIdx, Accommodation.AccomName, Accommodation.AccomType, Accommodation.AccomIntroduction, Accommodation.AccomThumbnailUrl, Accommodation.AccomCity, Accommodation.AccomAddress,
	Accommodation.AccomTheme, Accommodation.AccomGuide, Accommodation.ReserveInfo, Accommodation.AccomLatitude, Accommodation.AccomLongtitude, Accommodation.isDeleted, RegionGroup.RegionGroupIdx
FROM (Accommodation
    JOIN RegionGroup USING (RegionIdx))) as AR1
JOIN (
SELECT * 
FROM
(
SELECT * 
FROM
(SELECT PartTimeInfo.AccomIdx, PartTimeInfo.WeekdayTime, PartTimeInfo.WeekendTime, 
		PartTimeInfo.MemberWeekdayTime, PartTimeInfo.MemberWeekendTime
FROM PartTimeInfo) as P1 join 
(Select 
		PartTimePrice.AccomIdx as AccomIdx2,min(MemberPartTimeWeekdayPrice) as MemberPartTimeWeekdayPrice, min(MemberPartTimeWeekendPrice) as MemberPartTimeWeekendPrice
	From
		PartTimePrice
	GROUP BY PartTimePrice.AccomIdx) as P2 ON (P1.AccomIdx = P2.AccomIdx2)
GROUP BY P1.AccomIdx, P1.WeekdayTime, P1.WeekendTime, 
		P1.MemberWeekdayTime, P1.MemberWeekendTime) T1
JOIN
(SELECT *
FROM
(
SELECT AllDayInfo.AccomIdx as AccomIdx3, AllDayInfo.WeekdayTime as AllDayWeekdayTime, AllDayInfo.WeekendTime as AllDayWeekendTime, 
		AllDayInfo.MemberWeekdayTime as MemberAllDayWeekdayTime, AllDayInfo.MemberWeekendTime as MemberAllDayWeekendTime
FROM AllDayInfo) as A1 
	join 
(Select 
		AllDayPrice.AccomIdx as AccomIdx4, min(MemberAllDayWeekdayPrice) as MemberAllDayWeekdayPrice, min(MemberAllDayWeekendPrice) as MemberAllDayWeekendPrice
	From
		AllDayPrice
	GROUP BY AllDayPrice.AccomIdx) as A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
GROUP BY A1.AccomIdx3, A1.AllDayWeekdayTime, A1.AllDayWeekendTime, 
		A1.MemberAllDayWeekdayTime, A1.MemberAllDayWeekendTime) T2 ON (T1.AccomIdx = T2.AccomIdx3)) F1
        ON (AR1.AccomIdx = F1.AccomIdx)
 WHERE
  RegionGroupIdx = ?
        AND AR1.AccomType = 'M'
        AND AR1.isDeleted = 'N') AS a1 JOIN
        (SELECT 
        Accommodation.AccomIdx,
            AVG(OverallRating) AS OverallRating,
            COUNT(ReviewIdx) AS ReviewCount
    FROM
        Accommodation
    JOIN AccommodationReview ON (Accommodation.AccomIdx = AccommodationReview.AccomIdx)
    WHERE
        Accommodation.Accomtype = 'M'
            AND AccommodationReview.isDeleted = 'N'
            AND Accommodation.isDeleted = 'N'
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;;
";

    $st = $pdo->prepare($query);
    $st->execute([$RegionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function MemberSearchHotelByArea($RegionIdx, $startDate, $endDate)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT 
    *
FROM
    (SELECT 
        AR1.AccomIdx,
            AR1.AccomThumbnailUrl,
            AR1.AccomName,
            CASE
                WHEN
                    DAYOFWEEK(NOW()) = 2
                        || DAYOFWEEK(NOW()) = 3
                        || DAYOFWEEK(NOW()) = 4
                        || DAYOFWEEK(NOW()) = 5
                THEN
                    F1.MemberAllDayWeekdayTime
                ELSE F1.MemberAllDayWeekendTime
            END AS AllDayTime,
            CASE
                WHEN
                    DAYOFWEEK(NOW()) = 2
                        || DAYOFWEEK(NOW()) = 3
                        || DAYOFWEEK(NOW()) = 4
                        || DAYOFWEEK(NOW()) = 5
                THEN
                    F1.MemberAllDayWeekdayPrice
                ELSE F1.MemberAllDayWeekendPrice
            END AS AllDayPrice
    FROM
        (SELECT 
        Accommodation.RegionIdx,
            Accommodation.AccomIdx,
            Accommodation.AccomName,
            Accommodation.AccomType,
            Accommodation.AccomIntroduction,
            Accommodation.AccomThumbnailUrl,
            Accommodation.AccomCity,
            Accommodation.AccomAddress,
            Accommodation.AccomTheme,
            Accommodation.AccomGuide,
            Accommodation.ReserveInfo,
            Accommodation.AccomLatitude,
            Accommodation.AccomLongtitude,
            Accommodation.isDeleted,
            RegionGroup.RegionGroupIdx
    FROM
        (Accommodation
    JOIN RegionGroup USING (RegionIdx))) AS AR1
    JOIN (SELECT 
        *
    FROM
        (SELECT 
        *
    FROM
        (SELECT 
        AllDayInfo.AccomIdx AS AccomIdx3,
            AllDayInfo.WeekdayTime AS AllDayWeekdayTime,
            AllDayInfo.WeekendTime AS AllDayWeekendTime,
            AllDayInfo.MemberWeekdayTime AS MemberAllDayWeekdayTime,
            AllDayInfo.MemberWeekendTime AS MemberAllDayWeekendTime
    FROM
        AllDayInfo) AS A1
    JOIN (SELECT 
        AllDayPrice.AccomIdx AS AccomIdx4,
            MIN(MemberAllDayWeekdayPrice) AS MemberAllDayWeekdayPrice,
            MIN(MemberAllDayWeekendPrice) AS MemberAllDayWeekendPrice
    FROM
        AllDayPrice
    GROUP BY AllDayPrice.AccomIdx) AS A2 ON (A1.AccomIdx3 = A2.AccomIdx4)
    GROUP BY A1.AccomIdx3 , A1.AllDayWeekdayTime , A1.AllDayWeekendTime , A1.MemberAllDayWeekdayTime , A1.MemberAllDayWeekendTime) as D1) as F1 ON (AR1.AccomIdx = F1.AccomIdx3)
    WHERE
        RegionGroupIdx = ?
            AND AR1.AccomType = 'H'
            AND AR1.isDeleted = 'N') AS a1
        JOIN
    (SELECT 
        Accommodation.AccomIdx,
            AVG(OverallRating) AS OverallRating,
            COUNT(ReviewIdx) AS ReviewCount
    FROM
        Accommodation
    JOIN AccommodationReview ON (Accommodation.AccomIdx = AccommodationReview.AccomIdx)
    WHERE
        Accommodation.Accomtype = 'H'
            AND AccommodationReview.isDeleted = 'N'
            AND Accommodation.isDeleted = 'N'
    GROUP BY Accommodation.AccomIdx) AS a2 ON a1.AccomIdx = a2.AccomIdx;;
";

    $st = $pdo->prepare($query);
    $st->execute([$RegionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidRegion($RegionGroupIdx) {
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select RegionGroupIdx from RegionGroup where RegionGroupIdx = ? and isDeleted = 'N') as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$RegionGroupIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);

}
*/