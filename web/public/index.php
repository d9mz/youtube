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

if(isset($_SESSION['youtube'])) {
    $ban_search = $__db->prepare("SELECT * FROM bans WHERE ban_to = :to LIMIT 1");
    $ban_search->bindParam(":to", $_SESSION['youtube'], PDO::PARAM_STR);
    $ban_search->execute();
    if($ban_search->rowCount() == 1 && $_SERVER['REQUEST_URI'] != "/terminated") {
        header("Location: /terminated");
    }

    $stmt = $__db->prepare("UPDATE users SET last_login = NOW() WHERE youtube_username = ?");
    $stmt->execute([
        $_SESSION['youtube'],
    ]);
}

/*
	$channel_modules = (object) [
		"subscribers_mod"   => true,
		"subscriptions_mod" => true,
		"comments_mod"      => true,
		"profile_mod"       => true,
		"activity_mod"      => true,
		"info_mod"          => true,
		"friends_mod"       => false,
	];

	echo json_encode($channel_modules);
*/

$router->get('/', function() use ($twig, $__db, $select) { 
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
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'Comedy' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_com[] = $video;
        $videos_com[0]["video_views"] = $views_search->rowCount();
    }

    $videos_com['rows'] = $videos_search->rowCount();
    // CATEGORY 
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_category = 'People & Blogs' ORDER BY id DESC LIMIT 1");
    $videos_search->execute();
    
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_ppl[] = $video;
        $videos_ppl[0]["video_views"] = $views_search->rowCount();
    }

    $videos_ppl['rows'] = $videos_search->rowCount();
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
    $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_meta = 'f' ORDER BY id DESC LIMIT 3");
    $videos_search->execute();
    
    $i = 0;
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos_featured[] = $video;
        $videos_featured[$i]["video_views"] = $views_search->rowCount();
        $i++;
    }

    $videos_featured['rows'] = $videos_search->rowCount();

    // CATEGORY 

    $homepage = (object) [
        "unread_inbox"    => $select->get_inbox(@$_SESSION['youtube']),
        "profile_picture" => $select->fetch_user_pfp(@$_SESSION['youtube']),
    ];

    if(isset($_SESSION['youtube'])) {
        $videos_search = $__db->prepare("SELECT * FROM videos ORDER BY id DESC LIMIT 6");
        $videos_search->execute();
        $i = 0;
        while($video_n = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
            $views_search->bindParam(':view_target',  $video_n['video_id'], PDO::PARAM_STR);
            $views_search->execute();
            $videos_homepage[] = $video_n;
            $videos_homepage[$i]["video_views"] = $views_search->rowCount();
            $i++;
        }
    
        $videos_homepage['rows'] = $videos_search->rowCount();
    }

    echo $twig->render('index.twig', array(
        "videos_ent"      => $videos_ent,
        "videos_mus"      => $videos_mus,
        "videos_gaming"   => $videos_gaming,
        "videos_film"     => $videos_film,
        "videos_sports"   => $videos_sports,
        "videos_diy"      => $videos_diy,
        "videos_sci"      => $videos_sci,
        "videos_travel"   => $videos_travel,
        "videos_new"      => $videos_new,
        "videos_featured" => $videos_featured,
        "videos_people"   => $videos_ppl,
        "videos_comedy"   => $videos_com,
        "homepage"        => $homepage,
        "videos"          => @$videos_homepage,
    ));
});

