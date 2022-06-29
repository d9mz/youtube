<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
$select = new \Database\Select($__db);
?>
<div class="videos-list">
    <?php
        $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_id = :video_id LIMIT 1");
        $videos_search->bindParam(':video_id', $_GET['v'], PDO::PARAM_STR);
        $videos_search->execute();
        
        while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $seconds = $video['video_duration'];
            ?>
            <div class="utue-player" data-src="/v/<?php echo $video['video_id']; ?>.mp4"></div><br>
            <script>
                UTUE_PLAYER.init({
                    imageDir: '../assets/images',
                    autoplay: 0
                });
            </script>
            <h1 class="video-watch-title"><?php echo htmlspecialchars($video['video_title']); ?></h1>
            <p>
                From: <a href="/user/<?php echo htmlspecialchars($video['video_author']); ?>"><?php echo htmlspecialchars($video['video_author']); ?></a> | <?php echo date("M d, Y", strtotime($video['video_added'])); ?>
            </p>
            <p>
                <?php echo nl2br(htmlspecialchars($video['video_description'])); ?>
                <br><br>
                <a class="bold black" href="/watch?v=<?php echo htmlspecialchars($video['video_id']); ?>">View comments, related videos, and more</a>
            </p>
    <?php
        }
    ?>
</div>