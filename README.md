# notification_center
Result of technical test

## Installing / Getting started

A quick introduction of the minimal setup you need to get a hello world up &
running.

```shell
git clone https://github.com/deg-daniel/notification_center
install mysql
mysql -uroot <dump.sql
php -S localhost:8000 index.php
```
## API reference/exemple
```
  Json server, API syntax
  not implemented; versioning, security
  
  /get_all/user/{user_id}/
  browse all of my notifications (no matter if read or unread), sorted from the newest to the oldest
  have a specific message if I don’t have any notification yet. (No need to code an example, we will simulate this use case by playing with your mock)
  { notifications:
   [ {
      type: 'recommendation',
      content: { title: 'Je pense', description: 'Douma'},
      validity_period :  '2022-11-22T13:12:05.000Z',
      description: 'Best Song'
     },
     { ...}
   ],
   message: 'present only if notifications is an empty array'
  }
 
  /get_count/user/{user_id}
 	know how many notifications I have in my notification center, and how many of them are unread
   { total: 5, unread: 1 }
 
  /is_read/user/{user_id}/notif/{notif_id}
  { read: True }
 
  /set_read/user/{user_id}/notif/{notif_id}
 	Know whether a notification is read or unread, and mark it as read
  { is_done: True }
```

## BD schema

<img width="777" alt="Capture d’écran 2023-01-20 à 13 01 50" src="https://user-images.githubusercontent.com/84505471/213690623-fb1ac7e2-3b68-47c3-9374-b6c9561a5c00.png">

## Class schema

<img width="976" alt="Capture d’écran 2023-01-23 à 09 47 24" src="https://user-images.githubusercontent.com/84505471/213998491-4b18299c-a375-41df-995b-1382db33f3c0.png">


## Some explainations
 * this notification server must work with a website or a mobile application. I choose a JSON server that seems appropriate to me. Best pratice: https://www.freecodecamp.org/news/rest-api-best-practices-rest-endpoint-design-examples/
 
 
 * the 4 content tables extend the content of the notifications table. We could add a constraint in the database, or in the application
 * a user can have multiple notifications and a notification can have multiple users, it's an NxN relationship, with the "is_read" attribute depending on the combination of the two.
 * if the image of the notification must be taken into account, we could add a string field in the Notification table
 * if the 'shared content' notification must be linked to the user who shared the content, an 'id_user_who_shared' field should be added to the rel_notification_user table
 
 
 * I used class inheritance to share common parts of notifications
 * the routing is done with regex, but we could use a more complete solution with a framework and attributes
 * I coded a function to sort by date, at the level of the NotificationService object

## Unit test

 insert same data into mysql, and do some test:
  
 /get_all/user/1 check attribut, content, and count of element in notifications array
 
 /get_all/user/1 don't return a message
 /get_all/user/2 return a message
 
 /is_read/user/1/notif/1 check is_read is False
  
 /set_read/user/1/notif/1 check is_done is true, and after /is_read/user/1/notif/1  must return is_read True
  
