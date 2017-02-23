<?php
  //50*n^3
  //NOTE, mamp stack requires <?php and can't process <?
	ob_start(); //NO IDEA WHAT THIS DOES, apparently it's helpful...
	session_start();
	require_once 'dbconnect.php';
  include 'friends.php';

	if( !isset($_SESSION['user']) ) {
		header("Location: index.php");//Redirect
		exit;
	}

  $userRow=mysql_fetch_array(mysql_query("SELECT * FROM users WHERE userId=".$_SESSION['user']));
  $userStats=mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId=".$_SESSION['user']));

  if( $userRow[4] == 0) {//Check if user status (role) if it's not greater than 0, don't allow access.
    header("Location: approval.php");//Redirect
    exit;  
  }

  if($userStats[0]!=$_SESSION['user']) {
    $addStats=mysql_query('INSERT INTO user_stats VALUES ("'.$_SESSION['user'].'","0");');
    $userStats=mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId=".$_SESSION['user']));
    $first_login=1;//Enables something special
  }
  else {
    $first_login=0;//The usual
    $userStats=mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId=".$_SESSION['user']));
    $user_level = $userStats[2];//Set incase it doesn't get put in below.
    $nextLevel = $userStats[2] + 1;
    if ($userStats[1] >= (50*$nextLevel^3)) {
      $user_level = $nextLevel;
      $levelUp = mysql_query('UPDATE user_stats SET user_level='.$user_level.' WHERE userId = '.$_SESSION['user'].';');;
    }
  }
  
  if( isset($_POST['addxp']) && isset($_POST['main'])) {
    $main = trim($_POST['main']);
    $main = strip_tags($main);
    $main = htmlspecialchars($main);

    $bonus = trim($_POST['bonus']);
    $bonus = strip_tags($bonus);
    $bonus = htmlspecialchars($bonus);
    //Eventually this needs some attention for security, cuz you can break it easily with inspector and editing values...
    if (isset($_POST['main'])) {
      $main = $_POST['main'];
    }

    if (isset($_POST['bonus'])) {
      if ($_POST['quest-modifier'] != 0) {
        $bonus = $bonus = $_POST['bonus'] * $_POST['quest-modifier'];
      }
      else {
        $bonus = $_POST['bonus'];
      }
    }

    $quest_complete = mysql_query('INSERT INTO quest_complete VALUES ("'.$_POST['quest'].'","'.$_SESSION['user'].'","0");');//add this quest to done
    //echo "QuestId: ".$_POST['quest'];
    //echo "SessionId: ".$_SESSION['user'];
    $xp_amount_to_give = $main + $bonus;
    $xp_amount_to_set = $xp_amount_to_give + $userStats[1];
    $user_xp = mysql_query('UPDATE user_stats SET user_experience='.$xp_amount_to_set.' WHERE userId = '.$_SESSION['user'].';');
    
    header("Location: index.php");//Redirect - Prevents page reload sending quest complete
    exit;
  }
  else if (isset($_POST['addxp']) && !isset($_POST['main'])){//This shouldn't be possible. But check anyway...
    echo "Invalid Submission! Did you forget a checkbox?";
  }
  
  function isAdmin() {//If Admin, Provide Link to Game Settings
    $userRow=mysql_fetch_array(mysql_query("SELECT * FROM users WHERE userId=".$_SESSION['user']));
    if( $userRow[4] == 200) {
      echo '
      <hr>
      <p><a href="settings.php">Game Management &#9654;</a></p>';
    }
  }

  function getQuests() {
    //So here we need a list of all repeatable quests. But we also need to mark a quest as completed for it's duration, this gets kind of tricky... So what I'm thinking is, we don't use a timer, but rather we store a list of completed quests and a timestamp of when they can be active again. Best thought I have atm... I guess then the quest id would have to be looked up to see if it's currently in the "completed quest" database. If it's one time, it forever is in there (questID and userID paired).  

    //FIRST Run through ALL quest
    $allQuery = mysql_query("SELECT questId FROM quest GROUP BY questId LIMIT 0, 1000;");
    $allQuestsArray = array();
    while ($row = mysql_fetch_array($allQuery, MYSQL_ASSOC)) {
        $allQuestsArray[] =  $row['questId'];
    }

    //SECOND Get completed quests
    $completedQuery = mysql_query("SELECT questId FROM quest_complete WHERE userId=".$_SESSION['user']." GROUP BY questId LIMIT 0, 1000;");
    $completedQuestsArray = array();
    while ($row = mysql_fetch_array($completedQuery, MYSQL_ASSOC)) {
        $completedQuestsArray[] =  $row['questId'];
    }

    //THIRD Get a list of quests excluding completed.
    $allQuery = mysql_query("SELECT questId FROM quest GROUP BY questId LIMIT 0, 1000;");
    $availableQuestsArray = array();
    while ($row = mysql_fetch_array($allQuery, MYSQL_ASSOC)) {
      if (!in_array($row['questId'], $completedQuestsArray)) {
        $availableQuestsArray[] =  $row['questId'];
      }
    }

    //ToDo - Check to see if today is a new day and clear out all the quests that are repeatable from completed_quest

    echo "<span class='custom-line'> Quest Log &#9654; All: ".count($allQuestsArray)." Completed: ".count($completedQuestsArray)." Available: ".count($availableQuestsArray)."</span>";

    $sizeOfArray =  count($availableQuestsArray);//How big is our list?
    $rangeOfArray = 96;//How many to show at a time - this may be more important in the future and would be the ground work for pagination
    $arrayNumber =  0;//This will have to be figured out... can a variable persist like this? going to have to play with some includes here similar to sessions. For now just use 0 (Could also hardcode pages)
    $checkid1 = "1";
    $checkid2 = "2";

    for ($i = 0; $i < $rangeOfArray; $i++) {
        if ($arrayNumber >= $sizeOfArray) { return; }
        $currentArray = mysql_fetch_array(mysql_query("SELECT * FROM quest WHERE questId = ".$availableQuestsArray[$arrayNumber]));
        $arrayNumber++;//advance the selection

        echo
        '<div class="card card--quest center-align">
          <h3 class="card--header has-border"> &#x1f501; '.$currentArray[1].'</h3>
          <div class="card--content">
          <small>'.$currentArray[2].'</small>
            <form method="post">
              <fieldset class="column column--full">
                <div class="checkbox--custom">
                  <input type="checkbox" name="main" value="'.$currentArray[3].'" id="'.$checkid1.'" required>
                  <label for="'.$checkid1.'" class="inline remove-margin--top" value="'.$currentArray[3].'">Main Objective. ('.$currentArray[3].')</label>
                </div>';//Part 1 Complete
                if ($currentArray[5] != 0) {
                  echo'<div class="">
                  <input type="text" name="bonus" id="'.$checkid2.'">
                  <label for="'.$checkid2.'" class="inline remove-margin--bottom" value="'.$currentArray[5].'">Bonus Objective. ( Hours * '.$currentArray[5].')</label>
                </div>
                <input name="quest-modifier" type="hidden" value="'.$currentArray[5].'">
                <input name="quest" type="hidden" value="'.$currentArray[0].'">
              </fieldset>
              <input class="button button--smooth button--green" type="submit" name="addxp" value="Complete Quest">
            </form>
          </div>
        </div>';
                }
                else {
                echo'<div class="checkbox--custom">
                  <input type="checkbox" name="bonus" value="'.$currentArray[4].'" id="'.$checkid2.'">
                  <label for="'.$checkid2.'" class="inline remove-margin--bottom" value="'.$currentArray[4].'">Bonus Objective. ('.$currentArray[4].')</label>
                </div>
                <input name="quest" type="hidden" value="'.$currentArray[0].'">
              </fieldset>
              <input class="button button--smooth button--green" type="submit" name="addxp" value="Complete Quest">
            </form>
          </div>
        </div>'
        ;//end echo
        }
        $checkid1 = $checkid1 + $checkid2;//change the checkid to prevent labels activating other cards. These create id 1,2,3,4,etc... 
        $checkid2 = $checkid2 + $checkid2;
    }
  }


