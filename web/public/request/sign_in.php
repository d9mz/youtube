<?php
if(isset($_POST['remove_items']) && $_POST['remove_items'] == "y") {
    ini_set('session.gc_maxlifetime', 3600 * 48);
    session_set_cookie_params(3600 * 48, "/");
}
session_start();

require("../../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");

$request = (object) [
    "username" => trim(strtolower($_POST['youtube-username'])),
    "password" => $_POST['youtube-password'],
    "password_hash" => password_hash($_POST['youtube-password'], PASSWORD_DEFAULT),

    "progress" => (object) [
        "code"    => 200, // HTTP Response Code 
        "message" => "",
    ],
];

if(empty($request->username))
    $request->progress->message = "Your username cannot be empty.";
    
$stmt = $__db->prepare("SELECT youtube_password FROM users WHERE youtube_username = :username");
$stmt->bindParam(":username", $request->username);
$stmt->execute();

if(!$stmt->rowCount()){ 
    { $request->progress->message = "Incorrect username or password!"; } }

$row = $stmt->fetch(PDO::FETCH_ASSOC);
if(!isset($row['youtube_password'])) 
    { $request->progress->message = "Incorrect username or password!"; } 
else 
    { $request->returned_password = $row['youtube_password']; }

if(!@password_verify($request->password, $request->returned_password)) 
    { $request->progress->message = "Incorrect username or password!"; }
    
if($request->progress->message == "") {
    $_SESSION['youtube'] = $request->username;
    header("Location: /");
} else {
    $_SESSION['alert'] = (object) [
        "message" => $request->progress->message,
        "type" => 1,
    ];
    
    header("Location: /sign_in");
}

// echo json_encode($request, JSON_PRETTY_PRINT);