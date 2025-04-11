<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");
    include_once("classes/ClassesC.php");

	if (!$sessionUser) {
		header("Location: /login.php");
	}

    $oDataModel = new DataModel($sessionUser, $dbconnection);
    $oClasses = new Classes($dbconnection);

	if (!empty($_GET) && !empty($_GET['date'])) {
		$dbdate = $_GET['date'];
	} else {
        $dbdate = $oDataModel->GetNextSessionDate();
	}

    $passThisDay = $oDataModel->HasUserPass($sessionUser, date("n", strtotime($dbdate)), date("Y", strtotime($dbdate)));

	$dbTime = $dbdate . " " . date("H:i:s");

	$sError = "";

	if ($_POST) {

        if (isset($_POST['points_submit'])) {

            $uid = $_POST['skater'];
            $curdate = $_POST['sessionDate'];

            $count = 0;

            $oDataModel->ClearSession($curdate);

            if (is_array($_POST['timeSlot'])) {

                foreach ($_POST['timeSlot'] as $key => $value) {

                    $todaysSeconds = strtotime($curdate . " 00:00:00");

                    $sessionStart = $value * 60 + $todaysSeconds;
                    $oDataModel->AddSession($sessionStart, $passThisDay);
                    $count++;

                }

            }

            header("Location: previous.php?date=" . $curdate);
            exit();

        } elseif (isset($_POST['class_submit'])) {

            $uid = $_POST['skater'];
            $oClasses->removeUserFromActiveClasses($uid);
            if (array_key_exists('classToAdd', $_POST) && is_array($_POST['classToAdd'])) {
                foreach($_POST['classToAdd'] as $key => $value) {
                    $oClasses->addUserToClass($uid, $value);
                }
            }
        }

	}

    $aTimes = $oDataModel->GetSchedule($dbdate);
    $aSession = $oDataModel->GetSession($dbdate);
    $aUser = $oDataModel->GetUserData();
    $balance = $oDataModel->GetUserBalance();
    $numClasses = $oDataModel->GetNumberOfClasses();
    $numPassClasses = $oDataModel->GetNumberOfClassesWithPass();
    $deduct = $oDataModel->GetNumberOnePointClasses();
    $deductPass = $oDataModel->GetNumberOnePointClassesWithPass();


include_once("header.php");

