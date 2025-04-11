<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
    include_once("classes/DataModelC.php");

	if (!$sessionUser || $sessionRole != 3) {
		header("Location: /login.php");
	}

    $message = "";

	if ($_POST) {

			$sDate = date("Y-m-d", strtotime($_POST['date']));

            $oDataModel = new DataModel(0, $dbconnection);

            if (time() > strtotime($sDate)) {
                $message = "<p>Cannot edit schedule in past</p>";
            } else {

			$sql = "DELETE FROM `schedule` WHERE `date` = '" . mysqli_real_escape_string($dbconnection, $sDate) . "'";
			$result = mysqli_query($dbconnection, $sql);

            if (strtotime($sDate) >= strtotime("tomorrow") && isset($_POST['deleteReg']) && $_POST['deleteReg'] == 1) {
                $sql2 = "DELETE FROM `points` WHERE DATE(`session`) = '" . mysqli_real_escape_string($dbconnection, $sDate) . "' LIMIT 500";
                $result2 = mysqli_query($dbconnection, $sql2);
            }

			for ($i = 1; $i <= 6; $i++) {

                if ($_POST['starttime'][$i] == -1 || $_POST['endtime'][$i] == -1) continue;

                $start = $_POST['starttime'][$i];
                $end = $_POST['endtime'][$i];

                $sql2 = "INSERT INTO `schedule` ( `date`, `start`, `stop`, `entered`) VALUES ( 
				'" . mysqli_real_escape_string($dbconnection, $sDate) . "',
				'" . mysqli_real_escape_string($dbconnection, $start) . "',
				'" . mysqli_real_escape_string($dbconnection, $end) . "',
				NOW())";
                $result2 = mysqli_query($dbconnection, $sql2);

			}
            }

	}



	include_once("header.php");

?>

		<div class="infoBar">
			<div class="pageTitle">SET SCHEDULE</div> 
		</div>
		<form action="" method="post" id="schedule_form">
            <?php echo $message; ?>
			<div class="dateDiv">
                <div id="scheduleDate"></div>
                <input type="hidden" name="date" class="scheduleDate" value="<?php echo date("Y-m-d"); ?>">
			</div>
			<div class="timesDiv">
                <h3 class="selectedDate"><?php echo date("F j, Y") ?></h3>

                <?php $times = array(
                        "05:00:00", "05:15:00", "05:30:00", "05:45:00",
                        "06:00:00", "06:15:00", "06:30:00", "06:45:00",
                        "07:00:00", "07:15:00", "07:30:00", "07:45:00",
                        "08:00:00", "08:15:00", "08:30:00", "08:45:00",
                        "09:00:00", "09:15:00", "09:30:00", "09:45:00",
                        "10:00:00", "10:15:00", "10:30:00", "10:45:00",
                        "11:00:00", "11:15:00", "11:30:00", "11:45:00",
                        "12:00:00", "12:15:00", "12:30:00", "12:45:00",
                        "13:00:00", "13:15:00", "13:30:00", "13:45:00",
                        "14:00:00", "14:15:00", "14:30:00", "14:45:00",
                        "15:00:00", "15:15:00", "15:30:00", "15:45:00",
                        "16:00:00", "16:15:00", "16:30:00", "16:45:00",
                        "17:00:00", "17:15:00", "17:30:00", "17:45:00",
                        "18:00:00", "18:15:00", "18:30:00", "18:45:00",
                        "19:00:00", "19:15:00", "19:30:00", "19:45:00",
                        "20:00:00", "20:15:00", "20:30:00", "20:45:00",
                        "21:00:00", "21:15:00", "21:30:00", "21:45:00"


                ); ?>
				
				<?php for ($i = 1; $i <= 6; $i++) { ?>

				<p>Time <?php echo $i ?>:
                    <select name="starttime[<?php echo $i ?>]">
                        <option value="-1">N/A</option>
                        <?php for ($j = 0; $j < count($times); $j++) { ?>
                            <option value="<?php echo $times[$j] ?>"><?php echo $times[$j] ?></option>
                        <?php } ?>
                    </select>
                    <select name="endtime[<?php echo $i ?>]">
                        <option value="-1">N/A</option>
                        <?php for ($j = 0; $j < count($times); $j++) { ?>
                            <option value="<?php echo $times[$j] ?>"><?php echo $times[$j] ?></option>
                        <?php } ?>
                    </select>
				</p>
                <?php } ?>
                <p><input type="submit" value="Enter schedule"></p>
                <p><input type="checkbox" name="deleteReg" class="deleteReg" value="1"> Delete Previous Registrations</p>
			</div>
			<div style="clear:both"></div>
			<div class="scheduleDiv">
				<table class="scheduleTable" cellspacing="2" cellpadding="1" width="500">
					<thead>
						<tr>
							<td width="150">Date</td>
							<td width="150">Start</td>
							<td width="150">Stop</td>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
        </form>
		</div>

<?php 

	include_once("footer.php");

?>