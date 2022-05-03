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
        $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_author = :video_author ORDER BY RAND() LIMIT 5");
        $videos_search->bindParam(':video_author', $_GET['c'], PDO::PARAM_STR);
        $videos_search->execute();
        
        while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $seconds = $video['video_duration'];
            $video['video_duration'] = sprintf('%02d:%02d', (@$seconds/ 60 % 60), @$seconds% 60);
            ?>
        <div class="video-item inline-block w-115 hover-video padding">
            <div class="thumb-64 inline-block" style="background-image: url('/v/thumb/<?php echo $video['video_thumbnail']; ?>');">
                <span class="timestamp"><?php echo $video['video_duration']; ?></span>
            </div>
            <div class="video-meta">
                <a href="/watch?v=<?php echo $video['video_id']; ?>" class="video-author bigger"><?php echo htmlspecialchars($video['video_title']); ?></a><br>
                <span class="small">2 views</span><br>
                <a href="/user/<?php echo htmlspecialchars($video['video_author']); ?>" class="video-author"><?php echo htmlspecialchars($video['video_author']); ?></a>
            </div>
        </div>
    <?php
        }
    ?>
</div>