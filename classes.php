<?php

include_once("incl/session.php");
include_once("incl/database.php");
include_once("incl/library.php");
include_once("incl/config.php");
include_once("classes/DataModelC.php");
include_once("classes/ClassesC.php");
include_once("classes/LTS.php");

if (!$sessionUser || $sessionRole != 3) {
    header("Location: /login.php");
}

global $dbconnection;
$oClasses = new LTS($dbconnection);

$aPostVars = $_POST;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($aPostVars['addClassSubmit'])) {

        $day = $aPostVars['classDate'];
        $title = $aPostVars['title'];
        $levels = implode("|", $aPostVars['level']);
        $start = date("Y-m-d H:i:s", strtotime($day . " " . $aPostVars['classStart']));
        $end = date("Y-m-d H:i:s", strtotime($day . " " . $aPostVars['classEnd']));
        $result = $oClasses->AddLTSClass($title, $start, $end, $levels);

    } else if (isset($aPostVars['cancelClassSubmit'])) {

        $classId = $aPostVars['class'];
        $oClasses->CancelClass($classId);

    } else if (isset($aPostVars['deleteClassSubmit'])) {

        $classId = $aPostVars['class'];
        $oClasses->DeleteClass($classId);

    } else {

    }

}

$aActiveClasses = $oClasses->getClasses();
$aLevels = $oClasses->GetLevels();

include_once("header.php");

?>

<div class="infoBar">
    <div class="pageTitle">ACTIVE CLASSES</div>
</div>

<div class="main">

    <?php if (isset($message)) {
        echo "<p>" . $message . "</p>";
    } ?>

    <?php

    for ($i = 0; $i < count($aActiveClasses); $i++) { ?>
        <div class="activeClass">
                 <span class="gold"><?php echo $aActiveClasses[$i]['title'] . " | " .
                         date('l F j g:ia', strtotime($aActiveClasses[$i]['start'])) . " - " . date('g:ia', strtotime($aActiveClasses[$i]['end'])) ?>
                     <?php echo ($tmpSize = $oClasses->getClassSize($aActiveClasses[$i]['id'])) > 0 ? " (" . $tmpSize . ")" : "" ?>
                 </span>
            <p><?php echo $oClasses->getSkatersInClass($aActiveClasses[$i]['id']); ?></p>
        </div>
    <?php } ?>

    <div class="addClass">

        <a href="javascript:void(0)" class="addClassToggle">+Add a Class</a>

        <form action="" method="post" id="add_class_form">

                <p>Date:<br/><input type="text" name="classDate" id="classDate" value="<?php echo date("Y-m-d"); ?>">

                <?php $times = array(
                    "05:00am", "05:15am", "05:30am", "05:45am",
                    "06:00am", "06:15am", "06:30am", "06:45am",
                    "07:00am", "07:15am", "07:30am", "07:45am",
                    "08:00am", "08:15am", "08:30am", "08:45am",
                    "09:00am", "09:15am", "09:30am", "09:45am",
                    "10:00am", "10:15am", "10:30am", "10:45am",
                    "11:00am", "11:15am", "11:30am", "11:45am",
                    "12:00pm", "12:15pm", "12:30pm", "12:45pm",
                    "1:00pm", "1:15pm", "1:30pm", "1:45pm",
                    "2:00pm", "2:15pm", "2:30pm", "2:45pm",
                    "3:00pm", "3:15pm", "3:30pm", "3:45pm",
                    "4:00pm", "4:15pm", "4:30pm", "4:45pm",
                    "5:00pm", "5:15pm", "5:30pm", "5:45pm",
                    "6:00pm", "6:15pm", "6:30pm", "6:45pm",
                    "7:00pm", "7:15pm", "7:30pm", "7:45pm",
                    "8:00pm", "8:15pm", "8:30pm", "8:45pm",
                    "9:00pm", "9:15pm", "9:30pm", "9:45pm"
                ); ?></p>

                <p>Class Title:<br/><input type="text" name="title" class="classTitle"></p>
                <p>Start Time:<br/><select name="classStart" class="classStart">
                        <option value="-1">N/A</option>
                        <?php for ($j = 0; $j < count($times); $j++) { ?>
                            <option value="<?php echo $times[$j] ?>"><?php echo $times[$j] ?></option>
                        <?php } ?>
                    </select></p>
                <p>End Time:<br/><select name="classEnd" class="classEnd">
                        <option value="-1">N/A</option>
                        <?php for ($j = 0; $j < count($times); $j++) { ?>
                            <option value="<?php echo $times[$j] ?>"><?php echo $times[$j] ?></option>
                        <?php } ?>
                    </select>
                </p>
                <p>Level(s):<br/><select name="level[]" class="levelSelect" multiple="multiple">
                        <?php for ($j = 0; $j < count($aLevels); $j++) { ?>
                            <option value="<?php echo $aLevels[$j]['id'] ?>"><?php echo $aLevels[$j]['level'] ?></option>
                        <?php } ?>
                    </select>
                </p>

                <p><input type="submit" name="addClassSubmit" value="Add Class"></p>

            <div style="clear:both"></div>
        </form>
    </div>

    <div class="addClass">

        <a href="javascript:void(0)" class="cancelClassToggle">+Cancel a Class (one time)</a>

        <form action="" method="post" id="cancel_class_form">

            <p><select name="class">
                <?php for ($i = 0; $i < count($aActiveClasses); $i++) {
                    if (strtotime($aActiveClasses[$i]['start']) < time()) continue;
                ?>
                    <option value="<?php echo $aActiveClasses[$i]['id'] ?>"><?php echo $aActiveClasses[$i]['title'] . " " .
                         date('l F j g:ia', strtotime($aActiveClasses[$i]['start'])) . " - " . date('g:ia', strtotime($aActiveClasses[$i]['end'])) ?>
                    </option>
                <?php } ?>
            </select></p>

            <p><input type="submit" name="cancelClassSubmit" value="Cancel Class"></p>

            <div style="clear:both"></div>
        </form>
    </div>

    <div class="addClass">

        <a href="javascript:void(0)" class="deleteClassToggle">+Delete a Class (permanently)</a>

        <form action="" method="post" id="delete_class_form">

            <p><select name="class">
                <?php for ($i = 0; $i < count($aActiveClasses); $i++) {
                    if (strtotime($aActiveClasses[$i]['start']) < time()) continue;
                ?>
                    <option value="<?php echo $aActiveClasses[$i]['id'] ?>"><?php echo $aActiveClasses[$i]['title'] . " " .
                            date('l F j g:ia', strtotime($aActiveClasses[$i]['start'])) . " - " . date('g:ia', strtotime($aActiveClasses[$i]['end'])) ?>
                    </option>
                <?php } ?>
                </select>
            </p>

            <p><input type="submit" name="deleteClassSubmit" value="Delete Class"></p>

            <div style="clear:both"></div>
        </form>
    </div>

    <script type="text/javascript">

    </script>

    <?php

    include_once("footer.php");

    ?>

