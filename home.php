<?php
  //NOTES: 
  //mamp stack requires <?php and can't process <? by [default], it can be enabled, but I'm assuming for the project that it's not.
  //mysql should be prepared before making statements
  //mysqli has replaced these functions, revise at a later date
  //The php could be in its own file. This would make pairing with javascript easier.
	ob_start();
	session_start();
	require_once 'dbconnect.php';
  include 'friends.php';

  //Start Up
	if( !isset($_SESSION['user']) ) {
		header("Location: index.php");//Redirect
		exit;
	}

  //If all is good, go ahead and get user info:
  else {
    $userRow=mysql_fetch_array(mysql_query("SELECT * FROM users WHERE userId=".$_SESSION['user']));
    $userStats=mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId=".$_SESSION['user']));
    //Check Account Type
    if( $userRow[4] == 0 || $userRow[4] == -1) {//Check if user status (role) if it's not greater than 0, don't allow access. -1 is banned.
      //Redirect to page letting user know they are not approved.
      header("Location: approval.php");
      exit;
    }
  }

  //The user doesn't yet exist. 
  if($userStats[0]!=$_SESSION['user']) {
    $addStats=mysql_query('INSERT INTO user_stats VALUES ("'.$_SESSION['user'].'","0","0");');
    $userStats=mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId=".$_SESSION['user']));
    $first_login=1;
  }

  //The user has been here before.
  else {
    $first_login=0;
    //Calculate user level (Can this be done in SQL?)
    $userStats=mysql_fetch_array(mysql_query("SELECT * FROM user_stats WHERE userId=".$_SESSION['user']));
    $user_level = $userStats[2];
    $nextLevel = $userStats[2] + 1;
    $untilNext = 100*($nextLevel**2);//($nextLevel*($nextLevel-1)*300); <-previous

    //XP FORMULA
    if ($userStats[1] >= $untilNext) {//500*($nextLevel^3 // Formula from: https://forum.rpg.net/showthread.php?228600-D-amp-D-3-3-5-XP-Formula //100($nextlevel^2)
      $user_level = $nextLevel;
      $levelUp = mysql_query('UPDATE user_stats SET user_level='.$user_level.' WHERE userId = '.$_SESSION['user'].';');
    }
  }

  //Posted Announcement
  $announcementFetch = mysql_fetch_array(mysql_query('SELECT * FROM announcement WHERE announcementId=0'));
  $nonPersonalAnnouncement = $announcementFetch[1];

  //Request to add XP for closing something.
  if(isset($_POST['addxp']) && isset($_POST['main'])) {
    $mainStrip = trim($_POST['main']);
    $mainStrip = strip_tags($mainStrip);
    $mainStrip = htmlspecialchars($mainStrip);

    $bonusStrip = trim($_POST['bonus']);
    $bonusStrip = strip_tags($bonusStrip);
    $bonusStrip = htmlspecialchars($bonusStrip);

    if (isset($_POST['main'])) {
      $main = $mainStrip;
    }

    if (isset($_POST['bonus'])) {
      if ($_POST['quest-modifier'] != 0) {
        $bonus = $bonus = $_POST['bonus'] * $_POST['quest-modifier'];
      }
      else {
        $bonus = $bonusStrip;
      }
    }

    $quest_complete = mysql_query('INSERT INTO quest_complete VALUES ("'.$_POST['quest'].'","'.$_SESSION['user'].'","0");');//add this quest to done
    echo "Debug: QuestID=".$_POST['quest']." This User= ".$_SESSION['user'];
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
      <a href="settings.php"><i class="dashing-icon dashing-icon--settings"></i>Admin Panel</a>';
    }
  }

  function getQuests() {
    //FIRST Run through ALL quest.
    $allQuery = mysql_query("SELECT questId FROM quest GROUP BY questId LIMIT 0, 1000;");
    $allQuestsArray = array();
    while ($row = mysql_fetch_array($allQuery, MYSQL_ASSOC)) {
      $allQuestsArray[] =  $row['questId'];
    }
    //SECOND Get completed quests.
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
    //FOURTH Build html for displaying quest info.
    echo "<span class='custom-line'> Quest Log &#9654; All: ".count($allQuestsArray)." Completed: ".count($completedQuestsArray)." Available: ".count($availableQuestsArray)."</span>";

    //Needs Attention Later
    $sizeOfArray =  count($availableQuestsArray);//How big is our list?
    $rangeOfArray = 96;//How many to show at a time - this may be more important in the future and would be the ground work for pagination
    $arrayNumber =  0;//Need to persist a page number that the user is currently on. Not sure how. For now always start at 0.
    $checkid1 = "1";
    $checkid2 = "2";

    for ($i = 0; $i < $rangeOfArray; $i++) {
        if ($arrayNumber >= $sizeOfArray) { return; }
        $currentArray = mysql_fetch_array(mysql_query("SELECT * FROM quest WHERE questId = ".$availableQuestsArray[$arrayNumber]));
        $arrayNumber++;//advance the selection
        //Template for Quest Cards
        echo
        '<div class="card card--quest center-align">
          <h3 class="card--header has-border"> <i class="dashing-icon dashing-icon--question-filled"></i> '.$currentArray[1].'</h3>
          <div class="card--content">
          <small>'.$currentArray[2].'</small>
            <form method="post">
              <fieldset class="column column--full">
                <div class="checkbox--custom">
                  <input type="checkbox" name="main" value="'.$currentArray[3].'" id="'.$checkid1.'" required>
                  <label for="'.$checkid1.'" class="inline remove-margin--top" value="'.$currentArray[3].'">Main Objective. ('.$currentArray[3].')</label>
                </div>';
                //Build this for Normal Bonus
                if ($currentArray[5] != 0) {
                  echo'<div class="">
                  <input type="text" name="bonus" id="'.$checkid2.'">
                  <label for="'.$checkid2.'" class="inline remove-margin--bottom" value="'.$currentArray[5].'">Bonus Objective. ( Bonus Unit * '.$currentArray[5].')</label>
                </div>
                <input name="quest-modifier" type="hidden" value="'.$currentArray[5].'">
                <input name="quest" type="hidden" value="'.$currentArray[0].'">
              </fieldset>
              <input class="button button--smooth button--green" type="submit" name="addxp" value="Complete Quest">
            </form>
          </div>
        </div>';
                }
                //Build this for Text Field Bonus
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
        $checkid1 = $checkid1 + $checkid2;
        $checkid2 = $checkid2 + $checkid2;
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
<title>Design Arena - My Page</title>
  <link rel="stylesheet" href="./css/dashing.css" type="text/css" />
  <link rel="stylesheet" href="./css/custom.css" type="text/css" />
</head>

<body>
  <nav class="app-menu app-title--orange">
    <div class="app-context">
      <div class="app-title">Design Arena</div>
    </div>
    <ul class="app-navigation align-right">
      <li><?php isAdmin(); ?></li>
      <li><a href="logout.php?logout"><i class="dashing-icon dashing-icon--close"></i>Sign Out</a></li>
    </ul>
  </nav>
  <div class="app-content">
    <div class="card card--login">
      <div class="card--header card--header-padding has-border">
        <h3 class="card--user float-left"><?php echo $userRow[1]; ?> - Lvl: <?php echo $user_level; ?></h3>
      </div>
      <div class="card--content">
        <?php if($first_login == 1) {
            echo "<span>Welcome!</span>";
            //echo $_SESSION['user'];
            }
            else {
              echo "<span>Welcome Back!</span>";
              echo "<p>".$nonPersonalAnnouncement."</p>";
            } 
        ?> 
        <hr>
        <progress id="levelBar" value="<?php echo $userStats[1]; ?>" min="0" max="<?php echo $untilNext; ?>" style="width: 100%;"></progress>
        <p class="remove-margin--top"><?php echo $userStats[1]; ?> / <?php echo $untilNext; ?> XP</p>
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
<?php ob_end_flush(); ?>