<?php
session_start();

require("../../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$select = new \Database\Select($__db);

$request = (object) [
    "msg_author"   => @$_SESSION['youtube'],
    "msg_target"   => @$_GET['i'],
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

$msg = $select->fetch_table_singlerow($_GET['i'], "inbox", "id");
if(!isset($_GET['i'])) {
    $request->error->type    = 1;
    $request->error->message = "Inbox Parameter Not Set";
}

if(!isset($_SESSION['youtube'])) {
    $request->error->type    = 1;
    $request->error->message = "You are not logged in.";
}

if(!isset($msg['id'])) {
    $request->error->type    = 1;
    $request->error->message = "This message does not exist.";
}

if($msg['inbox_author'] != $_SESSION['youtube']) {
    $request->error->type    = 1;
    $request->error->message = "You do not own this video.";
}

if($request->error->message == "") {
    $stmt = $__db->prepare("DELETE FROM inbox WHERE id = ? AND inbox_author = ?");
    $stmt->execute(
        [
            $request->msg_target,
            $request->msg_author,
        ]
    );
    
    header("Location: /inbox/");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->error->message,
        "type" => $request->error->type,
    ];
    header("Location: /inbox/");
}