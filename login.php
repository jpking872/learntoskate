<?php

	include_once("classes/loginC.php");
	include_once("incl/database.php");
	include_once("classes/DataModelC.php");
	include_once("incl/session.php");
	include_once("incl/config.php");

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $aPostVars = $_POST;
        $loginResponse = Login($aPostVars);

		if (isset($loginResponse) && $loginResponse['status'] === true) {

            header("Location: points.php");
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
            <div class="signupLink">Enter your PIN and last name:</div>
			<?php if (isset($errorMessage)) echo "<p>" . $errorMessage . "</p>" ?>
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
                    Questions?<br/><a class="userLink" href="mailto:admin@skatetothepoint.com">admin@skatetothepoint.com</a>
                </p>
			</form>
		</div>

<?php 

	include_once("footer.php");
?>