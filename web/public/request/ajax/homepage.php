<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
$select = new \Database\Select($__db);
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function convert_to_duration($total_time){
    $seconds = $total_time %60;
    $minutes = (floor($total_time/60)) % 60;
    $hours = floor($total_time/3600);
    if($hours == 0){
        return $minutes . ':' . sprintf('%02d', $seconds);
    }else{
        return $hours . ':' . $minutes . ':' . sprintf('%02d', $seconds);
    }
}
?>
<br>
    <?php
        $videos_search = $__db->prepare("SELECT * FROM videos ORDER BY id DESC LIMIT 6");
        $videos_search->execute();
        
        while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $seconds = $video['video_duration'];
            $video['video_duration'] = sprintf('%02d:%02d', (@$seconds/ 60 % 60), @$seconds% 60);
        ?>
            <img class="icon" src="/img/feed.png"> 
            <span class="mid">
                <b>Uploaded</b> by <a href="/user/<?php echo htmlspecialchars($video['video_author']);  ?>"><?php echo htmlspecialchars($video['video_author']);  ?></a>
                <span class="caption">(<?php echo time_elapsed_string($video['video_added']); ?>)</span>
            </span>
            <br>
            <div class="video-item inline-block w-300 hover-video padding" style="width: 623px;">
                <a href="/watch?v=<?php echo htmlspecialchars($video['video_id']);  ?>">
                    <div class="thumb-128 inline-block"
                        style="background-image: url('/v/thumb/<?php echo htmlspecialchars($video['video_thumbnail']);  ?>');"
                    >
                        <span class="timestamp" style="position: relative;top: 55px;left: 89px;"><?php echo ($video['video_duration']);  ?></span>
                    </div>
                </a>
                <div class="video-meta">
                    <a href="/watch?v=<?php echo htmlspecialchars($video['video_id']);  ?>" class="video-author bigger"><?php echo htmlspecialchars($video['video_title']);  ?></a><br>
                    <span class="small"> 
                        <span class="video-description-meta">
                            <?php echo htmlspecialchars($video['video_description']);  ?>
                        </span>
                    </span><br>
                    by <a href="/user/<?php echo htmlspecialchars($video['video_author']);  ?>"><?php echo htmlspecialchars($video['video_author']);  ?></a> <span class="seperator"></span> <?php echo time_elapsed_string($video['video_added']); ?> <span class="seperator"></span> <b>2 views</b>
                </div>
            </div><br>
            <hr class="dotted-under" style="border-bottom: 1px solid #ededed;">
    <?php
        }
    ?>