<?php

include_once("classes/loginC.php");
include_once("incl/database.php");
include_once("classes/DataModelC.php");
include_once("incl/session.php");
include_once("incl/config.php");

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $aPostVars = $_POST;
    $loginResponse = Login($aPostVars);

    if (isset($loginResponse) && $loginResponse['status'] === true) {

        header("Location: today.php");
        exit();

    } else {

        $errorMessage = $loginResponse['data'];

    }

}

include_once("header.php");

?>
    <div class="infoBar">
        <div class="pageTitle">LOGIN TO POINTS MONITOR</div>
    </div>

    <div id="login_area">
        <p class="loginMessage"><?php echo $errorMessage ?></p>
        <div class="signupLink">Enter your PIN and last name:</div>
        <form id="login_form" method="post" action="">
            <p>
                PIN:<br/><input type="password" id="pinInput" name="pin" size="6" maxlength="5"><br/>
                Last name:<br/><input type="text" id="nameInput" name="last" size="15" maxlength="25"><br/>
            </p>
            <div class="adminWrapper">
                Enter Admin Password:<br/>
                <input id="adminPass" type="password" name="adminPass" maxlength="16" size="15">
            </div>
            <p>
                <input id="pinSubmit" type="submit" name="submit" value="Submit">
            </p>
            <div style="clear:both"></div>
            <p class="signupLink">
                New skater?<br/><a class="userLink" href="/register.php">Create an account</a>
            </p>
            <p class="signupLink">
                Questions?<br/><a class="userLink" href="mailto:skatetothepoint@gmail.com">skatetothepoint@gmail.com</a>
            </p>
        </form>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#login_form").submit(function (e) {
                var errorText = "";
                if ($("input[name='last']").val().length < 2 || $("input[name='last']").val().length > 50) {
                    errorText += "Skater last name is required.<br/> ";
                }
                if ($(".adminWrapper").is(":visible")) {
                    if ($("input[name='adminPass']").val().length < 8 || $("input[name='adminPass']").val().length > 16) {
                        errorText += "Valid password is required.<br/> ";
                    }
                }
                var pin = new RegExp('^[A-Z,a-z,0-9]{5}$');
                if (!pin.test($("input[name='pin']").val())) {
                    errorText += "Pin must be 5 letters or numbers.<br/> ";
                }
                if (errorText.length > 0) {
                    $(".loginMessage").html(errorText);
                    return false;
                } else {
                    return true;
                }
            })
        })
    </script>

<?php

include_once("footer.php");
?>