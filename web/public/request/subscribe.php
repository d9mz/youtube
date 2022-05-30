<?php
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "sub_to"     => @$_GET['u'],
    "sub_ref"    => @$_GET['v'],
    "sub_from"   => @$_SESSION['youtube'],
    "subscribed" => false,
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

if(!isset($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "Sub Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in.";
}

if(!$select->user_exists($_GET['u'])) {
    $request->error->type    = 1;
    $request->error->message = "This user does not exist.";
}

// Sub Check
$sub_search = $__db->prepare(
    "SELECT * FROM subscribers WHERE subscription_to = :sub_to AND subscription_from = :sub_from LIMIT 1"
);
$sub_search->bindParam(":sub_to", $request->sub_to);
$sub_search->bindParam(":sub_from", $request->sub_from);
$sub_search->execute();

while($subscription = $sub_search->fetch(PDO::FETCH_ASSOC)) { 
    $request->subscribed = true;
}

if($request->error->message == "") {
    if($request->subscribed == false) {
        $stmt = $__db->prepare(
            "INSERT INTO subscribers 
                (subscription_to, subscription_from) 
             VALUES 
                (?, ?)"
        );
        $stmt->execute([
            $request->sub_to,
            $request->sub_from,
        ]);
    } else {
        $stmt = $__db->prepare("DELETE FROM subscribers WHERE subscription_to = ? AND subscription_from = ?");
        $stmt->execute(
            [
                $request->sub_to,
                $request->sub_from,
            ]
        );
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
}