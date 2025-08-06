<?php
include_once("incl/database.php");
function GetUserIdFromNameAndPin($skaterName, $skaterPin) {

    global $dbconnection;

    $nameArray = explode(" ", $skaterName);
    $skaterFirst = $nameArray[0];
    $skaterLast = "";
    for ($i = 1; $i < count($nameArray); $i++) {
        $skaterLast .= $nameArray[$i] . " ";
    }
    rtrim($skaterLast);
    $skaterPin = trim($skaterPin);

    $sql = "SELECT * FROM `users` WHERE `sfname` = '" . mysqli_real_escape_string($dbconnection, $skaterFirst) . "'
            AND `slname` = '" . mysqli_real_escape_string($dbconnection, $skaterLast) . "' ORDER BY `id` DESC LIMIT 1";
    $result = mysqli_query($dbconnection, $sql);

    $numReturned = mysqli_num_rows($result);

    if ($numReturned === 1) {
        $skater = mysqli_fetch_assoc($result);
        var_dump($skater);
        return ['id' => $skater['id'], 'pin' => $skater['pin']];
    }

    $sql = "SELECT * FROM `users` WHERE `pin` = '" . mysqli_real_escape_string($dbconnection, $skaterPin) . "' ORDER BY `id` DESC LIMIT 1";
    $result = mysqli_query($dbconnection, $sql);

    if ($row = mysqli_fetch_array($result)) {
        $skaterFirstNames = explode(" ", $row['sfname']);
        $skaterLastNames = explode(" ", $row['slname']);
        $parentFirstNames = explode(" ", $row['fname']);
        $parentLastNames = explode(" ", $row['lname']);
        $allNames = array_merge($skaterFirstNames, $skaterLastNames, $parentFirstNames, $parentLastNames);
        var_dump($allNames);

        for ($j = 0; $j < count($allNames); $j++) {
            if (strlen($allNames[$j]) >= 3 && $allNames[$j] != strtolower("n/a")) {
                if (stristr($skaterName, $allNames[$j])) {
                    $userId = $row['id'];
                    return ['id' => $row['id'], 'pin' => $row['pin']];
                }
            }
        }
    }
    return false;

}

$userData = GetUserIdFromNameAndPin("Alberto Govela Martinez", "64656");
var_dump($userData);