$router->get('/user_subscribers/(\w+)', function($username) use ($twig, $__db, $select) { 
    if(!empty(trim($username)) && $select->user_exists($username)) {
        $user = $select->fetch_table_singlerow($username, "users", "youtube_username");
        $subscribers_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_to = :video_author ORDER BY RAND()");
        $subscribers_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscribers_search->execute();
        
        $i = 0;
        while($sub = $subscribers_search->fetch(PDO::FETCH_ASSOC)) { 
            $sub["profile_picture"] = $select->fetch_user_pfp($sub['subscription_from']);
            $subscribers[] = $sub;
            $i++;
        }
        
        $subscribers_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_to = :video_author");
        $subscribers_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscribers_search->execute();
        $subscribers['rows'] = $subscribers_search->rowCount();

        $categories = ["UTuer", "Director", "Musician", "Comedian", "Guru", "Nonprofit"];
        $channel_colors = json_decode($user['youtube_colors']);
        echo $twig->render('user_subscribers.twig', 
            array(
                "user" => $user, 
                "subscribers" => $subscribers, 
                "channel_colors" => $channel_colors,
            )
        );
        
    } else {
        header("Location: /");
    }
});

$router->get('/user_subscriptions/(\w+)', function($username) use ($twig, $__db, $select) { 
    if(!empty(trim($username)) && $select->user_exists($username)) {
        $user = $select->fetch_table_singlerow($username, "users", "youtube_username");
        $subscriptions_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_from = :video_author ORDER BY id DESC");
        $subscriptions_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscriptions_search->execute();
        
        $i = 0;
        while($sub = $subscriptions_search->fetch(PDO::FETCH_ASSOC)) { 
            $sub["profile_picture"] = $select->fetch_user_pfp($sub['subscription_to']);
            $subscriptions[] = $sub;
            $i++;
        }
    
        $subscriptions_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_from = :video_author");
        $subscriptions_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscriptions_search->execute();
        $subscriptions['rows'] = $subscriptions_search->rowCount();

        $categories = ["UTuer", "Director", "Musician", "Comedian", "Guru", "Nonprofit"];
        $channel_colors = json_decode($user['youtube_colors']);
        echo $twig->render('user_subscriptions.twig', 
            array(
                "user" => $user, 
                "subscriptions" => $subscriptions, 
                "channel_colors" => $channel_colors,
            )
        );
        
    } else {
        header("Location: /");
    }
});

