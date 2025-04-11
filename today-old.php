<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");
	include_once("classes/loginC.php");
	include_once("classes/ClassesC.php");

	if (!$sessionUser || $sessionRole < 1) {
		header("Location: /login.php");
	}

	$numSessions = isset($_GET['sessions']) ? $_GET['sessions'] : 0;

	$isLogout = isset($_GET['logout']) && $_GET['logout'] == "yes" ? true : false;
	if ($isLogout) { 

		LogoutHidden(); 
		$sessionRole = 0;

	}

	include_once("header.php");

	$previousDate = date("Y-m-d");

	//Get current session time
	$oData = new DataModel(0, $dbconnection);
	$aSched = $oData->GetSchedule($previousDate);

	$currentSession = "";
    $sessionBreaks = [];

	foreach ($aSched as $sched) {

		$strstart = date("Y-m-d") . " " . $sched['start'];
		$strend = date("Y-m-d"). " " . $sched['stop'];

        $sessionBreaks[] = date("g:ia", strtotime($strend) + 450);

		$currentSession .= date("g:ia", strtotime($strstart)) . "-" . date("g:ia", strtotime($strend)) . " ";
	}

?>

		<?php if ($isLogout) { ?>

		<div class="infoBar barMargin">
			<div class="confirmation"><?php echo $numSessions . " sessions registered." ?></div>
		</div>

		<?php } ?>

		<div class="infoBar">
            <span class="gold"><?php echo date("F j, Y") ?></span> <?php echo $currentSession ?>
            <span class="gold" id="totalSkaters">0</span> <span id="skaterText">skaters</span>
		</div>
		<div class="main">
		<div class="headerBar">
            <h3 class="typeHeader lato-bold">Freestyle Sessions</h3>
		</div>
		<div class="skaterContentAreaPrevious">

				<?php 

					$oData = new DataModel(0, $dbconnection);
					$aSkaters = $oData->GetTodaysSkaters($previousDate);

					$onIceSkaters = array();
					$aSkaterTable = array();

					foreach ($aSkaters as $key => $value) {

						for ($i = 0; $i < count($value); $i++) {

							$onIceSkaters[] = array("uid" => $key, "order" => $value[$i]['order']);
							break;

						}
					}


					usort($onIceSkaters, 'cmp');

					function cmp($a, $b) {

						if ($a['order'] == $b['order']) {
        					return 0;
    					}

    					return ($a['order'] < $b['order']) ? -1 : 1;
					}

                    $coachCount = 0;
					for ($i = 0; $i < count($onIceSkaters); $i++) {

						$uid = $onIceSkaters[$i]['uid'];

						$sessionString = "";

						for ($j = 0; $j < count($aSkaters[$uid]); $j++) {

							if ($j == 0) {

                                $isCoach = $aSkaters[$uid][$j]['role'] == 2;
                                if ($isCoach) $coachCount++;
								$name = $aSkaters[$uid][$j]['name'];
							}

							$session = date("g:i", $aSkaters[$uid][$j]['session']);
							$ampm = date("a", $aSkaters[$uid][$j]['session']);
							$hasPass = $aSkaters[$uid][$j]['pass'];

							if ($j == 0) {
                                if ($hasPass) {
                                    $sessionString .= "<span class=\"green\">";
                                } else if ($isCoach) {
                                    $sessionString .= "<span class=\"gold\">";
                                } else if ($ampm == "pm") {
                                    $sessionString .= "<span>";
                                } else {
                                    $sessionString .= "<span>";
                                }
							}

                            $sessionSeparator = ",";
                            if ($j + 1 < count($aSkaters[$uid])) {
                                $nextSession = date("g:i", $aSkaters[$uid][$j + 1]['session']);
                                $ampmNext = date("a", $aSkaters[$uid][$j + 1]['session']);
                                for ($k = 0; $k < count($sessionBreaks); $k++) {
                                    if (strtotime($session . $ampm) < strtotime($sessionBreaks[$k]) && strtotime($nextSession . $ampmNext) > strtotime($sessionBreaks[$k])) {
                                        $sessionSeparator = " ";
                                    }
                                }
                            }
							$sessionString .= $session . $sessionSeparator;

							if ($j == count($aSkaters[$uid]) - 1) {
								$sessionString = substr($sessionString, 0, -1);
                                $sessionString .= "</span>";
							}

						}

						$aSkaterTable[] = array('name' => $name, 'session' => $sessionString);
		

					}

					$numSkaters = count($aSkaterTable);

				?>

		<div class="skatersSinglePrevious">

			<?php

				echo "<table id=\"skaterSingleTable\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"100%\">";

				for ($i = 0; $i < $numSkaters; $i++) {

						$name = empty($aSkaterTable[$i]) ? "" : $aSkaterTable[$i]['name'];
						$session = empty($aSkaterTable[$i]) ? "" : $aSkaterTable[$i]['session'];

						if ($name == "" && $i == 0) {
							$name = "No skaters";
						}

						echo "<tr><td class=\"skater\" width=\"35%\">" . $name . "</td><td class=\"time\" width=\"65%\">" . $session . "</td></tr>";

				} 

				echo "</table>";

            $oClasses = new Classes($dbconnection);
            $daysClasses = $oClasses->getDaysClasses($previousDate);

            if(count($daysClasses) > 0) { ?>

            <div class="headerBar">
                <h3 class="typeHeader lato-bold">Classes</h3>
            </div>
            <table id="classesTable" cellpadding="2" cellspacing="1" border="0" width="100%">

                <?php for ($i = 0; $i < count($daysClasses); $i++) { ?>
                <tr><td class="skater" width="35%"><?php echo $daysClasses[$i]['title']?><br/><?php echo date('g:ia', strtotime($daysClasses[$i]['start'])) . " to " . date('g:ia', strtotime($daysClasses[$i]['end'])) ?></td>
                    <td class="time" width="65%"><?php echo $oClasses->getSkatersInClass($daysClasses[$i]['id']) ?></td></tr>
                <?php } ?>
            </table>

            <?php } ?>

		</div>
		<div class="sidebar">

		</div>
		</div>

		<script type="text/javascript">

		var numberOfSkaters = "<?php echo $numSkaters - $coachCount ?>";

		</script>

<?php

	include_once("footer.php");

?>