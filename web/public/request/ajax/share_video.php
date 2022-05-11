<?php 
session_start();
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
$select = new \Database\Select($__db);
$video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
?>
<span class="bold black mid" style="font-size: 15px;position: relative;top: 6px;">
    Share Video
</span>
<div class="right">
    <span class="sprite sprite-facebook"></span>
    <span class="sprite sprite-digg"></span>
    <span class="sprite sprite-reddit"></span>
    <span class="sprite sprite-tumblr"></span>
    <span class="sprite sprite-twitter"></span>
</div>
<div class="share-panel">
    <input class="ut-input" type="text" value="https://utue.net/watch?v=<?php echo $video['video_id']; ?>" disabled>
    <br>
    <textarea class="ut-input no-resize" disabled>DUMMY IFRAME EMBED DATA - PLACEHOLDER</textarea>
</div>