$router->get('/user/(\w+)', function($username) use ($twig, $__db, $select) { 
    if(!empty(trim($username)) && $select->user_exists($username)) {
        $user = $select->fetch_table_singlerow($username, "users", "youtube_username");

        if(isset($_SESSION['youtube'])) {
            $sub_search = $__db->prepare("SELECT subscription_to, id FROM subscribers WHERE subscription_to = :sub_to AND subscription_from = :sub_from");
            $sub_search->bindParam(':sub_to',    $user['youtube_username'], PDO::PARAM_STR);
            $sub_search->bindParam(':sub_from',  $_SESSION['youtube'], PDO::PARAM_STR);
            $sub_search->execute();
            $subscription = $sub_search->fetch();

            if(isset($subscription['id'])) {
                $user["subscribed"] = true;
            }
        }

        $videos_search = $__db->prepare("SELECT * FROM videos WHERE video_author = :video_author ORDER BY RAND() LIMIT 20");
        $videos_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $videos_search->execute();
        
        while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $videos[] = $video;
        }
    
        $videos['rows'] = $videos_search->rowCount();

        $subscribers_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_to = :video_author ORDER BY RAND() LIMIT 14");
        $subscribers_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscribers_search->execute();
        
        $i = 0;
        while($sub = $subscribers_search->fetch(PDO::FETCH_ASSOC)) { 
            $sub["profile_picture"] = $select->fetch_user_pfp($sub['subscription_from']);
            $subscribers[] = $sub;
            $i++;
        }
        
        $subscribers_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_to = :video_author");
        $subscribers_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscribers_search->execute();
        $subscribers['rows'] = $subscribers_search->rowCount();

        $subscriptions_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_from = :video_author ORDER BY id DESC LIMIT 14");
        $subscriptions_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscriptions_search->execute();
        
        $i = 0;
        while($sub = $subscriptions_search->fetch(PDO::FETCH_ASSOC)) { 
            $sub["profile_picture"] = $select->fetch_user_pfp($sub['subscription_to']);
            $subscriptions[] = $sub;
            $i++;
        }
    
        $subscriptions_search = $__db->prepare("SELECT * FROM subscribers WHERE subscription_from = :video_author");
        $subscriptions_search->bindParam(':video_author',    $user['youtube_username'], PDO::PARAM_STR);
        $subscriptions_search->execute();
        $subscriptions['rows'] = $subscriptions_search->rowCount();

        $comments_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
        $comments_search->bindParam(':target', $username, PDO::PARAM_STR);
        $comments_search->execute();
        
        while($comment = $comments_search->fetch(PDO::FETCH_ASSOC)) { 
            $comment["profile_picture"] = $select->fetch_user_pfp($comment['comment_author']);
            $comments[] = $comment;
        }
    
        $comments['rows'] = $comments_search->rowCount();

        $friends_search = $__db->prepare("SELECT * FROM friends WHERE friend_to = :target ORDER BY id DESC LIMIT 10");
        $friends_search->bindParam(':target', $user['youtube_username'], PDO::PARAM_STR);
        $friends_search->execute();
        
        while($friend = $friends_search->fetch(PDO::FETCH_ASSOC)) { 
            $friend["profile_picture"] = $select->fetch_user_pfp($friend['friend_by']);
            $friend_to[] = $friend;
        }
    
        $friend_to['rows'] = $friends_search->rowCount();

        $friends_by = $__db->prepare("SELECT * FROM friends WHERE friend_by = :target AND friend_status = 'a' ORDER BY id DESC LIMIT 10");
        $friends_by->bindParam(':target', $user['youtube_username'], PDO::PARAM_STR);
        $friends_by->execute();
        
        while($friend = $friends_by->fetch(PDO::FETCH_ASSOC)) { 
            $friend["profile_picture"] = $select->fetch_user_pfp($friend['friend_by']);
            $friend_by[] = $friend;
        }
    
        $friend_to['rows'] = $friend_to['rows'] + $friends_by->rowCount();

        $categories = ["UTuer", "Director", "Musician", "Comedian", "Guru", "Nonprofit"];
        $channel_colors = json_decode($user['youtube_colors']);

        if($user['youtube_featured'] != "" && $select->video_exists($user['youtube_featured'])) {
            $video = $select->fetch_table_singlerow($user['youtube_featured'], "videos", "video_id");
            $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
            $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
            $views_search->execute();
            $video["video_views"] = $views_search->rowCount();
        }
		
		$user_videos = $__db->prepare("SELECT * FROM videos WHERE video_author = :video_author ORDER BY id DESC LIMIT 20");
		$user_videos->bindParam(':video_author',  $user['youtube_username'], PDO::PARAM_STR);
		$user_videos->execute();
		while($side_video = $user_videos->fetch(PDO::FETCH_ASSOC)) { 
			$views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
			$views_search->bindParam(':view_target',  $side_video['video_id'], PDO::PARAM_STR);
			$views_search->execute();
			$side_video["video_views"] = $views_search->rowCount();

			$side_videos[] = $side_video;
		}
        
        $user["blocked"]              = $select->user_blocked(@$_SESSION['youtube'], $user['youtube_username']);
        $user["blocked_recieving"]    = $select->user_blocked($user['youtube_username'], @$_SESSION['youtube']);
		$user["youtube_modules"]      = json_decode($user["youtube_modules"]);
        $user["friend"]               = $select->get_friend_request($user['youtube_username'], @$_SESSION['youtube']);
        $user["friend_accepted"]      = $select->get_friend_request($user['youtube_username'], @$_SESSION['youtube'], "a");
        echo $twig->render('user.twig', 
            array(
                "side_videos" => @$side_videos, 
                "video" => $video, 
                "channel_colors" => $channel_colors, 
                "categories" => $categories, 
                "user" => $user, 
                "subscribers" => $subscribers, 
                "subscriptions" => $subscriptions, 
                "videos" => $videos, 
                "comments" => $comments,
                "friends_by" => @$friend_by,
                "friends_to" => $friend_to,
            )
        );
        
    } else {
        header("Location: /");
    }
});

