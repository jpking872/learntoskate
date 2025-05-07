<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/loginC.php");
	include_once("classes/LTS.php");
    include_once("classes/ClassesC.php");

	if (!$sessionUser || $sessionRole < 1) {
		//header("Location: /login.php");
	}

    $numSkaters = 0;
	include_once("header.php");

    if (isset($_GET['date'])) {
        $LTSDate = $_GET['date'];
    } else {
        $LTSDate = date("Y-m-d");
    }

    $oLTS = new LTS($dbconnection);
    $classes = $oLTS->GetDayOfClasses($LTSDate);

    ?>

		<div class="infoBar">
            <h3 class="gold ltsHeader"><?php echo date("l, F j, Y", strtotime($LTSDate)) ?></h3>
            <span><?php echo date('g:ia', strtotime("+30 seconds")) ?></span>
                <input type="hidden" name="date" id="LTSDate" style="width:100px" value="<?php echo $LTSDate ?>"><span class="calendarIcon">&#128466;</span>
            <?php if (count($classes) == 0) { ?>
                <p>No classes today</p>
            <?php } ?>
		</div>
    <div class="main">

<?php for ($i = 0; $i < count($classes); $i++) {
    $tmpSession = $classes[$i]['session'];
    $strstart = date("g:ia", strtotime($tmpSession['start']));
    $strend = date("g:ia", strtotime($tmpSession['start']) + 30 * 60);
    $currentSessionTitle = $strstart . " to " . $strend;
    $classCount = 0;

    ?>
    <div class="ltsHeaderBar">
        <div class="typeHeader"><?php echo $currentSessionTitle ?></div>
    </div>

    <div class="ltsTable">

        <?php for ($j = 0; $j < count($classes[$i]['classes']); $j++) {
            $tmpClass = $classes[$i]['classes'][$j];
            $tmpNum = $oLTS->getClassSize($tmpClass['id']);
            ?>

            <div class="ltsRow">
                <div class="ltsName"><?php echo $tmpClass['title'] ?></div>
                <div class="ltsSkaters"><?php echo $oLTS->getSkatersInClass($tmpClass['id']) ?><?php echo $tmpNum > 0 ? " <span class=\"gold\">(" . $tmpNum . ")</span>" : "" ?></div>
            </div>

        <?php } ?>

    </div>

    <?php } ?>



<?php

	include_once("footer.php");

?>