<?php
require_once 'DB.php';


/**
 * Json server, API syntax
 * not implemented; version, security
 * 
 * /get_all/user/{user_id}/
 * 	browse all of my notifications (no matter if read or unread), sorted from the newest to the oldest
 *  have a specific message if I don’t have any notification yet. (No need to code an example, we will simulate this use case by playing with your mock)
 * { notifications:
 *  [ 
 *    {
 *     type: 'recommendation',
 *     content: { title: 'Je pense', description: 'Douma'},
 *     validity_period :  '2022-11-22T13:12:05.000Z',
 *     description: 'coucou'
 *    },
 *    { ...}
 *  ],
 *  message: 'present only if notifications is an empty array'
 * }
  
 * /get_count/user/{user_id}
 *	know how many notifications I have in my notification center, and how many of them are unread
 *  { total: 5, unread: 1 }
 *
 * /is_read/user/{user_id}/notif/{notif_id}
 * { read: True }
 * 
 * /set_read/user/{user_id}/notif/{notif_id}
 *	Know whether a notification is read or unread, and mark it as read
 * { is_done: True }
 */


 // TODO mettre les classes dans des fichiers séparés
class Notification {
    protected $id=1;
    protected $description = "dummy";
    //..

    public function toArray(): array {
        return [
            'id'         => $this->id,
            'description'=> $this->description,
            'content'    => null //will be override by children
            //...
        ];
    }
    public function getPeriod(): DateTime  {
        return $this->period;
    }
}

class Podcast extends Notification {
    //..
}

class Playlist extends Notification {
    //..
}

class Track extends Notification {
    //..
}

class Album extends Notification {
    protected $name= "";
    public function __construct(string $name) {
        $this->name = $name;
    }
    // I use inheritance so that each class describes its parameters
    public function toArray(): array {
        $notif = parent::toArray();
        $notif['content'] = $this->name;
        return $notif;
    }
}

// I use a service class to manage actions on "Notification" type objects
class NotificationService {
    protected int $user_id;
    public function __construct(int $user_id) {
        $this->user_id = $user_id;
    }

    protected function sortByNewest(&$notifications) {
        usort($notifications, fn($a,$b) =>  $a->getPeriod() > $b->getPeriod() );
    }
    protected function convToArray(&$notification) {
        $res = [];
        foreach($notification as $notif) {
            $res[] = $notif->toArray();
        }
        $notification = $res;
    }

    public function getAll() {
        $query ="select n.*,a.name as name from notification n 
        join rel_notification_user rnu on n.notification_id = rnu.id_notification 
        join `user` u on u.user_id  = rnu.id_user 
        left join album a on a.album_id = n.id_album 
        where u.user_id=".$this->user_id;
        // TODO complete request ..

        $statement = DB::getInstance()->query($query);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $notifications = [];
        foreach($rows as $row) {
            switch ($row['type']) {
                case 'album':
                    // to create an album, we can only give the ID parameter, but that will require another sql query
                    $notif = new Album($row['name']);
                
                // etc.
            }
            
            $notifications[] = $notif;
        }
        
        if (empty($notifications)) {
            $result = ['notifications' => [], 'message'=>'Pas de notification nouvelle'];
        } else {
            $this->sortByNewest($notifications);
            $this->convToArray($notifications);
            $result = ['notifications: ' => $notifications];                
        }
        return $result;
    }
    function getCount() {
        $query = "select count(*) from ...";
        return ['total'=>2, 'read'=>1 ];
    }
    function isRead($idNotification) {
        return ['is_read' => false ];
    }
    function setRead($idNotification) {
        $query = "UPDATE WHERE..";
        $isDone = true;
        return ['is_done' => $isDone];
    }

}

// mini router 
// php -S localhost:8000 index.php
$url = $_SERVER["REQUEST_URI"];
$res = ["error" => "panic"];
try {
    if (preg_match_all('#/get_all/user/(?<user_id>[0-9]+)#', $url, $matches)) {
        $res = (new NotificationService( $matches['user_id'][0] ))->getAll();
    } elseif (preg_match_all('#/get_count/user/(?<user_id>[0-9]+)#', $url, $matches)) {
        $res = (new NotificationService( $matches['user_id'][0] ))->getCount();
    } elseif (preg_match_all('#/is_read/user/(?<user_id>[0-9]+)/notif/(?<notif_id>[0-9]+)#', $url, $matches)) {
        $res = (new NotificationService( $matches['user_id'][0] ))->isRead( $matches['notif_id'][0] );
    } elseif (preg_match_all('#/set_read/user/(?<user_id>[0-9]+)/notif/(?<notif_id>[0-9]+)#', $url, $matches)) {
        $res = (new NotificationService( $matches['user_id'][0] ))->setRead( $matches['notif_id'][0] );
    }
    
} finally {
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($res);

/*
 * test with simple data in database
 * 
 * /get_all/user/1 check content, type and count of element in notifications array
 *
 * /get_all/user/1 don't return a message
 * /get_all/user/2 return a message
 *
 * /is_read/user/1/notif/1 check is_read is False
 * 
 * /set_read/user/1/notif/1 check is_done is true, and after /is_read/user/1/notif/1  must return is_read True
 * 
 */