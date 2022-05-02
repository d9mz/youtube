<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "like_type"   => @$_GET['t'],
    "like_from"   => @$_SESSION['youtube'],
    "like_target" => @$_GET['v'],
    "like_dummy_type" => [
        "l",
        "d",
    ],
    "liked"       => false,
    "disliked"    => false,
    "error"          => (object) [
        "message"    => "",
        "type"       => 0,
        /*
            0 - success
            1 - error
            2 - warning
        */
    ],
];

if(!isset($_GET['v']) || !isset($_GET['t'])) {
    $request->error->type    = 1;
    $request->error->message = "Like Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in.";
}

if(!$select->video_exists($_GET['v'])) {
    $request->error->type    = 1;
    $request->error->message = "This video does not exist.";
}

// Likes Check
$videos_search = $__db->prepare(
    "SELECT * FROM votes WHERE vote_type = :vote_type AND vote_target = :vote_target AND vote_from = :vote_from LIMIT 1"
);
$videos_search->bindParam(":vote_type", $request->like_dummy_type[0]);
$videos_search->bindParam(":vote_target", $request->like_target);
$videos_search->bindParam(":vote_from", $request->like_from);
$videos_search->execute();

while($video_n = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
    $request->liked = true;
}

// DisLikes Check
$videos_search = $__db->prepare(
    "SELECT * FROM votes WHERE vote_type = :vote_type AND vote_target = :vote_target AND vote_from = :vote_from LIMIT 1"
);
$videos_search->bindParam(":vote_type", $request->like_dummy_type[1]);
$videos_search->bindParam(":vote_target", $request->like_target);
$videos_search->bindParam(":vote_from", $request->like_from);
$videos_search->execute();

while($video_n = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
    $request->disliked = true;
}

if($request->error->message == "") {
    if($request->liked == false && $request->disliked == false) {
        $stmt = $__db->prepare(
            "INSERT INTO votes 
                (vote_target, vote_from, vote_type) 
             VALUES 
                (?, ?, ?)"
        );
        $stmt->execute([
            $request->like_target,
            $_SESSION['youtube'],
            $request->like_type,
        ]);
    } 

    if($request->liked == true && $request->disliked == false) {
        if($request->like_type == "l") {
            $stmt = $__db->prepare("DELETE FROM votes WHERE vote_target = ? AND vote_from = ? AND vote_type = ?");
            $stmt->execute(
                [
                    $request->like_target,
                    $request->like_from,
                    "l",
                ]
            );
        } else if($request->like_type == "d") {
            $stmt = $__db->prepare("DELETE FROM votes WHERE vote_target = ? AND vote_from = ? AND vote_type = ?");
            $stmt->execute(
                [
                    $request->like_target,
                    $request->like_from,
                    "l",
                ]
            );

            $stmt = $__db->prepare(
                "INSERT INTO votes 
                    (vote_target, vote_from, vote_type) 
                VALUES 
                    (?, ?, ?)"
            );
            $stmt->execute([
                $request->like_target,
                $_SESSION['youtube'],
                $request->like_type,
            ]);
        }
    } else if($request->liked == false && $request->disliked == true) {
        if($request->like_type == "l") {
            $stmt = $__db->prepare("DELETE FROM votes WHERE vote_target = ? AND vote_from = ? AND vote_type = ?");
            $stmt->execute(
                [
                    $request->like_target,
                    $request->like_from,
                    "d",
                ]
            );

            $stmt = $__db->prepare(
                "INSERT INTO votes 
                    (vote_target, vote_from, vote_type) 
                VALUES 
                    (?, ?, ?)"
            );
            $stmt->execute([
                $request->like_target,
                $_SESSION['youtube'],
                $request->like_type,
            ]);
        } else if($request->like_type == "d") {
            $stmt = $__db->prepare("DELETE FROM votes WHERE vote_target = ? AND vote_from = ? AND vote_type = ?");
            $stmt->execute(
                [
                    $request->like_target,
                    $request->like_from,
                    "d",
                ]
            );
        }
    }
    // OPTIMIZE THIS PLEASE LATER
    
    header("Location: /watch?v=" . $request->like_target);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /watch?v=" . $request->like_target);
}