?>
<!DOCTYPE html>
<html>
<head>
<title>Design Arena - Page1</title>
  <link rel="stylesheet" href="./css/dashing.css" type="text/css" />
  <link rel="stylesheet" href="./css/custom.css" type="text/css" />
</head>

<body>
  <div class="app-content">
    <div class="card card--login">
      <div class="card--header card--header-padding has-border">
        <h3 class="card--user float-left"><?php echo $userRow[1]; ?> - Lvl: <?php echo $user_level; ?></h3>
        <small class="card--user-signout float-right"><a href="logout.php?logout">Sign Out</a></small>
      </div>
      <div class="card--content">
        <?php if($first_login == 1) {
            echo "<p class='remove-margin--top'>First Login! (Could do something special here.)</p>";
            //echo $_SESSION['user'];
            }
            else {
              echo "<p class='remove-margin--top'>Welcome Back!</p>";
            } 
        ?>
        <p class="remove-margin--top"> Character Information: </p>
        <span> XP: <?php echo $userStats[1]; ?></span>
        <?php isAdmin(); ?>
      </div>
    </div>
    <div class="container-quest">
      <?php getAllUsers(); ?>
    </div>
    <div class="container-quest">
      <?php getQuests(); ?>
    </div>
  </div>
</body>
</html>
<?php ob_end_flush(); //NO IDEA WHAT THIS DOES, apparently it's helpful...?>