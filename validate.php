<?php

include_once("incl/session.php");
include_once("incl/database.php");
include_once("incl/library.php");
include_once("incl/config.php");
include_once("classes/DataModelC.php");
include_once("classes/EmailC.php");

if (!$sessionUser || $sessionRole != 3) {
    header("Location: /login.php");
}

global $dbconnection;
$oEmail = new Email($dbconnection);
$oData = new DataModel(0, $dbconnection);

$aPostVars = $_POST;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $aPostVars = $_POST;
    $oData->SetUser($aPostVars['uid']);
    $userData = $oData->GetUserData();
    if(isset($aPostVars['approveSubmit'])) {

        $approved = $oData->ApproveAccount($aPostVars['uid']);
        //$emailed = $oEmail->SendApproval($userData);
        $emailed = true;

        if ($approved && $emailed) {
            $message = "Account approved.";
        } else {
            $message = "Could not approve account.";
        }

    } elseif (isset($aPostVars['denySubmit'])) {

        $denied = $oData->DenyAccount($aPostVars['uid']);
        //$emailed = $oEmail->SendDenial($userData);
        $emailed = true;

        if ($denied && $emailed) {
            $message = "Denied account.";
        } else {
            $message = "Could not deny account.";
        }

    } else {

    }

}

include_once("header.php");

?>

<div class="infoBar">
    <div class="pageTitle">VALIDATE NEW ACCOUNTS</div>
</div>

<div class="main">

    <div class="activeClass">

    <?php if (strlen($message) > 0) {
        echo "<p>" . $message . "</p>";
    } ?>

    <?php

        $oData = new DataModel(0, $dbconnection);

        $newAccounts = $oData->GetNewAccounts();

        if(count($newAccounts) == 0) {
            echo "<p>There are no new accounts.</p>";
        }

        ?>

        <?php for ($i = 0; $i < count($newAccounts); $i++) {
            $tmp = $newAccounts[$i];

            ?>

            <form class="approveAccount" method="post">
                <p>Name: <?php echo $tmp['fname'] . " " . $tmp['lname'] ?><br/>
                    Email: <?php echo $tmp['email'] ?></p>
                <input type="hidden" name="uid" value="<?php echo $tmp['id'] ?>">
                <input type="submit" name="approveSubmit" value="Approve">
                <input type="submit" name="denySubmit" value="Deny">
            </form>


        <?php } ?>

    </div>

    <script type="text/javascript">

    </script>

    <?php

    include_once("footer.php");

    ?>