$router->get('/my/video_manager', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube'])) {
        $video_manager = $__db->prepare("SELECT * FROM videos WHERE video_author = :video_author ORDER BY id DESC LIMIT 20");
        $video_manager->bindParam(':video_author',  $_SESSION['youtube'], PDO::PARAM_STR);
        $video_manager->execute();
        while($video = $video_manager->fetch(PDO::FETCH_ASSOC)) { 
            $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
            $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
            $views_search->execute();
            $comments_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
            $comments_search->bindParam(':target', $video['video_id'], PDO::PARAM_STR);
            $comments_search->execute();
            $video["video_views"] = $views_search->rowCount();
            $video['comments'] = $comments_search->rowCount();

            $videos[] = $video;
        }

        $videos['rows'] = $video_manager->rowCount();
        echo $twig->render('video_manager.twig', array("videos" => $videos));
    } else {
        header("Location: /");
    }
});


$router->get('/video_analytics', function() use ($twig, $__db, $select) { 
    if(isset($_GET['v']) && $select->video_exists($_GET['v'])) {
        $video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
        $video_manager = $__db->prepare("SELECT * FROM views WHERE view_video = :target ORDER BY id DESC LIMIT 10");
        $video_manager->bindParam(':target',  $_GET['v'], PDO::PARAM_STR);
        $video_manager->execute();
        while($view = $video_manager->fetch(PDO::FETCH_ASSOC)) { 
            $views[] = $view;
        }

        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $comments_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
        $comments_search->bindParam(':target', $video['video_id'], PDO::PARAM_STR);
        $comments_search->execute();
        $video["video_views"] = $views_search->rowCount();
        $video['comments'] = $comments_search->rowCount();
        echo $twig->render('video_analytics.twig', array("views" => $views, "video" => $video));
    } else {
        header("Location: /my/video_manager");
    }
});

$router->get('/inbox/', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube'])) {
        $inbox_search = $__db->prepare("SELECT * FROM inbox WHERE inbox_to = :author ORDER BY id DESC LIMIT 50");
        $inbox_search->bindParam(':author', $_SESSION['youtube'], PDO::PARAM_STR);
        $inbox_search->execute();
        
        while($message = $inbox_search->fetch(PDO::FETCH_ASSOC)) { 
            $message["profile_picture"] = $select->fetch_user_pfp($message['inbox_author']);
            $messages[] = $message;
        }

        if($inbox_search->rowCount() == 0) {
            $messages = [];
        }

        echo $twig->render('inbox/inbox.twig', array("messages" => $messages));
    } else {
        header("Location: /");
    }
});

$router->get('/inbox/comments', function() use ($twig, $__db, $select) { 
	$inbox_search = $__db->prepare("SELECT * FROM inbox WHERE inbox_to = :author AND inbox_category = 'Notification' ORDER BY id DESC LIMIT 50");
	$inbox_search->bindParam(':author', $_SESSION['youtube'], PDO::PARAM_STR);
	$inbox_search->execute();
	
	while($message = $inbox_search->fetch(PDO::FETCH_ASSOC)) { 
		$message["profile_picture"] = $select->fetch_user_pfp($message['inbox_author']);
		$messages[] = $message;
	}
    echo $twig->render('inbox/comments.twig', array("messages" => $messages));
});

$router->get('/inbox/compose', function() use ($twig, $__db, $select) { 
    echo $twig->render('inbox/compose.twig', array());
});

$router->get('/my/video_history', function() use ($twig, $__db, $select) { 
    $video_manager = $__db->prepare("SELECT * FROM history WHERE video_author = :video_author ORDER BY id DESC LIMIT 20");
    $video_manager->bindParam(':video_author',  $_SESSION['youtube'], PDO::PARAM_STR);
    $video_manager->execute();
    while($video = $video_manager->fetch(PDO::FETCH_ASSOC)) { 
        $video = $select->fetch_table_singlerow($video['video_id'], "videos", "video_id");
        $videos[] = $video;
    }

    $videos['rows'] = $video_manager->rowCount();
    echo $twig->render('video_history.twig', array("videos" => $videos));
});

