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

        header("Location: signup.php");
        exit();

    } else {

        $errorMessage = $loginResponse['data'];

    }

}

include_once("header.php");

?>
    <div class="infoBar">
        <div class="pageTitle">LOGIN TO LEARN TO SKATE</div>
    </div>

    <div id="login_area">
        <p class="infoMessage">Welcome to Learn to Skate to the Point!</p>
        <p class="infoMessage"><a href="https://youtube.com/shorts/6p9IAsChOWQ?feature=shared" target="_blank">&raquo;&raquo;&raquo; Watch this video to learn how to sign up! &laquo;&laquo;&laquo;</a></p>
        <p class="infoMessage">Here are the steps to register for Learn to Skate classes:</p>
        <ol><li>First, submit the registration form and waiver which can be downloaded <a href="https://docs.google.com/forms/d/e/1FAIpQLScSAP63WMAyCIJ8NqKTAASPaRasvPJwhvM4D7t_cLIXR1zfJA/viewform?usp=header" download target="_blank">here</a>.</li>
            <li>Second, <a href="/register.php">register</a> your skater on this website.  Make a note of your pin because you will need it to log in and sign up for classes.</li>
            <li>Third, <a href="https://ice-skate-usa.square.site/" target="_blank">purchase the registration package</a>. This gives you access to Learn to Skate classes at Memorial City and a one year USFSA Learn to Skate membership.</li>
            <li>Finally, <a href="https://ice-skate-usa.square.site/" target="_blank">purchase a skating plan</a>. If you subscribe after July 1, you will be billed monthly on the date that you subscribe.
                The points for your plan will automatically be added to your learntoskatetothepoint.com account.</li>
        </ol>
        <p class="infoMessage">Actually you may do these steps in any order, but you won't be able to login to the site until you've
                completed the steps and your account is approved.</p>
        <p class="loginMessage"><?php echo $errorMessage ?></p>
        <div class="signupLink">Enter your PIN and last name:</div>
        <form id="login_form" method="post" action="">
            <p>
                PIN:<br/><input type="password" id="pinInput" name="pin" size="6" maxlength="5"><br/>
                Skater last name:<br/><input type="text" id="nameInput" name="last" size="15" maxlength="25"><br/>
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
                    errorText += "Last name is required.<br/> ";
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