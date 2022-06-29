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
?>
<br>
    <?php
        $videos_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_to = :subscribers_from ORDER BY id DESC LIMIT 6");
        $videos_search->bindParam(':subscribers_from', $_SESSION['youtube'], PDO::PARAM_STR);
        $videos_search->execute();
        
        while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $video["profile_picture"] = $select->fetch_user_pfp($video['subscription_from']);
            ?>
            <img class="icon" src="/img/feed.png"> 
            <span class="mid">
                <b>New subscription</b> by <a href="/user/<?php echo htmlspecialchars($video['subscription_from']);  ?>"><?php echo htmlspecialchars($video['subscription_from']);  ?></a>
                <span class="caption">(<?php echo time_elapsed_string($video['subscription_when']); ?>)</span>
            </span>
            <br>
            <div class="video-item inline-block w-300 hover-video padding" style="width: 623px;">
                <a href="/user/<?php echo htmlspecialchars($video['subscription_from']);  ?>">
                    <img src="/v/p/<?php echo $video['profile_picture']; ?>" style="width:60px;height:60px;" class="profile-picture">
                </a>
                <div class="video-meta">

                </div>
            </div><br>
            <hr class="dotted-under" style="border-bottom: 1px solid #ededed;">
    <?php
        }
    ?>