$router->get('/account', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        echo $twig->render('settings/settings.twig', array("user" => $user));
    } else {
        header("Location: /");
    }
});

$router->get('/my/friends', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        $friends_search_recieving = $__db->prepare("SELECT * FROM friends WHERE friend_to = :to ORDER BY id DESC LIMIT 20");
        $friends_search_recieving->bindParam(":to", $_SESSION['youtube'], PDO::PARAM_STR);
        $friends_search_recieving->execute();
        while($friend = $friends_search_recieving->fetch(PDO::FETCH_ASSOC)) { 
            $friend["profile_picture"] = $select->fetch_user_pfp($friend['friend_by']);
            $friends_recieving[] = $friend;
        }
    
        $friends_recieving['rows'] = $friends_search_recieving->rowCount();
        $friends_search_sent = $__db->prepare("SELECT * FROM friends WHERE friend_by = :by ORDER BY id DESC LIMIT 20");
        $friends_search_sent->bindParam(":by", $_SESSION['youtube'], PDO::PARAM_STR);
        $friends_search_sent->execute();
        while($friend = $friends_search_sent->fetch(PDO::FETCH_ASSOC)) { 
            $friend["profile_picture"] = $select->fetch_user_pfp($friend['friend_to']);
            $friends_sent[] = $friend;
        }
    
        $friend_sent['rows'] = $friends_search_sent->rowCount();
        echo $twig->render('settings/friend_request.twig', 
            array(
                "user" => $user, 
                "recieving_friends" => $friends_recieving, 
                "sent_friends" => $friends_sent, 
            )
        );
    } else {
        header("Location: /");
    }
});

$router->get('/admin/', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube']) && $select->user_is_admin($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        $referral_search = $__db->prepare("SELECT * FROM referrals ORDER BY id DESC LIMIT 20");
        $referral_search->execute();
        while($key = $referral_search->fetch(PDO::FETCH_ASSOC)) { 
            $referrals[] = $key;
        }
    
        $referrals['rows'] = $referral_search->rowCount();

        echo $twig->render('admin/index.twig', array("user" => $user, "referrals" => $referrals));
    } else {
        header("Location: /");
    }
});

$router->get('/admin/ban', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube']) && $select->user_is_admin($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        echo $twig->render('admin/ban.twig', array("user" => $user));
    } else {
        header("Location: /");
    }
});

$router->get('/admin/delete', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube']) && $select->user_is_admin($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        echo $twig->render('admin/delete.twig', array("user" => $user));
    } else {
        header("Location: /");
    }
});

$router->get('/videos', function() use ($twig, $__db, $select) { 
    if(!isset($_GET['c']) || @$_GET['c'] == "None") {
        $query = "SELECT * FROM videos ORDER BY id DESC LIMIT 40";
        $current_category = "None";

        $videos_search = $__db->prepare($query);
        $videos_search->execute();
    } else {
        $query = "SELECT * FROM videos WHERE video_category = :category ORDER BY id DESC LIMIT 40";
        $current_category = $_GET['c'];

        $videos_search = $__db->prepare($query);
        $videos_search->bindParam(':category',  $current_category, PDO::PARAM_STR);
        $videos_search->execute();
    }

    
    $i = 0;
    while($video = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
        $views_search->bindParam(':view_target',  $video['video_id'], PDO::PARAM_STR);
        $views_search->execute();
        $videos[] = $video;
        $videos[$i]["video_views"] = $views_search->rowCount();
        $i++;
    }

    $videos['rows'] = $videos_search->rowCount();
    $categories = [
        "None", 
        "Film & Animation", 
        "Autos & Vehicles", 
        "Music", 
        "Pets & Animals", 
        "Sports", 
        "Travel & Events", 
        "Gaming", 
        "People & Blogs", 
        "Comedy", 
        "Entertainment", 
        "News & Politics", 
        "Howto & Style", 
        "Education", 
        "Science & Technology", 
        "Nonprofits & Activism"
    ];

    echo $twig->render('videos.twig', array("videos" => $videos, "categories" => $categories, "cc" => $current_category));
});