?>

		<div class="infoBar">
			<div class="pageTitle">LOG YOUR SESSION</div> 
		</div>
		<div class="mainContent">
		<div class="profile">
			<?php
                $stringCoach = $aUser['role'] == 2 ? " (coach)" : "";
				$nameString = $passThisDay ?
				"<span class='green'>" . $aUser['lname'] . ", " . $aUser['fname'] . $stringCoach . "</span>" :
				"<span>" . $aUser['lname'] . ", " . $aUser['fname'] . $stringCoach . "</span>";
			?>
			<p><?php echo $nameString . "<br/>" . date("M d Y", strtotime($dbdate)) ?></p>
		</div>
		<div style="clear:both"></div>
		<?php echo empty($message) || $message == "" ? "" : "<p class=\"message\">$message</p>"; ?>
		<form action="" method="post" id="points_form">
			<?php echo $sError; ?>
			<input type="hidden" name="skater" id="skater" value="<?php echo $sessionUser ?>">
			<input type="hidden" name="sessionDate" id="sessionDate" value="<?php echo $dbdate ?>">

			<ul id="logSessionList">

			<?php 

			$count = $oDataModel->GetSkaterCount($dbdate);
			$numSessions = 0;
		
					foreach($aTimes as $time) {

						$strTime = $dbdate . " " . $time['start'];
						$seconds = strtotime($strTime);
							
						$aStart = explode(":", $time['start']);
						$aStop = explode(":", $time['stop']);
					
						$iStartMin = (intval($aStart[0]) * 60) + intval($aStart[1]);
						$iEndMin = (intval($aStop[0] * 60)) + intval($aStop[1]);
					

						for ($i = $iStartMin; $i < ($iEndMin - 5); $i += 30) {

                            // skip if not a full 30 minute session
                            if ($iEndMin - $i < 20) continue;

							echo "<li>";

                            //echo date("Y-m-d G:i:s");
							$sessionTime = strtotime($dbdate . " 00:00:00") + $i * 60;
                            $beforeAfterSession = time() - $sessionTime;

							$iHour = floor($i / 60);
							$iMins = round($i - floor($iHour * 60));

							// 24 hour format
							$tmpHr = $iHour;

							if ($iHour > 12) {
								$iHour -= 12;
								$ampm = "p";
							} else if ($iHour == 12) {
								$ampm = "p";
							} else {
								$ampm = "a";
							}

							if ($iMins < 10) { $iMins = "00"; }
					
							echo "<span class=\"gold\">" . $iHour . ":" . $iMins . $ampm . "</span><br/>";

							$sessionOpen = true;
							$num = "<span class=\"numreg\">0</span>";
							if ($tmpHr < 10) $tmpHr = '0' . $tmpHr;
							for ($j = 0; $j < count($count); $j++) {
								$time = $dbdate . " " . $tmpHr . ":" . $iMins . ":00";
								if ($count[$j]['session'] == $time) {
									$num = "<span class=\"numreg\">" . $count[$j]['registered'] . "</span>";
									if ($count[$j]['registered'] >= MAX_SKATERS && $aUser['role'] <= 1 ) {
										$sessionOpen = false;
									}
									break;
								} 
							}

                            if ($balance <= 0 && !$passThisDay) {
                                $sessionOpen = false;
                            }

							if (in_array($i, $aSession)) {
								if ($beforeAfterSession <= 0) {
									if ($sessionOpen) {
										$input = "<input class=\"timeSlot\" type=\"checkbox\" checked name=\"timeSlot[]\" value=\"" . $i . "\">" . $num;
									} else {
										$input = "<input class=\"timeSlot\" type=\"checkbox\" checked name=\"timeSlot[]\" value=\"" . $i . "\">" . $num;
									}
								} else {
									if ($sessionOpen) {
										$input = "<div class=\"sessionCheck\"><span style=\"color:#FFFFFF\">&#10003</span> " . $num . "</div><input class=\"timeSlot\" type=\"hidden\" name=\"timeSlot[]\" value=\"" . $i . "\">";
									} else {
										$input = "<div class=\"sessionCheck\"><span style=\"color:#FFFFFF\">&#10003</span> " . $num . "</div><input class=\"timeSlot\" type=\"hidden\" name=\"timeSlot[]\" value=\"" . $i . "\">";
									}
								}
								$numSessions++;
							} else {
								if ($beforeAfterSession - 1800 <= 0) {
									if ($sessionOpen) {
										$input = "<input class=\"timeSlot\" type=\"checkbox\" name=\"timeSlot[]\" value=\"" . $i . "\">" . $num;
									} else {
										$input = "<div class=\"sessionFull\"><span style=\"color:#FFFFFF\">&#10006</span> " . $num . "</div>";
									}

								} else {
									if ($sessionOpen) {
										$input = "<div class=\"sessionCheck\"><span style=\"color:#FFFFFF\">&#10006</span> " . $num . "</div>";
									} else {
										$input = "<div class=\"sessionFull\"><span style=\"color:#FFFFFF\">&#10006</span> " . $num . "</div>";
									}

								}									

							}
					
							echo $input;

							echo "</li>";
					
						}
					
					}
						
			
			?>

			</ul>
			<div style="clear:both"></div>

			<?php

			$numreg = 0;
			for ($i = 0; $i < count($count); $i++) {
				$numreg += $count[$i]['registered'];
			}

			?>

			<p><span>Signup Date: </span><input type="text" name="date" id="signupDate" style="width:100px" value="<?php echo $dbdate ?>"></p>
			<p>Skater Sessions: <?php echo $numSessions ?> | Total Sessions Today: <?php echo $numreg; ?> | Points from Classes: <?php echo ($numClasses - $numPassClasses) * 2 - $deduct ?></span><span class="green">(<?php echo $numPassClasses * 2 - $deductPass ?>)</span> | Points Balance: <?php echo $balance ?></p>
			<p>Maximum <?php echo MAX_SKATERS ?> skaters per session.</p>

			<?php

            if ($balance <= 0) {
                $balanceMessage = "<p class=\"errorText\">Your points balance is 0, you must purchase more points to register.  
                                            Click <a href=\"https://squareup.com/store/ice-skate-usa\" class=\"userLink\">here</a> to purchase points</p>";
                $sessionOpen = false;
            }
            else if ($balance < 10) {
                $balanceMessage = "<p>Your points balance is below 10, 
                                            Click <a href=\"https://squareup.com/store/ice-skate-usa\" class=\"userLink\">here</a> to purchase points.</p>";
            }
            else {
                $balanceMessage = "";
            }

            echo $balanceMessage;

            ?>
			
            <p>
                <input class="pointButton" name="points_submit" type="submit" value="Enter"></form>
                <a href="/history.php?userid=<?php echo $sessionUser ?>"><button class="pointButton">History</button></a>

            </p>

		<div style="clear:both"></div>

        </div>

    <div class="infoBar">
        <div class="pageTitle">SIGN UP FOR A CLASS</div>
    </div>


    <div class="classDiv">
        <form action="" method="post" id="class_form">
            <input type="hidden" name="skater" id="skater" value="<?php echo $sessionUser ?>">
            <ul>
                <?php
                    $classList = $oClasses->getClasses();
                    $signupClasses = $oClasses->getClassesByUid($sessionUser);
                    $registeredClasses = array();
                    for($i = 0; $i < count($signupClasses); $i++) {
                        $registeredClasses[] = $signupClasses[$i]['id'];
                    }

                    for ($i = 0; $i < count($classList); $i++) {
                        $tmp = $classList[$i];

                        if (strtotime($tmp['start']) < strtotime("last Sunday") - 86400 * 7) continue;
                        $classSize = $oClasses->getClassSize($tmp['id']);

                        $isRegisteredThisClass = in_array($tmp['id'], $registeredClasses);
                        $isBoxChecked = $isRegisteredThisClass ? " checked" : "";
                        $isBoxGold = $isRegisteredThisClass ? " gold" : "";

                        $classDate = $tmp['start'];
                        $passClassDay = $oDataModel->HasUserPass($sessionUser, date("n", strtotime($classDate)), date("Y", strtotime($classDate)));

                        if (($classSize >= $tmp['size'] && !$isRegisteredThisClass) || ($balance <= 0 && !$passClassDay) || (!$isRegisteredThisClass && (time() > strtotime($tmp['end']))) ) {
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

                    }

                ?>
            </ul>
            <div style="clear:both"></div>
            <input class="pointButton" type="submit" name="class_submit" value="Sign Up">
        </form>
        <div style="clear:both"></div>



    </div></div>
		<?php include_once("incl/history.php"); ?>

<?php 

	include_once("footer.php");

?>