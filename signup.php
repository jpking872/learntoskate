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

writeLog('in signup');

$oDataModel = new DataModel($sessionUser, $dbconnection);
$oDataModel->SetUser($sessionUser);
$userData = $oDataModel->GetUserData();
$oLTS = new LTS($dbconnection);

$sError = "";

if ($_POST) {

    if (isset($_POST['class_submit'])) {

        $uid = $sessionUser;
        $oLTS->removeUserFromActiveClasses($uid);
        if (array_key_exists('classToAdd', $_POST) && is_array($_POST['classToAdd'])) {
            foreach ($_POST['classToAdd'] as $key => $value) {
                $oLTS->addUserToClass($uid, $value);
            }
        }
    }

}

include_once("header.php");

?>

    <div class="infoBar">
        <div class="pageTitle">SIGN UP TO LEARN TO SKATE</div>
    </div>
    <div class="mainContent">

        <?php echo empty($message) || $message == "" ? "" : "<p class=\"message\">$message</p>"; ?>

        <div class="classDiv">
            <form action="" method="post" id="class_form">
                <?php

                $skaterName = $userData['sfname'] == $userData['fname'] && $userData['slname'] == $userData['lname'] ? "" : " (" . $userData['sfname'] . " " . $userData['slname'] . ")";
                $aLevels = $oLTS->GetLevels();
                $level = "Not Approved";
                for($i = 0; $i < count($aLevels); $i++) {
                    if ($aLevels[$i]['id'] == $userData['level']) {
                        $level = $aLevels[$i]['level'];
                    }
                }
                ?>

                <h2>
                    <span class="skaterName"><?php echo $userData['fname'] . " " . $userData['lname'] . $skaterName ?></span><br/>
                    <span class="skaterName"><?php echo $level ?></span>
                </h2>
                <ul>
                    <?php

                    $oData = new DataModel($sessionUser, $dbconnection);
                    $aResult = array();
                    $aResult['purchases'] = $oData->GetPurchasesForUser();
                    $aResult['totalpurchases'] = $oData->GetTotalPurchasesForUser();
                    $aResult['totalpayments'] = $oData->GetTotalPaymentsForUser();
                    $totalClasses = $oData->GetNumberOfClasses();
                    $totalPassClasses = $oData->GetNumberOfClassesWithPass();
                    $balance = $oData->GetUserBalance();

                    $classList = $oLTS->GetLTSClasses($userData);
                    $signupClasses = $oLTS->getClassesByUid($sessionUser);
                    $registeredClasses = array();
                    for($i = 0; $i < count($signupClasses); $i++) {
                        $registeredClasses[] = $signupClasses[$i]['id'];
                    }

                    for ($i = 0; $i < count($classList); $i++) {
                        $tmp = $classList[$i];

                        if (strtotime($tmp['start']) < time() + 60 * 15) continue;
                        $classSize = $oLTS->getClassSize($tmp['id']);

                        $isRegisteredThisClass = in_array($tmp['id'], $registeredClasses);

                        $isBoxChecked = $isRegisteredThisClass ? " checked" : "";
                        $isBoxGold = $isRegisteredThisClass ? " gold" : "";

                        $classDate = $tmp['start'];
                        $passClassDay = $oDataModel->HasUserPass($sessionUser, date("n", strtotime($classDate)), date("Y", strtotime($classDate)));

                        if (($classSize >= $tmp['size'] && !$isRegisteredThisClass) || ($balance <= 0 && !$passClassDay && !$isRegisteredThisClass) || (!$isRegisteredThisClass && (time() > strtotime($tmp['end']))) ) {
                            echo "<li><div class=\"sessionFull\"><span style=\"color:#FFFFFF\">&#10006</span></div>";
                            echo "<div class=\"classTitle" . $isBoxGold . "\">" . $tmp['title'] . ": " .
                                date('l F j g:ia', strtotime($tmp['start'])) . " - " . date('g:ia', strtotime($tmp['end'])) . " (" . $classSize . ")</div></li>";
                        } else if ($isRegisteredThisClass && (time() > strtotime($tmp['start']))) {
                            echo "<li><div class=\"sessionCheck\"><span style=\"color:#FFFFFF\">&#10003</span></div>";
                            echo "<div class=\"classTitle gold\">" . $tmp['title'] . ": " .
                                date('l F j g:ia', strtotime($tmp['start'])) . " - " . date('g:ia', strtotime($tmp['end'])) . " (" . $classSize . ")</div></li>";
                        } else {
                            echo "<li><input type=\"checkbox\" name=\"classToAdd[]\"" . $isBoxChecked . " value=\"" . $tmp['id']
                                . "\"><div class=\"classTitle" . $isBoxGold . "\">" . $tmp['title'] . ": " .
                                date('l F j g:ia', strtotime($tmp['start'])) . " - " . date('g:ia', strtotime($tmp['end'])) . " (" . $classSize . ")</div></li>";
                        }

                    }

                    ?>
                </ul>
                <div style="clear:both"></div>
                <div class="summary">

                    Points used: <span id="totalClasses"><?php echo($totalClasses - $totalPassClasses) ?></span><span
                            class="green">(<?php echo $totalPassClasses ?>)</span> |
                    Total purchased: <span id="totalPurchases"><?php echo $aResult['totalpurchases'] ?></span> |
                    Point balance:

                    <?php

                    $pointsBal = $balance;
                    if ($pointsBal < 0) {
                        $pointsBalClass = " class=\"red\"";
                    } else {
                        $pointsBalClass = "";
                    }

                    ?>

                    <span id="pointBalance"<?php echo $pointsBalClass ?>><?php echo $pointsBal ?></span>

                </div>
                <div style="clear:both"></div>
                <input class="pointButton" type="submit" name="class_submit" value="Sign Up"></form>
                <a href="/profile.php?userid=<?php echo $sessionUser ?>"><button class="pointButton">Profile</button></a>
            <div style="clear:both"></div>

        </div>

        <div style="clear:both"></div>


    </div>

<script type="text/javascript">
    $(document).ready(function () {
        let totalChecked, balance;
        let startPoints = <?php echo $balance; ?>;
        let startChecked = $("input[name='classToAdd[]']:checked").length;
        let hasPass = <?php echo $passClassDay ? 1 : 0 ?>;

        $("input[name='classToAdd[]']").click(function(e) {
            if (!hasPass) {
                totalChecked = $("input[name='classToAdd[]']:checked").length;
                balance = startPoints - totalChecked + startChecked;
                $("#pointBalance").text(balance);
                if (parseInt($("#pointBalance").text()) <= 0) {
                    $("span#pointBalance").addClass("red");
                    $("input[name='classToAdd[]']:not(:checked)").attr("disabled", true);
                } else {
                    $("span#pointBalance").removeClass("red");
                    $("input[name='classToAdd[]']:not(:checked)").attr("disabled", false);
                }
            }
        })


    })
</script>

<?php

include_once("footer.php");

?>