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

    if (isset($_GET['userid']) && ($_GET['userid'] == $sessionUser || $sessionRole == 3)) {
        $uid = $_GET['userid'];
        $validRequest = true;
    } else {
        $validRequest = false;
    }

	if (isset($_GET['page'])) {
		$pageno = $_GET['page'];
	} else {
		$pageno = 1;
	}

	if ($validRequest) {

        global $dbconnection;

		$oData = new DataModel($uid, $dbconnection);
		$oClasses = new Classes($dbconnection);

		$aResult = array();
		$aResult['points'] = $oData->GetPointsForUser();
		$aResult['monthpoints'] = $oData->GetPointsByMonth(0);
		$aResult['totalpoints'][0] = $oData->GetRecentPointsForUser(0);
		$aResult['totalpoints'][1] = $oData->GetRecentPointsForUser(1);
		$aResult['purchases'] = $oData->GetPurchasesForUser();
		$aResult['totalpurchases'] = $oData->GetTotalPurchasesForUser();
		$aResult['totalpayments'] = $oData->GetTotalPaymentsForUser();
		$aResult['userinfo'] = $oData->GetUserData();
        $aResult['classes'] = $oClasses->getClassesByUid($uid);
        $totalClasses = $oData->GetNumberOfClasses();
        $totalPassClasses = $oData->GetNumberOfClassesWithPass();
		$balance = $oData->GetUserBalance();
        $deduct = $oData->GetNumberOnePointClasses();
        $deductPass = $oData->GetNumberOnePointClassesWithPass();

	}

    $expandable = count($aResult['monthpoints']) > 20 || count($aResult['purchases']) > 20 || count($aResult['classes']) > 20;

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postVars = $_POST;
        if (isset($postVars['submitNote'])) {
            $uid = $postVars['skaterId'];
            $admin = $sessionUser;
            $note = $postVars['note'];
            $result = $oData->AppendNote($note, $uid, $admin);
        }
    }

	include_once("header.php");

