<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/loginC.php");
	include_once("classes/LTS.php");	include_once("classes/ClassesC.php");

	if (!$sessionUser || $sessionRole < 1) {
		//header("Location: /login.php");
	}

    $numSkaters = 0;
	include_once("header.php");

    $oLTS = new LTS($dbconnection);
    $classes = $oLTS->GetTodaysClasses();

    if(count($classes) > 0) {
    $currentSession = "";
    for ($i = 0; $i < count($classes); $i++) {
        $strstart = date("Y-m-d") . " " . $classes[$i]['session']['start'];
        $strend = date("Y-m-d"). " " . $classes[$i]['session']['end'];
        $currentSession .= date("g:ia", strtotime($strstart)) . "-" . date("g:ia", strtotime($strend)) . " ";
    }

    } else { ?>

        <div class="infoBar">
            <span class="gold"><?php echo date("l, F j, Y") ?></span> <?php echo "No classes today" ?>
            <span class="gold" id="totalSkaters">0</span> <span id="skaterText">skaters</span>
            <span class="gold"><?php echo date('g:ia', strtotime("+30 seconds")) ?></span>
        </div>

    <?php

    	include_once("footer.php");
        return;
    }
    ?>

		<div class="infoBar">
            <h3 class="gold lato-bold ltsHeader"><?php echo date("l, F j, Y") ?></h3>
            <span><?php echo date('g:ia', strtotime("+30 seconds")) ?></span>
		</div>
    <div class="main">

<?php for ($i = 0; $i < count($classes); $i++) {
    $tmpSession = $classes[$i]['session'];

    $strstart = date("Y-m-d") . " " . $tmpSession['start'];
    $strend = date("Y-m-d"). " " . $tmpSession['end'];
    $currentSessionTitle = date("g:ia", strtotime($strstart)) . " to " . date("g:ia", strtotime($strend)) . " (" . $numSkaters . ")";

    ?>
    <div class="ltsHeaderBar">
        <div class="typeHeader"><?php echo $currentSessionTitle; ?></div>
    </div>

    <div class="ltsTable">

        <?php for ($j = 0; $j < count($classes[$i]['classes']); $j++) {
            $tmpClass = $classes[$i]['classes'][$j]; ?>

            <div class="ltsRow">
                <div class="ltsName"><?php echo $tmpClass['title'] ?></div>
                <div class="ltsSkaters"><?php //echo $oLTS->getSkatersInClass($tmpClass['id']) ?>
                    <?php echo $j % 3 != 0 ? "<p>King,Jo., <span class=\"green\">Murdock,La.</span></p>" : "<p>King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>, King,Jo., <span class=\"green\">Murdock,La.</span>,King,Jo., <span class=\"green\">Murdock,La.</span>,King,Jo., <span class=\"green\">Murdock,La.</span>,King,Jo., <span class=\"green\">Murdock,La.</span></p>" ?>

                </div>
            </div>

        <?php } ?>

    </div>

    <?php } ?>



<?php

	include_once("footer.php");

?>