$router->get('/my/keys', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        $referral_search = $__db->prepare("SELECT * FROM referrals WHERE key_from = :key_from ORDER BY id DESC LIMIT 20");
        $referral_search->bindParam(':key_from',  $_SESSION['youtube'], PDO::PARAM_STR);
        $referral_search->execute();
        while($key = $referral_search->fetch(PDO::FETCH_ASSOC)) { 
            $referrals[] = $key;
        }
    
        $referrals['rows'] = $referral_search->rowCount();
        
        echo $twig->render('settings/keys.twig', array("user" => $user, "referrals" => $referrals));
    } else {
        header("Location: /");
    }
});

$router->get('/manage_account', function() use ($twig, $__db, $select) { 
    if(isset($_SESSION['youtube'])) {
        $user = $select->fetch_table_singlerow($_SESSION['youtube'], "users", "youtube_username");
        echo $twig->render('settings/manage.twig', array("user" => $user));
    } else {
        header("Location: /");
    }
});

$router->get('/my/video_liked', function() use ($twig, $__db, $select) { 
    $video_manager = $__db->prepare("SELECT * FROM votes WHERE vote_from = :vote_from ORDER BY id DESC LIMIT 20");
    $video_manager->bindParam(':vote_from',  $_SESSION['youtube'], PDO::PARAM_STR);
    $video_manager->execute();
    while($video = $video_manager->fetch(PDO::FETCH_ASSOC)) { 
        $video = $select->fetch_table_singlerow($video['vote_target'], "videos", "video_id");
        $videos[] = $video;
    }

    $videos['rows'] = $video_manager->rowCount();
    echo $twig->render('video_liked.twig', array("videos" => $videos));
});

$router->get('/my/edit_video', function() use ($twig, $__db, $select) { 
    $video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
    if(isset($_GET['v']) && $select->video_exists($_GET['v']) && $_SESSION['youtube'] == $video['video_author']) {
        echo $twig->render('edit_video.twig', array("video" => $video));
    } else {
        $_SESSION['alert'] = (object) [
            "message" => "This video does not exist!",
            "type" => 1,
        ];

        header("Location: /");
    }
});

