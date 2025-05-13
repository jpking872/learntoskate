<?php

    require_once(dirname(__DIR__, 2) . "/env-lts.php");

	$siteroot = "";
	date_default_timezone_set("America/Chicago");
        
    define('MAX_SKATERS', 30);
    define('MAX_POINTS_DAY', 50);

    define('DIRECTOR_EMAIL', 'nicole@skatememorialcity.com');

    define('EMAIL_TEMPLATES', [ 'register' => 40031273,
                                'message' => 40041774
                              ]);

?>