
BEGIN TRANSACTION;

Create table user(userid integer primary key autoincrement, username text, password text );
Create table topics(topicid integer primary key autoincrement, title text, titdesciption text );
Create table reply(replyid integer primary key autoincrement, content TEXT, topicid int, foreign key(topicid) references topics(topicid));
insert into "user" values(0, 'tyler', 'password');
insert into "topics" values(0, 'Welcome page', 'Welcome to the forums!');
insert into "reply" values(0, 'Thanks!', 0);

COMMIT;