$router->get('/watch', function() use ($twig, $__db, $select) { 
    if(isset($_GET['v']) && $select->video_exists($_GET['v'])) {
        $video = $select->fetch_table_singlerow($_GET['v'], "videos", "video_id");
        $user = $select->fetch_table_singlerow($video['video_author'], "users", "youtube_username");
        $video["vote_likes"]    = 0;
        $video["vote_dislikes"] = 0;
        $video[0]["video_views"]   = 0;
        $video["vote_liked"]    = false;
        $video["vote_disliked"] = false;
        $video["author_subscribed"] = false;

        if(isset($_SESSION['youtube'])) {
            $vote_search = $__db->prepare("SELECT vote_type, id FROM votes WHERE vote_target = :vote_target AND vote_from = :vote_from");
            $vote_search->bindParam(':vote_target',  $_GET['v'], PDO::PARAM_STR);
            $vote_search->bindParam(':vote_from',    $_SESSION['youtube'], PDO::PARAM_STR);
            $vote_search->execute();
            $vote = $vote_search->fetch();

            if(isset($vote['id'])) {
                if($vote['vote_type'] == 'l') {
                    $video["vote_liked"] = true;
                } else {
                    $video["vote_disliked"] = true;
                }
            }
        }
        
        if(isset($_SESSION['youtube'])) {
            $sub_search = $__db->prepare("SELECT subscription_to, id FROM subscribers WHERE subscription_to = :sub_to AND subscription_from = :sub_from");
            $sub_search->bindParam(':sub_to',    $video['video_author'], PDO::PARAM_STR);
            $sub_search->bindParam(':sub_from',  $_SESSION['youtube'], PDO::PARAM_STR);
            $sub_search->execute();
            $subscription = $sub_search->fetch();

            if(isset($subscription['id'])) {
                $video["author_subscribed"] = true;
            }
        }
        
        $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target AND view_ip = :view_ip");
        $views_search->bindParam(':view_target',  $_GET['v'], PDO::PARAM_STR);
        $views_search->bindParam(':view_ip',     $_SERVER["HTTP_CF_CONNECTING_IP"], PDO::PARAM_STR);
        $views_search->execute();
        $view = $views_search->fetch();
        if(!isset($view['id']) && isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            if(!isset($_SERVER["HTTP_REFERER"])) {
                $_SERVER["HTTP_REFERER"] = "unknown";
            }
            $stmt = $__db->prepare(
                "INSERT INTO views 
                    (view_video, view_ip, view_referral) 
                 VALUES 
                    (?, ?, ?)"
            );
            $stmt->execute([
                $_GET['v'],
                $_SERVER["HTTP_CF_CONNECTING_IP"],
                $_SERVER["HTTP_REFERER"],
            ]);
        }

        if(isset($_SESSION['youtube'])) {
            $history_search = $__db->prepare("SELECT video_id FROM history WHERE video_id = :video_id AND video_author = :video_author");
            $history_search->bindParam(':video_id',       $_GET['v'], PDO::PARAM_STR);
            $history_search->bindParam(':video_author',   $_SESSION['youtube'], PDO::PARAM_STR);
            $history_search->execute();
            $history = $history_search->fetch();
            if(!isset($history['id'])) {
                $stmt = $__db->prepare(
                    "INSERT INTO history 
                        (video_id, video_author) 
                    VALUES 
                        (?, ?)"
                );
                $stmt->execute([
                    $_GET['v'],
                    $_SESSION['youtube'],
                ]);
            }
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
            // CATEGORY 
            $like_votes_search = $__db->prepare("SELECT id FROM comment_votes WHERE vote_target = :vote_target AND vote_type = 'l'");
            $like_votes_search->bindParam(':vote_target', $comment['id'], PDO::PARAM_STR);
            $like_votes_search->execute();
            $comment["vote_likes"] = $like_votes_search->rowCount();
        
            // CATEGORY 
            $like_votes_search = $__db->prepare("SELECT id FROM comment_votes WHERE vote_target = :vote_target AND vote_type = 'd'");
            $like_votes_search->bindParam(':vote_target', $comment['id'], PDO::PARAM_STR);
            $like_votes_search->execute();
            $comment["vote_dislikes"] = $like_votes_search->rowCount();

            $comments[] = $comment;
            $replyid = "/reply/" . $comment['id'];
            $replies_search = $__db->prepare("SELECT * FROM comment WHERE comment_target = :target ORDER BY id DESC LIMIT 20");
            $replies_search->bindParam(':target', $replyid, PDO::PARAM_STR);
            $replies_search->execute();
            
            while($reply = $replies_search->fetch(PDO::FETCH_ASSOC)) { 
                $comments["replies"] = $reply;
            }
        }
    
        $comments['rows'] = $comments_search->rowCount();

        $videos_search = $__db->prepare("SELECT * FROM videos ORDER BY RAND() LIMIT 13");
        $videos_search->execute();
        $i = 0;
        while($video_n = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
            $views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
            $views_search->bindParam(':view_target',  $video_n['video_id'], PDO::PARAM_STR);
            $views_search->execute();
            $videos_new[] = $video_n;
            $videos_new[$i]["video_views"] = $views_search->rowCount();
            $i++;
        }
    
        $videos_new['rows'] = $videos_search->rowCount();
    
        // CATEGORY 
        $like_votes_search = $__db->prepare("SELECT id FROM votes WHERE vote_target = :vote_target AND vote_type = 'l'");
        $like_votes_search->bindParam(':vote_target', $_GET['v'], PDO::PARAM_STR);
        $like_votes_search->execute();
        $video["vote_likes"] = $like_votes_search->rowCount();
    
        // CATEGORY 
        $like_votes_search = $__db->prepare("SELECT id FROM votes WHERE vote_target = :vote_target AND vote_type = 'd'");
        $like_votes_search->bindParam(':vote_target', $_GET['v'], PDO::PARAM_STR);
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

        $video['video_tags'] = explode(",", $video['video_tags']);
        $video["blocked"]              = $select->user_blocked(@$_SESSION['youtube'], $video['video_author']);
        $video["blocked_recieving"]    = $select->user_blocked($video['video_author'], @$_SESSION['youtube']);
        echo $twig->render('watch.twig', array("video" => $video, "comments" => $comments, "videos_rec" => $videos_new, "user" => $user));
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

$router->get('/terminated', function() use ($twig, $__db) { 
    $ban_search = $__db->prepare("SELECT * FROM bans WHERE ban_to = :to LIMIT 1");
    $ban_search->bindParam(":to", $_SESSION['youtube'], PDO::PARAM_STR);
    $ban_search->execute();
    if($ban_search->rowCount() == 1) {
        $ban = $ban_search->fetch();
        echo $twig->render('terminated.twig', array("ban" => $ban));
    } else {
        header("Location: /");
    }
});

$router->get('/search_query', function() use ($twig, $__db) {
	$start_time = microtime(true);
	if(isset($_GET['q'])) {
		$search = "%" . $_GET['q'] . "%";
        // WHERE concat(city,name) like '%adelaide%'
		$videos_search = $__db->prepare("SELECT * FROM videos WHERE concat(video_title, video_description, video_tags, video_author) LIKE lower(:video_search) ORDER BY RAND() LIMIT 50");
		$videos_search->bindParam(':video_search', $search, PDO::PARAM_STR);
		$videos_search->execute();
		$i = 0;
		while($video_n = $videos_search->fetch(PDO::FETCH_ASSOC)) { 
			$views_search = $__db->prepare("SELECT id FROM views WHERE view_video = :view_target");
			$views_search->bindParam(':view_target',  $video_n["video_id"], PDO::PARAM_STR);
			$views_search->execute();
			$videos[] = $video_n;
			$videos[$i]["video_views"] = $views_search->rowCount();
			$i++;
		}
		
		$videos['rows'] = $videos_search->rowCount();
	} else {
		header("Location: /");
	}
	
	$load = number_format(microtime(true) - $start_time, 2);
    echo $twig->render('search.twig', array("videos" => $videos, "load" => $load));
});

$router->get('/sign_up', function() use ($twig, $__db) { 
    echo $twig->render('sign_up.twig', array());
});

$router->get('/upload_video', function() use ($twig, $__db) { 
    if(isset($_SESSION['youtube'])) {
        $categories = ["None", "Film & Animation", "Autos & Vehicles", "Music", "Pets & Animals", "Sports", "Travel & Events", "Gaming", "People & Blogs", "Comedy", "Entertainment", "News & Politics", "Howto & Style", "Education", "Science & Technology", "Nonprofits & Activism"];
        echo $twig->render('upload_video.twig', array("categories" => $categories));
    } else {
        header("Location: /sign_in");
    }
});

$router->set404(function() use ($twig) {
    header("HTTP/1.1 404 Not Found");
    echo $twig->render('404.twig', array());
});

$twig->addGlobal('config',   $config);
$twig->addGlobal('session',  $_SESSION);
$twig->addGlobal('args',     @$_GET);
$twig->addGlobal('referrer', @$_SERVER['HTTP_REFERER']);
$router->run();

unset($_SESSION['alert']);