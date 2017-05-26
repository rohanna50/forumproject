<?php
  session_start();
  $url2 = -1;
  $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
  if(substr($url, -1) != 'p'){
    $url2 = substr($url, -1);
  }
  //echo($url2);
  function add_reply($reply){
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    //change the last one reference the current topic id - $_SESSION(name)??
    
    $statement = $dbhandle->prepare("insert into reply (replyid, 'username','content', 'topicid') values (NULL,'".$_SESSION["username"]."',':reply','".$url2."' )");
    $statement->bindParam(':reply', $reply);
    $statement->execute();
  };
  
  function add_topic($title, $description){
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("insert into topics values(NULL ,:title, :description)");
    $statement->bindParam(':title', $title);
    $statement->bindParam(':description', $description);
    $statement->execute();
  }
  
  //add this logic for replies maybe just change name and call after loading subchats
  function render_replies($filter=false){
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    if ($filter != false){
      $statement = $dbhandle->prepare("select username, content from reply where reply like '%".$filter."%' order by id DESC");
    } else {
      $statement = $dbhandle->prepare("select username, content, reply.topicid from reply join topics on reply.topicid = topics.title");
    }
    $statement->execute();
    $replies = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    $template = file_get_contents("discussion.html");
    $reply_template = file_get_contents("reply.html");
    
    $reply_rows = "";
    foreach($replies as $reply){
      $reply_rows .= str_replace("USERNAME", $reply["username"], 
                            str_replace("REPLYHERE", $reply["content"], $reply_template));
    }
    echo str_replace("REPLYHERE", $reply_rows, 
        str_replace("MYUSERNAME",$_SESSION["username"], $template));
  };
  
  function render_forum($filter=false){
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    
    $statement = $dbhandle->prepare("select topicid, title, titdesciption from topics order by topicid DESC limit 0, 100");
    
    $statement->execute();
    $topics = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    $template = file_get_contents("forum.html");
    $topic_template = file_get_contents("topics.html");
    
    $topic_rows = "";
    foreach($topics as $topic){
      $topic_rows .= str_replace("USERNAME", $topic["title"], 
                            str_replace("TOPICHERE", $topic["titdesciption"], 
                            str_replace("LINKHERE", $url . $topic["title"] . "/" . $topic["topicid"],$topic_template)));
    }
    echo str_replace("TOPICHERE", $topic_rows, 
        str_replace("MYUSERNAME",$_SESSION["username"], $template));
  };
  
  function render_login($reply = ""){
    $template = file_get_contents("login.html");
    echo str_replace("REPLYHERE", $reply, $template);
  };
  
  function login($username, $pwd){
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("Select * from user where username= :user and password= :pwd");
    $statement->bindParam(':user',$username);
    $statement->bindParam(':pwd',$pwd);
    $statement->execute();
    $results = $statement->fetch(PDO::FETCH_ASSOC);
    if (isset($results["username"])){
      $_SESSION["username"] = $results["username"];
      $_SESSION["logged_in"] = "1";
      render_forum();
    } else {
      render_login("Failed authentication");
      alert("That combination doesn't exist. Try again..");
    }
  };
  
  function logout(){
    session_start();
    unset($_SESSION["username"]);
    unset($_SESSION["logged_in"]);
  };
   
  function changeURL(){
    $url = $url . $_SERVER["REQUEST_URI"];
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    
    $statement = $dbhandle->prepare("select topicid, title, titdesciption from topics");
    $statement->execute();
    $topics = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($topics as $topic){
      $id = $topic["topicid"];
    };
    
  };
  
  function register($username, $pwd){
    $dbhandle = new PDO("sqlite:forum.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("insert into user values(NULL ,:user,:pwd)");
    $statement->bindParam(':user',$username);
    $statement->bindParam(':pwd', $pwd);
    $statement->execute();
    $_SESSION["username"] = $username;
    $_SESSION["logged_in"] = "1";
  };
  
  if (isset($_SESSION["logged_in"])){
    if ($_SESSION["logged_in"] == "1"){
      if (isset($_POST["logout"])){
          logout();
          render_login();
      } else if (isset($_POST["reply"])){
          add_reply($_POST["reply"]);
          render_replies();
      } else if (isset($_POST["description"])){
          add_topic($_POST["title"], $_POST["description"]);
          render_forum();
      } else if ($url != "http://forumproject2-tylerapo.c9users.io/" && $url != "http://forumproject2-tylerapo.c9users.io/index.php"){
          render_replies();
      }else {
        render_forum();
      }
    }
  } else {
    if (isset($_POST["login"])){
        login($_POST["username"], $_POST["password"]);
    } else if (isset($_POST["register"])) {
        register($_POST["username"], $_POST["password"]);
        render_forum();
    } else {
        render_login();
    }
  }
?>