<?php

include_once("incl/session.php");
include_once("incl/database.php");
include_once("incl/library.php");
include_once("incl/config.php");
include_once("classes/DataModelC.php");
include_once("classes/ClassesC.php");
include_once("classes/LTS.php");

if (!$sessionUser) {
    header("Location: /login.php");
}

$oDataModel = new DataModel($sessionUser, $dbconnection);
$oLTS = new LTS($dbconnection);
$classes = $oLTS->GetActiveClasses();

$sError = "";

if ($_POST) {

    if (isset($_POST['class_submit'])) {

        $uid = $_POST['skater'];
        $oLTS->removeUserFromActiveClasses($uid);
        if (array_key_exists('classToAdd', $_POST) && is_array($_POST['classToAdd'])) {
            foreach ($_POST['classToAdd'] as $key => $value) {
                $oLTS->addUserToClass($uid, $value);
            }
        }
    }

}

//$aTimes = $oDataModel->GetSchedule($dbdate);
//$aSession = $oDataModel->GetSession($dbdate);
$aUser = $oDataModel->GetUserData();
//$balance = $oDataModel->GetUserBalance();
//$numClasses = $oDataModel->GetNumberOfClasses();
//$numPassClasses = $oDataModel->GetNumberOfClassesWithPass();
//$deduct = $oDataModel->GetNumberOnePointClasses();
//$deductPass = $oDataModel->GetNumberOnePointClassesWithPass();


include_once("header.php");

?>

    <div class="infoBar">
        <div class="pageTitle">SIGN UP TO LEARN TO SKATE</div>
    </div>
    <div class="mainContent">

        <?php echo empty($message) || $message == "" ? "" : "<p class=\"message\">$message</p>"; ?>

    <div class="classDiv">
        <form action="" method="post" id="class_form">
            <input type="hidden" name="skater" id="skater" value="<?php echo $sessionUser ?>">

                <?php
                $balance = 10;
                $classList = $oLTS->getClasses();
                $signupClasses = $oLTS->getClassesByUid($sessionUser);
                $registeredClasses = array();
                for ($i = 0; $i < count($signupClasses); $i++) {
                    $registeredClasses[] = $signupClasses[$i]['id'];
                }

                for ($i = 0; $i < count($classes); $i++) {
                $tmpSession = $classes[$i]['session'];

                $currentSessionTitle = date("l, F j, g:ia", strtotime($tmpSession['start'])) . " to " . date("g:ia", strtotime($tmpSession['end']));

    ?>
    <div class="ltsHeaderBar">
        <div class="typeHeader"><?php echo $currentSessionTitle; ?></div>
    </div>

                    <ul>

                        <?php for ($j = 0; $j < count($classes[$i]['classes']); $j++) {
                            $tmpClass = $classes[$i]['classes'][$j]; ?>

                                <p class="ltsName"><?php echo $tmpClass['title'] ?></p>

                        <?php } ?>
                    </ul>
                <?php /*for ($i = 0; $i < count($classList); $i++) {
                    $tmp = $classList[$i];

                    if (strtotime($tmp['start']) < strtotime("last Sunday") - 86400 * 7) continue;
                    $classSize = $oLTS->getClassSize($tmp['id']);

                    $isRegisteredThisClass = in_array($tmp['id'], $registeredClasses);
                    $isBoxChecked = $isRegisteredThisClass ? " checked" : "";
                    $isBoxGold = $isRegisteredThisClass ? " gold" : "";

                    $classDate = $tmp['start'];
                    $passClassDay = $oDataModel->HasUserPass($sessionUser, date("n", strtotime($classDate)), date("Y", strtotime($classDate)));

                    if (($classSize >= $tmp['size'] && !$isRegisteredThisClass) || ($balance <= 0 && !$passClassDay) || (!$isRegisteredThisClass && (time() > strtotime($tmp['end'])))) {
                        echo "<li><div class=\"sessionFull\"><span style=\"color:#FFFFFF\">&#10006</span></div>";
                        echo "<div class=\"classTitle" . $isBoxGold . "\">" . $tmp['title'] . " " .
                            date('l F j g:ia', strtotime($tmp['start'])) . " - " . date('g:ia', strtotime($tmp['end'])) . " (" . $classSize . ")</div></li>";
                    } else if ($isRegisteredThisClass && (time() > strtotime($tmp['start']))) {
                        echo "<li><div class=\"sessionCheck\"><span style=\"color:#FFFFFF\">&#10003</span></div>";
                        echo "<div class=\"classTitle gold\">" . $tmp['title'] . " " .
                            date('l F j g:ia', strtotime($tmp['start'])) . " - " . date('g:ia', strtotime($tmp['end'])) . " (" . $classSize . ")</div></li>";
                    } else {
                        echo "<li><input type=\"checkbox\" name=\"classToAdd[]\"" . $isBoxChecked . " value=\"" . $tmp['id']
                            . "\"><div class=\"classTitle" . $isBoxGold . "\">" . $tmp['title'] . " " .
                            date('l F j g:ia', strtotime($tmp['start'])) . " - " . date('g:ia', strtotime($tmp['end'])) . " (" . $classSize . ")</div></li>";
                    }

                }*/
                }
                ?>

            <div style="clear:both"></div>
            <input class="pointButton" type="submit" name="class_submit" value="Sign Up">
            <div style="clear:both"></div>
        </form>

        <p>Points Remaining: <?php echo $balance ?> | Maximum <?php echo MAX_SKATERS ?> skaters per session.</p>

        <?php

        if ($balance <= 0) {
            $balanceMessage = "<p class=\"errorText\">Your points balance is 0, you must purchase more points to register.  
                                            Click <a href=\"https://squareup.com/store/ice-skate-usa\" class=\"userLink\">here</a> to purchase points</p>";
            $sessionOpen = false;
        } else if ($balance < 10) {
            $balanceMessage = "<p>Your points balance is below 10, 
                                            Click <a href=\"https://squareup.com/store/ice-skate-usa\" class=\"userLink\">here</a> to purchase points.</p>";
        } else {
            $balanceMessage = "";
        }

        echo $balanceMessage;

        ?>
    </div>
        <div style="clear:both"></div>

    </div>

<?php

include_once("footer.php");

?>