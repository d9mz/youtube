<?php
session_start();

require("../app/vendor/autoload.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/config.inc.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/db.php");
require($_SERVER['DOCUMENT_ROOT'] . "/protected/select.php");

$router = new \Bramus\Router\Router();
$loader = new \Twig\Loader\FilesystemLoader('twig/templates');
$twig = new \Twig\Environment($loader);
$select = new \Database\Select($__db);

$filter = new \Twig\TwigFilter('timeago', function ($datetime) {
    $time = time() - strtotime($datetime); 
    $units = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($units as $unit => $val) {
        if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return ($val == 'second')? 'a few seconds ago' : 
                (($numberOfUnits>1) ? $numberOfUnits : 'a')
                .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
    }
});

$twig->addFilter($filter);

$router->get('/', function() use ($twig, $__db) { 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Entertainment' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_ent[] = $video;
        $videos_ent[0]["video_views"] = $views_search->rowCount();
    }

    $videos_ent['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Music' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_mus[] = $video;
        $videos_mus[0]["video_views"] = $views_search->rowCount();
    }

    $videos_mus['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Gaming' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_gaming[] = $video;
        $videos_gaming[0]["video_views"] = $views_search->rowCount();
    }

    $videos_gaming['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Film & Animation' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_film[] = $video;
        $videos_film[0]["video_views"] = $views_search->rowCount();
    }

    $videos_film['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Sports' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_sports[] = $video;
        $videos_sports[0]["video_views"] = $views_search->rowCount();
    }

    $videos_sports['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Howto & Style' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_diy[] = $video;
        $videos_diy[0]["video_views"] = $views_search->rowCount();
    }

    $videos_diy['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Science & Technology' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_sci[] = $video;
        $videos_sci[0]["video_views"] = $views_search->rowCount();
    }

    $videos_sci['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Travel & Events' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_travel[] = $video;
        $videos_travel[0]["video_views"] = $views_search->rowCount();
    }

    $videos_travel['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos ORDER BY id DESC LIMIT 3");
    $videos_search->execute();
    
    $i = 0;
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_new[] = $video;
        $videos_new[$i]["video_views"] = $views_search->rowCount();
        $i++;
    }

    $videos_new['rows'] = $videos_search->rowCount();

    // CATEGORY 

    echo $twig->render('index.twig', array(
        "videos_ent"    => $videos_ent,
        "videos_mus"    => $videos_mus,
        "videos_gaming" => $videos_gaming,
        "videos_film"   => $videos_film,
        "videos_sports" => $videos_sports,
        "videos_diy"    => $videos_diy,
        "videos_sci"    => $videos_sci,
        "videos_travel" => $videos_travel,
        "videos_new"    => $videos_new,
    ));
});

$router->get('/user/(\w+)', function($username) use ($twig, $__db, $select) { 
    if(!empty(trim($username)) && $select->user_exists($username)) {
        $user = $select->fetch_table_singlerow($username, "users", "youtube_username");

        $sub_search = $__db->prepare("SELECT subscription_to, id FROM subscribers WHERE subscription_to = :sub_to AND subscription_from = :sub_from");
        $sub_search->bindParam(':sub_to',    $user['youtube_username'], PDO::PARAM_STR);
        $sub_search->bindParam(':sub_from',  $_SESSION['youtube'], PDO::PARAM_STR);
        $sub_search->execute();
        $subscription = $sub_search->fetch();

        if(isset($_SESSION['youtube']) && isset($subscription['id'])) {
            $user["subscribed"] = true;
        }

        $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_author = :video_author ORDER BY RAND() LIMIT 20");
        $videos_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $videos_search->execute();
        
        while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $videos[] = $video;
        }
    
        $videos['rows'] = $videos_search->rowCount();

        $comments_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
        $comments_search->bindParam(':target', $username, PDO::PARAM_STR);
        $comments_search->execute();
        
        while($comment = $comments_search->fetch(PDO::FETCH_ASSOC)) { 
            $comments[] = $comment;
        }
    
        $comments['rows'] = $comments_search->rowCount();

        echo $twig->render('user.twig', array("user" => $user, "videos" => $videos, "comments" => $comments));
    } else {
        header("Location: /");
    }
});

$router->get('/my/video_manager', function() use ($twig, $__db, $select) { 
    $video_manager = $__db->prepare("SELECT * FROM videos WHERE video_author = :video_author ORDER BY id DESC LIMIT 20");
    $video_manager->bindParam(':video_author',  $_SESSION['youtube'], PDO::PARAM_STR);
    $video_manager->execute();
    while($video = $video_manager->fetch(PDO::FETCH_ASSOC)) { 
        $videos[] = $video;
    }

    $videos['rows'] = $video_manager->rowCount();
    echo $twig->render('video_manager.twig', array("videos" => $videos));
});

