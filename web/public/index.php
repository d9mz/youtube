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
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Entertainment'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_ent[] = $video;
    }

    $videos_ent['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Music'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_mus[] = $video;
    }

    $videos_mus['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Gaming'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_gaming[] = $video;
    }

    $videos_gaming['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Film & Animation'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_film[] = $video;
    }

    $videos_film['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Sports'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_sports[] = $video;
    }

    $videos_sports['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Howto & Style'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_diy[] = $video;
    }

    $videos_diy['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Science & Technology'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_sci[] = $video;
    }

    $videos_sci['rows'] = $videos_search->rowCount();

    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Travel & Events'");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $videos_travel[] = $video;
    }

    $videos_travel['rows'] = $videos_search->rowCount();

    // CATEGORY 

    echo $twig->render('index.twig', array(
        "videos_ent"    => $videos_ent,
        "videos_mus"    => $videos_mus,
        "videos_gaming"    => $videos_gaming,
        "videos_film"   => $videos_film,
        "videos_sports" => $videos_sports,
        "videos_diy"    => $videos_diy,
        "videos_sci"    => $videos_sci,
        "videos_travel" => $videos_travel,
    ));
});

$router->get('/watch', function() use ($twig, $__db, $select) { 
    if(isset($_GET['v']) && $select->video_exists($_GET['v'])) {
        $video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
        $comments_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
        $comments_search->bindParam(':target', $_GET['v'], PDO::PARAM_STR);
        $comments_search->execute();
        
        while($comment = $comments_search->fetch(PDO::FETCH_ASSOC)) { 
            $comments[] = $comment;
        }
    
        $comments['rows'] = $comments_search->rowCount();

        echo $twig->render('watch.twig', array("video" => $video, "comments" => $comments));
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