?>

		<div class="infoBar">
			<div class="pageTitle">SKATER HISTORY</div> 
		</div>

    <?php if (!$validRequest) {
        echo "<p class=\"message\">Invalid Request</p>";
    } else { ?>
		<div class="historyDiv">
			<h2>Skater Name: <span class="skaterName"><?php echo $aResult['userinfo']['fname'] . " " . $aResult['userinfo']['lname']?></span></h2>

			<div class="pointsDiv">
				<h4>Summary of Points</h4>
				<table class="pointsTable" width="325" cellspacing="2" cellpadding="1">
					<thead>
						<tr>
							<td width="175">Date</td>
							<td width="125"># Sessions</td>
						</tr>
					</thead>
					<tbody>
						<?php 

						$i = 0;
						foreach ($aResult['points'] as $aPointRow) {
							$i++;
							if ($i % 2 == 0) {
								$rowClass = " class=\"evenRow2\"";
							} else {
								$rowClass = "";
							}

							$sNumber = $aPointRow['pass'] ? "<span class=\"green\">" . $aPointRow['num'] . "</span>" : $aPointRow['num'];

							echo "<tr$rowClass><td>" . $aPointRow['date'] . "</td><td>" . $sNumber . "</td></tr>\n";

						} 

						?>
					</tbody>
				</table>
				<h4>Summary of Points By Month</h4>
				<table class="pointsTable" width="325" cellspacing="2" cellpadding="1">
					<thead>
						<tr>
							<td width="175">Date</td>
							<td width="125"># Sessions</td>
						</tr>
					</thead>
					<tbody>
						<?php 

						$i = 0;
						foreach ($aResult['monthpoints'] as $aPointRow) {
							$i++;
                            if ($i % 2 == 0) {
                                if ($i >= 20) {
                                    $rowClass = " class=\"evenRow2 showHideRow\"";
                                } else {
                                    $rowClass = " class=\"evenRow2\"";
                                }
                            } else {
                                if ($i >= 20) {
                                    $rowClass = " class=\"showHideRow\"";
                                } else {
                                    $rowClass = "";
                                }
                            }

							echo "<tr$rowClass><td>" . $aPointRow['date'] . "</td><td>" . $aPointRow['num'] . "</td></tr>\n";

						} 

						?>
					</tbody>
				</table>
			</div>
			<div class="purchaseDiv">
				<h4>Summary of Purchases</h4>
					<table class="paymentTable" width="325" cellspacing="2" cellpadding="1">
					<thead>
						<tr>
							<td width="125">Date</td>
							<td width="75">Points</td>
							<td width="75">Pass</td>
							<td width="100">Price</td>
						</tr>
					</thead>
					<tbody>
						
						<?php 

						$i = 0;
						foreach ($aResult['purchases'] as $aPurchaseRow) {
							$i++;
							if ($i % 2 == 0) {
                                if ($i >= 20) {
                                    $rowClass = " class=\"evenRow2 showHideRow\"";
                                } else {
                                    $rowClass = " class=\"evenRow2\"";
                                }
							} else {
                                if ($i >= 20) {
                                    $rowClass = " class=\"showHideRow\"";
                                } else {
                                    $rowClass = "";
                                }
							}

							echo "<tr$rowClass><td>" . $aPurchaseRow['date'] . "</td><td>" . $aPurchaseRow['points'] . "</td><td>" . $aPurchaseRow['pass'] . "</td><td>" . $aPurchaseRow['price'] . "</td></tr>\n";

						} 

						?>

					</tbody>
				</table>
			</div>
            <div class="classesDiv">
                <h4>Summary of Classes</h4>
                <table class="paymentTable" width="325" cellspacing="2" cellpadding="1">
                    <thead>
                    <tr>
                        <td width="125">Date</td>
                        <td width="200">Class</td>
                    </tr>
                    </thead>
                    <tbody>

                    <?php

                    for ($i = 0; $i < count($aResult['classes']); $i++) {
                        $tmp = $aResult['classes'][$i];
                        if ($i % 2 == 1) {
                            if ($i >= 20) {
                                $rowClass = " class=\"evenRow2 showHideRow\"";
                            } else {
                                $rowClass = " class=\"evenRow2\"";
                            }
                        } else {
                            if ($i >= 20) {
                                $rowClass = " class=\"showHideRow\"";
                            } else {
                                $rowClass = "";
                            }
                        }

                        if (strtotime($tmp['start']) < strtotime("2025-02-01 00:00:00")) {
                            $onePointClass = "(1pt)";
                        } else {
                            $onePointClass = "";
                        }

                        $titleText = $tmp['pass'] == 1 ? "<span class=\"green\">" . $tmp['title'] . $onePointClass . "</span>" : $tmp['title'] . $onePointClass;

                        echo "<tr$rowClass><td>" . date("M j, Y", strtotime($tmp['start'])) . "</td>
                            <td>" . $titleText . "</td></tr>\n";

                    }

                    ?>

                    </tbody>
                </table>
            </div>
			<div style="clear:both"></div>

			<div class="summary">
				Total points used: <span id="totalPoints"><?php echo $aResult['totalpoints'][0] . "<span class=\"green\">(" . $aResult['totalpoints'][1] . ")</span>";  ?></span> |
				Total points purchased: <span id="totalPurchases"><?php echo $aResult['totalpurchases'] ?></span> |
				Points from  Classes: <span id="totalClasses"><?php echo ($totalClasses - $totalPassClasses) * 2 - $deduct ?></span><span class="green">(<?php echo $totalPassClasses * 2 - $deductPass ?>)</span> |
				Point Balance:

						<?php 

							//$pointsBal = $aResult['totalpurchases'] - $aResult['totalpoints'];
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
            <?php if (isset($sessionUser) && $sessionRole == 3) { ?>
                <div class="historyNotes">
                    <h4>Notes</h4>
                    <?php $userNotes = $oData->GetNotes($uid);
                        if (count($userNotes) > 10) $expandable = true;
                        for($i = 0; $i < count($userNotes); $i++) {
                            if ($i % 2 == 1) {
                                if ($i >= 10) {
                                    $rowClass = " evenRow2 showHideRow";
                                } else {
                                    $rowClass = " evenRow2";
                                }
                            } else {
                                if ($i >= 10) {
                                    $rowClass = " showHideRow";
                                } else {
                                    $rowClass = "";
                                }
                            }
                            echo "<p class=\"singleNote" . $rowClass . "\">" . date("M j, Y, g:i a", strtotime($userNotes[$i]['entered'])) . ": " . mysqli_real_escape_string($dbconnection, $userNotes[$i]["note"]) . "</p>";
                        }
                    ?>
                    <p><a href="javascript:void(0)" class="addNoteToggle gold">+ Add a note</a></p>
                        <form id="addNote" method="post" action="">
                            <input type="hidden" name="skaterId" value="<?php echo $uid ?>">
                            <input type="text" maxlength="500" name="note"> <input type="submit" name="submitNote" class="noteButton" value="Add Note">
                        </form>

                </div>
            <?php } ?>
            <?php
            if(isset($_SERVER['HTTP_REFERER'])) {
                $goBackUrl = strstr($_SERVER['HTTP_REFERER'], 'user') ? "/user.php" : "/points.php" ?>
                <div class="goback"><?php if ($expandable) { ?><a href="javascript:void(0)" class="toggleShow">&laquo; show all</a> | <?php } ?><a href="<?php echo $goBackUrl ?>">&laquo; return</a></div>
            <?php } ?>
		</div>



		<?php } ?>

		</div>

<?php 

	include_once("footer.php");

?>