$router->get('/watch', function() use ($twig, $__db, $select) { 
    if(isset($_GET['v']) && $select->video_exists($_GET['v'])) {
        $video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
        $video["vote_likes"]    = 0;
        $video["vote_dislikes"] = 0;
        $video[0]["video_views"]   = 0;
        $video["vote_liked"]    = false;
        $video["vote_disliked"] = false;
        $video["author_subscribed"] = false;

        $vote_search = $__db->prepare("SELECT vote_type, id FROM votes WHERE vote_target = :vote_target AND vote_from = :vote_from");
        $vote_search->bindParam(':vote_target',  $_GET['v'], PDO::PARAM_STR);
        $vote_search->bindParam(':vote_from',    $_SESSION['youtube'], PDO::PARAM_STR);
        $vote_search->execute();
        $vote = $vote_search->fetch();

        if(isset($_SESSION['youtube']) && isset($vote['id'])) {
            if($vote['vote_type'] == 'l') {
                $video["vote_liked"] = true;
            } else {
                $video["vote_disliked"] = true;
            }
        }
        
        $sub_search = $__db->prepare("SELECT subscription_to, id FROM subscribers WHERE subscription_to = :sub_to AND subscription_from = :sub_from");
        $sub_search->bindParam(':sub_to',    $video['video_author'], PDO::PARAM_STR);
        $sub_search->bindParam(':sub_from',  $_SESSION['youtube'], PDO::PARAM_STR);
        $sub_search->execute();
        $subscription = $sub_search->fetch();

        if(isset($_SESSION['youtube']) && isset($subscription['id'])) {
            $video["author_subscribed"] = true;
        }
        
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target AND view_ip = :view_ip");
        $views_search->bindParam(':view_target',  $_GET['v'], PDO::PARAM_STR);
        $views_search->bindParam(':view_ip',     $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $views_search->execute();
        $view = $views_search->fetch();
        if(isset($_SESSION['youtube']) && !isset($view['id'])) {
            $stmt = $__db->prepare(
                "INSERT INTO views 
                    (view_video, view_ip) 
                 VALUES 
                    (?, ?)"
            );
            $stmt->execute([
                $_GET['v'],
                $_SERVER['REMOTE_ADDR'],
            ]);
        }

        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $_GET['v'], PDO::PARAM_STR);
        $views_search->execute();
        $video[0]["video_views"] = $views_search->rowCount();

        $videos_search = $__db->prepare("SELECT id FROM videos WHERE video_author = :video_author");
        $videos_search->bindParam(':video_author',  $video['video_author'], PDO::PARAM_STR);
        $videos_search->execute();
        $video["author_videos"] = $videos_search->rowCount();

        $comments_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
        $comments_search->bindParam(':target', $_GET['v'], PDO::PARAM_STR);
        $comments_search->execute();
        
        while($comment = $comments_search->fetch(PDO::FETCH_ASSOC)) { 
            $comments[] = $comment;
        }
    
        $comments['rows'] = $comments_search->rowCount();

        $videos_search = $__db->prepare("SELECT * FROM videos ORDER BY RAND() LIMIT 13");
        $videos_search->execute();
        while($video_n = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $videos_new[] = $video_n;
        }
    
        $videos_new['rows'] = $videos_search->rowCount();
    
        // CATEGORY 
        $like_votes_search = $__db->prepare("SELECT id FROM votes WHERE vote_target = :vote_target AND vote_from = :vote_from AND vote_type = 'l'");
        $like_votes_search->bindParam(':vote_target', $_GET['v'], PDO::PARAM_STR);
        $like_votes_search->bindParam(':vote_from',   $_SESSION['youtube'], PDO::PARAM_STR);
        $like_votes_search->execute();
        $video["vote_likes"] = $like_votes_search->rowCount();
    
        // CATEGORY 
        $like_votes_search = $__db->prepare("SELECT id FROM votes WHERE vote_target = :vote_target AND vote_from = :vote_from AND vote_type = 'd'");
        $like_votes_search->bindParam(':vote_target', $_GET['v'], PDO::PARAM_STR);
        $like_votes_search->bindParam(':vote_from',   $_SESSION['youtube'], PDO::PARAM_STR);
        $like_votes_search->execute();
        $video["vote_dislikes"] = $like_votes_search->rowCount();
    
        // CATEGORY
        
        if($video["vote_likes"] == 0 && $video["vote_dislikes"] == 0) {
            $video['likes_width'] = 50;
            $video['dislikes_width'] = 50;
        } else {
            $video['likes_width'] = $video["vote_likes"] / ($video["vote_likes"] + $video["vote_dislikes"]) * 100;
            $video['dislikes_width'] = 100 - $video['likes_width'];
        }

        echo $twig->render('watch.twig', array("video" => $video, "comments" => $comments, "videos_rec" => $videos_new));
    } else {
        $_SESSION['alert'] = (object) [
            "message" => "This video does not exist!",
            "type" => 1,
        ];

        header("Location: /");
    }
});

$router->get('/sign_in', function() use ($twig, $__db) { 
    echo $twig->render('sign_in.twig', array());
});

$router->get('/sign_up', function() use ($twig, $__db) { 
    echo $twig->render('sign_up.twig', array());
});

$router->get('/upload_video', function() use ($twig, $__db) { 
    $categories = ["None", "Film & Animation", "Autos & Vehicles", "Music", "Pets & Animals", "Sports", "Travel & Events", "Gaming", "People & Blogs", "Comedy", "Entertainment", "News & Politics", "Howto & Style", "Education", "Science & Technology", "Nonprofits & Activism"];
    echo $twig->render('upload_video.twig', array("categories" => $categories));
});

$router->set404(function() use ($twig) {
    echo "404";
});

$twig->addGlobal('config',   $config);
$twig->addGlobal('session',  $_SESSION);
$twig->addGlobal('args',     @$_GET);
$twig->addGlobal('referrer', @$_SERVER['HTTP_REFERER']);
$router->run();

unset($_SESSION['alert']);