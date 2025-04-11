<?php

include_once("incl/session.php");
include_once("incl/database.php");
include_once("incl/library.php");
include_once("incl/config.php");
include_once("classes/SkatersC.php");
include_once("classes/ClassesC.php");

include_once("header.php");


$currentSession = "";
$sessionBreaks = [];

foreach ($aSched as $sched) {

    $strstart = date("Y-m-d") . " " . $sched['start'];
    $strend = date("Y-m-d"). " " . $sched['stop'];
    $currentSession .= date("g:ia", strtotime($strstart)) . "-" . date("g:ia", strtotime($strend)) . " ";
    $sessionBreaks[] = date("g:ia", strtotime($strend) + 450);
}

?>
<div style="clear:both"></div>
    <div class="infoBar">
        <span class="gold"><?php echo date("F j, Y") ?></span> <?php echo $currentSession ?>
        <span class="gold" id="totalSkaters">0</span> <span id="skaterText">skaters</span>
        <span class="gold"><?php echo date('g:ia', strtotime("+30 seconds")) ?></span>
    </div>

    <div class="main">
    <div class="headerBar">
        <h3 class="typeHeader lato-bold">Freestyle Sessions</h3>
    </div>
    <div class="skaterContentArea">
            <?php

            $oData = new DataModel(0, $dbconnection);
            $aSkaters = $oData->GetTodaysSkaters();

            $seconds = time();
            $onIceSkaters = array();
            $aSkaterTable = array();

            foreach ($aSkaters as $key => $value) {

                for ($i = 0; $i < count($value); $i++) {

                    if (($seconds > ($value[$i]['session'])) && ($seconds < ($value[$i]['session'] + 1800))) {
                        $onIceSkaters[] = array("uid" => $key, "order" => $value[0]['order']);
                        break;
                    }
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

                    $hasPass = $aSkaters[$uid][$j]['pass'];
                    $session = date("g:i", $aSkaters[$uid][$j]['session']);
                    $ampm = date("a", $aSkaters[$uid][$j]['session']);

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

        <div class="skatersSingle">

            <?php

            echo "<table id=\"skaterSingleTable\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"100%\">";

            $showNumber = $numSkaters > 14 ? $numSkaters : 14;

            for ($i = 0; $i < $showNumber; $i++) {

                $name = empty($aSkaterTable[$i]) ? "" : $aSkaterTable[$i]['name'];
                $session = empty($aSkaterTable[$i]) ? "" : $aSkaterTable[$i]['session'];

                if ($name == "" && $i == 0) {
                    $name = "No current skaters";
                }

                echo "<tr><td class=\"skater\" width=\"35%\">" . $name . "</td><td class=\"time\" width=\"65%\">" . $session . "</td></tr>";

            }

            echo "</table>";

            ?>

            <?php

                $oClasses = new Classes($dbconnection);
                $currentClasses = $oClasses->getCurrentClasses();

                if(count($currentClasses) > 0) { ?>

                    <div class="headerBar">
                        <h3 class="typeHeader lato-bold">Classes</h3>
                    </div>


            <table id="classesTable" cellpadding="2" cellspacing="1" border="0" width="100%">

                <?php for ($i = 0; $i < count($currentClasses); $i++) { ?>
                <tr><td class="skater" width="35%"><?php echo $currentClasses[$i]['title']?><br/><?php echo date('g:ia', strtotime($currentClasses[$i]['start'])) . " to " . date('g:ia', strtotime($currentClasses[$i]['end'])) ?></td>
                    <td class="time" width="65%"><?php echo $oClasses->getSkatersInClass($currentClasses[$i]['id']) ?></td></tr>
                <?php } ?>
            </table>

            <?php } ?>
        </div>

        <div class="sidebar">
            <div class="schedule">
                <h3 class="gold lato-bold">Calendar</h3>
                <ul>
                    <li><span class="gold">Sun 3/22</span> no freestyle</li>
                    <li><span class="gold">Mon 3/23</span> 5:30pm-9:45pm</li>
                    <li><span class="gold">Tue 3/24</span> 5:30pm-9:45pm</li>
                    <li><span class="gold">Wed 3/25</span> 5:30pm-9:45pm</li>
                    <li><span class="gold">Thu 3/26</span> 5:30pm-9:45pm</li>
                    <li><span class="gold">Fri 3/27</span> 5:30pm-9:45pm</li>
                    <li><span class="gold">Sat 3/28</span> 6:00am-8:00am</li>
                </ul>
            </div>
        </div>
    </div>

    <script type="text/javascript">

        var numberOfSkaters = "<?php echo $numSkaters - $coachCount ?>";

        var currentTimeObject = new Date();

        var currentHours = currentTimeObject.getHours();
        var currentMinutes = currentTimeObject.getMinutes();

    </script>

    <script type="text/javascript">

            setTimeout( function () {

                window.location.href = "/";

            }, 30000)


    </script>


<?php

include_once("footer.php");

?>