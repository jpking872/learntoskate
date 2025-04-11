<?php

	Class DataModel {

		private $userid;

		function __construct($uid = 0, $con = null) {
       		
       	$this->userid = $uid;
        $this->con = $con;

   	}

      function SetUser($uid) {

         $this->userid = $uid;
         
      }

      function GetLastInsertId() {
            return mysqli_insert_id($this->con);
      }

		function GetPointsForUser() {

         $sql = "SELECT DATE(`session`) as `datesess`, `pass`, COUNT(*) as `numsess` FROM `points` WHERE `uid` = '" . 
         mysqli_real_escape_string($this->con, $this->userid) . "' AND DATE_SUB(CURDATE(), INTERVAL 30 DAY) < DATE(`session`) GROUP BY DATE(`session`) ORDER BY DATE(`session`) DESC";

			$result = mysqli_query($this->con, $sql);

			$aResults = array();
            $monthCount = array();
            $bMonthYear = "";
			while ($row = mysqli_fetch_array($result)) {

            $sessionTime = strtotime($row['datesess']);

              $sDate = date("M j, Y", $sessionTime);
              $aResults[] = array("date" => $sDate, "num" => $row['numsess'], "pass" => $row['pass']);

          }



			return $aResults;
		}

    function GetPointsByMonth($withPass = 2) {

        switch($withPass) {
          case "0":
            $sPass = " AND `pass` = '0'";
            break;
          case "1":
            $sPass = " AND `pass` = '1'";
            break;
          default:
            $sPass = "";
            break;
        }

        $sql = "SELECT YEAR(`session`) as `yearsess`, MONTH(`session`) as 'monthsess', COUNT(*) as `numsess` FROM `points` WHERE `uid` = '" . 
         mysqli_real_escape_string($this->con, $this->userid) . "'" . $sPass . "
         GROUP BY YEAR(`session`), MONTH(`session`) ORDER BY YEAR(`session`) DESC, MONTH(`session`) DESC";

      $result = mysqli_query($this->con, $sql);

      $aResults = array();
      $monthCount = array();
      $bMonthYear = "";
      while ($row = mysqli_fetch_array($result)) {

            $sessYear = $row['yearsess'];
            $sessMonth = $row['monthsess'];
            $monthName = date('F', mktime(0, 0, 0, $sessMonth, 10));

            $aResults[] = array("date" => $monthName . ' ' . $sessYear, "num" => $row['numsess']);

          }



      return $aResults;

    }

		function GetTotalPointsForUser() {

			$sql = "SELECT COUNT(*) as `totalpoints` FROM `points` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "'";

			$result = mysqli_query($this->con, $sql); 

			if ($row = mysqli_fetch_array($result)) {

				$total = $row['totalpoints'];
			}

			return $total;

		}


    function GetRecentPointsForUser($withPass = 2) {

        switch($withPass) {
          case "0":
            $sPass = " AND `pass` = '0'";
            break;
          case "1":
            $sPass = " AND `pass` = '1'";
            break;
          default:
            $sPass = "";
            break;
        }

      //$sql = "SELECT COUNT(*) as `totalpoints` FROM `points` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' AND DATE(`session`) > DATE_SUB(curdate(), INTERVAL 6 MONTH)";
      $sql = "SELECT COUNT(*) as `totalpoints` FROM `points` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' AND DATE(`session`) > DATE('2015-10-16 00:00:00')" . $sPass;

      $result = mysqli_query($this->con, $sql); 

      if ($row = mysqli_fetch_array($result)) {

        $total = $row['totalpoints'];
      }

      return $total;

    }

		function GetPurchasesForUser() {

			$sql = "SELECT * FROM `purchase` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' ORDER BY `id` DESC";

			$result = mysqli_query($this->con, $sql);

			$aResults = array();
			while ($row = mysqli_fetch_array($result)) {
				$sDate = date("M j, Y", strtotime($row['purchaseDate']));
				$aResults[] = array('date' => $sDate, 'points' => $row['points'], 'price' => $row['price'], 'pass' => $row['pass'], 'note' => $row['note']);
			}

			return $aResults;
		}

		function GetTotalPurchasesForUser() {

			$sql = "SELECT SUM(`points`) as totalpoints FROM `purchase` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "'";

			$result = mysqli_query($this->con, $sql); 

			if ($row = mysqli_fetch_array($result)) {

				$total = $row['totalpoints'];

			}

			return $total;

		}

        function GetNumberOfClasses() {

            $sql = "SELECT COUNT(*) as numclasses FROM `class_user` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "'";
            $result = mysqli_query($this->con, $sql);

            if ($row = mysqli_fetch_array($result)) {
                $numClasses = $row['numclasses'];
            } else {
                $numClasses = 0;
            }

            return $numClasses;
        }

        function GetNumberOfClassesWithPass() {

            $sql = "SELECT COUNT(*) as numclasses FROM `class_user` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' AND `pass` = 1";
            $result = mysqli_query($this->con, $sql);

            if ($row = mysqli_fetch_array($result)) {
                $numClasses = $row['numclasses'];
            } else {
                $numClasses = 0;
            }

            return $numClasses;
        }

      function GetUserBalance() {

         $totalPoints = $this->GetRecentPointsForUser(0);
         $totalPurchases = $this->GetTotalPurchasesForUser(0);
         $totalClasses = $this->GetNumberOfClasses() - $this->GetNumberOfClassesWithPass();

         $deduct = $this->GetNumberOnePointClasses();

         return $totalPurchases - $totalPoints - $totalClasses * 2 + $deduct;

      }

      function GetNumberOnePointClasses()
      {
          $sql = "select cu.`uid`, count(*) as numone from `class_user` cu left join `classes` c on cu.`classid` = c.`id`
                    where cu.`pass` = 0 and cu.`uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' and c.`start` < '2025-02-01 00:00:00'";

          $result = mysqli_query($this->con, $sql);

          if ($row = mysqli_fetch_array($result)) {
              $numone = $row['numone'];
          } else {
              $numone = 0;
          }

          return $numone;
      }

        function GetNumberOnePointClassesWithPass()
        {
            $sql = "select cu.`uid`, count(*) as numone from `class_user` cu left join `classes` c on cu.`classid` = c.`id`
                    where cu.`pass` = 1 and cu.`uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' and c.`start` < '2025-02-01 00:00:00'";

            $result = mysqli_query($this->con, $sql);

            if ($row = mysqli_fetch_array($result)) {
                $numone = $row['numone'];
            } else {
                $numone = 0;
            }

            return $numone;
        }

      function TodaysAdjustedPoints() {

            $sql = "SELECT `uid`, SUM(`points`) as `today` FROM `purchase` WHERE `uid` = '" . $this->userid . "' AND `purchaseDate` = CURDATE() GROUP BY `uid`";
            $result = mysqli_query($this->con, $sql);

            if ($row = mysqli_fetch_array($result)) {
              $total = $row['today'];
            }

            return $total;
      }

		function GetTotalPaymentsForUser() {

			$sql = "SELECT SUM(`price`) as totalprice FROM `purchase` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "'";

			$result = mysqli_query($this->con, $sql); 

			if ($row = mysqli_fetch_array($result)) {

				$total = $row['totalprice'];

			}

			return $total;

		} 

		function AddPurchase($aParams) {


      if ($aParams['pass']) {
        $sPass = $aParams['pass'];
        $aPassDate = explode("-", $aParams['pass']);
        if (!$this->AddUserPass($this->userid, $aPassDate[0], $aPassDate[1])) return false;
      } else {
        $sPass = "";
      }

      $admin = $aParams['admin'] ?? 0;
      $ipaddr = $aParams['ipaddr'] ?? $_SERVER['REMOTE_ADDR'];
      $note = "Adjustment " . $aParams['note'];

      $result2 = $this->AppendNote($note, $aParams['uid'], $admin);

			$sql = "INSERT INTO `purchase` ( `uid`, `points`, `pass`, `price`, `purchaseDate`, `admin`, `ipaddr`, `note`, `entered`) VALUES ('"
                . mysqli_real_escape_string($this->con, $aParams['uid']) . "', '"
				. mysqli_real_escape_string($this->con, $aParams['points']) . "', '" 
                . mysqli_real_escape_string($this->con, $sPass) . "', '"
				. mysqli_real_escape_string($this->con, $aParams['price']) . "', '"
				. mysqli_real_escape_string($this->con, $aParams['date']) . "', '"
				. mysqli_real_escape_string($this->con, $admin) . "', '"
				. mysqli_real_escape_string($this->con, $ipaddr) . "', '"
				. mysqli_real_escape_string($this->con, $aParams['note']) . "', NOW())";

			$result = mysqli_query($this->con, $sql);

			return $result;

		}

      function GetSchedule($dbdate) {

         $sql = "SELECT * FROM `schedule` WHERE `date` = '" . mysqli_real_escape_string($this->con, $dbdate) . "'";

         $result = mysqli_query($this->con, $sql);

         //var_dump($result);exit;

         $aTimes = array();
         while ($row = mysqli_fetch_array($result)) {
            $aTimes[] = array("start" => $row['start'], "stop" => $row['stop']);
         }

         return $aTimes;

      }

      function RetrieveDailySession($sDate) {

            $sql = "SELECT * FROM `schedule` WHERE `date` = '" . $sDate . "'";
            $result = mysqli_query($this->con, $sql);
            $aDayData = array();
            while ($row = mysqli_fetch_array($result)) {
                $aDayData[] = array('date' => $sDate, 'start' => $row['start'], 'stop' => $row['stop']);
            }

            return $aDayData;
        }
      
      function ClearSession($sDate) {
      
      	
      	$sql = "DELETE FROM `points` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' AND DATE(`session`) = '" . $sDate . "'";
      	$result = mysqli_query($this->con, $sql);
      	
      	return $result;
      	
      }

      function VerifySession($time) {

         $dateFormatted = date("Y-m-d G:i:s", $time);

         $sql = "SELECT * FROM `points` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' AND `session` = '" . mysqli_real_escape_string($this->con, $dateFormatted) . "'";
         $result = mysqli_query($this->con, $sql);

         if ($aRow = mysqli_fetch_array($result)) {
            return FALSE;
         }
         
         return TRUE;

      }
      
      function GetSession($sDate) {
      	
      	$sql = "SELECT * FROM `points` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $this->userid) . "' AND DATE(`session`) = '" . $sDate . "'";
      	$result = mysqli_query($this->con, $sql);
      	
      	$aSession = array();
      	while ($aRow = mysqli_fetch_array($result)) {
      		$sTime = strtotime($aRow['session']);
      		$sHour = date("G", $sTime);
      		$sMin = date("i", $sTime);
      		$aSession[] = $sHour * 60 + $sMin;
      	}
      	
      	return $aSession;
      	
      }

      function AddSession($time, $hasPass = false) {

         $dateFormatted = date("Y-m-d G:i:s", $time);

         $noteText = $hasPass ? "1" : "0";

         $sql = "INSERT INTO `points` (`uid`, `session`, `pass`) VALUES ( '" . mysqli_real_escape_string($this->con, $this->userid) . "', '" . mysqli_real_escape_string($this->con, $dateFormatted) . "', '" . mysqli_real_escape_string($this->con, $noteText) . "')";
         $result = mysqli_query($this->con, $sql);

      }

      function GetTodaysSkaters($_date = null) {

        if ($_date == null) {

          $sDate = date("Y-m-d");

        } else {

          $sDate = $_date;
        }

         $sql = "select p.id as pointid, p.uid, p.entered, p.pass, u.fname, u.lname, u.role, p.session, p.pass from points p inner join users u on u.id = p.uid 
         		where date(p.session) = '" . mysqli_real_escape_string($this->con, $sDate) . "' ORDER BY p.uid, p.session";
         
         $result = mysqli_query($this->con, $sql);

         $aTodaysSkaters = array();
         $currentid = 0;
         $count = -1;
         while ($row = mysqli_fetch_array($result)) {
            if ($row['uid'] != $currentid) {
               $currentid = $row['uid'];
               $count++;
            }

            $sessionStart = strtotime($row['session']);
            //$order = strtotime($row['entered']);
            $order = strtotime($row['session']);

            $aTodaysSkaters[$currentid][] = array("name" => $row['lname'] . ", " . substr($row['fname'],0,2) . ".", "role" => $row['role'], "session" => $sessionStart, "pass" => $row['pass'], "order" => $order);

         }

         return $aTodaysSkaters;


      }

      function GetDailySkaterData($_date) {

        $sql = "SELECT COUNT(DISTINCT `uid`) as `numskaters`, COUNT(`session`) as `numsessions` FROM `points` WHERE DATE(`session`) = '" . mysqli_real_escape_string($this->con, $_date) . "'";

        $result = mysqli_query($this->con, $sql);

        if ($row = mysqli_fetch_array($result)) {

          $return = array("skaters" => $row['numskaters'], "sessions" => $row['numsessions']);

        }

        return $return;

      }

      function GetSkaterCount($_date) {

        $sql = "SELECT COUNT(*) as `numregistered`, `session` FROM `points` p LEFT JOIN `users` u ON p.`uid` = u.`id` WHERE DATE(`session`) = '" . mysqli_real_escape_string($this->con, $_date) . "' AND u.`role` = 1 GROUP BY `session`";
        
        $result = mysqli_query($this->con, $sql);

        $return = array();

        while ($row = mysqli_fetch_array($result)) {

          $return[] = array("session" => $row['session'], "registered" => $row['numregistered']);

        }

        return $return;

      }


      function GetUserData() {

         $sql = "SELECT * FROM `users` WHERE `id` = '" . $this->userid . "'";
         $result = mysqli_query($this->con, $sql);
         if ($row = mysqli_fetch_array($result)) {
            $returnData = $row;
         } else {
             $returnData = null;
         }

         return $returnData;
      }

      function GetAllUsers($filter) {

          $sql = "SELECT *, u.`id` as `userid`, GROUP_CONCAT(p.`month`,'/',p.`year`) as pass FROM `users` u LEFT JOIN passes p ON u.`id` = p.`uid` WHERE u.`role` > 0 GROUP BY u.`id` ORDER BY u.`lname` ASC, u.`fname` ASC";
          $result = mysqli_query($this->con, $sql);

          $returnUsers = array();

          // filter users who haven't skated in a year
          while ($row = mysqli_fetch_array($result)) {
              if (isset($filter['active']) && $filter['active']) {
                  $getMax = "SELECT MAX(`session`) as lastsession FROM points WHERE uid = '" . $row['userid'] . "'";
                  $result2 = mysqli_query($this->con, $getMax);
                  if ($row2 = mysqli_fetch_array($result2)) {
                      if (time() - 24*365*3600 < strtotime($row2['lastsession'])) {
                          $returnUsers[] = $row;
                      }
                  }
              } elseif (isset($filter['pin'])) {

                  if ($row['pin'] == $filter['pin']) {
                      $returnUsers[] = $row;
                  }

              } elseif (isset($filter['user'])) {
                  $tmpString = $filter['user'];

                  if (stristr($row['fname'], $tmpString) || stristr($row['lname'], $tmpString) || stristr($row['email'], $tmpString)) {
                      $returnUsers[] = $row;

                  }

              } else {
                  $returnUsers[] = $row;
              }
          }

          return $returnUsers;

      }

      function DeleteUser() {

         $sql = "DELETE FROM `users` WHERE `id` = '" . $this->userid . "'";
         $result = mysqli_query($this->con, $sql);

         return $result;
         
      }
      
      function IsInvoiceOpen() {
          
          $sql = "SELECT * FROM `invoice` WHERE `status` = 'open' AND `uid` = '" . $this->userid . "'";
          $result = mysqli_query($this->con, $sql);
          
          if ($row = mysqli_fetch_array($result)) {
              return "open";
          }
          
          return "closed";
      }
      
      function AddInvoice($uid, $amount, $points, $note, $date) {
          
          $ref = uniqid("is");
          
          $sql = "INSERT INTO `invoice` (`invid`, `uid`, `amount`, `points`, `note`, `date`) "
                  . "VALUES ('$ref', '$uid', '$amount', '$points', '" . mysqli_real_escape_string($this->con, $note) . "', '$date')";
          
          $result = mysqli_query($this->con, $sql);
          
         if ($result == true) {
          
             $aParams = array('uid' => $uid, 'points' => $points, 'price' => $amount, 'date' => $date, 'note' => $note );
             //$bAdd = $this->AddPurchase($aParams);
             
             return $bAdd;
          
         }
         
         return false;
          
          
     }

     function CheckPIN($pin) {

         $sql = "SELECT * FROM `users` WHERE `pin` = '" . $pin . "'";

         $result = mysqli_query($this->con, $sql);

         $rows = mysqli_num_rows($result);

         return $rows ? FALSE : TRUE;

     }

     function CheckEmail($email)
     {

         $sql = "SELECT * FROM `users` WHERE `email` = '" . mysqli_real_escape_string($this->con, $email) . "'";

         $result = mysqli_query($this->con, $sql);

         return $result->num_rows == 0;

     }

        function CheckName($fname, $lname)
        {

            $sql = "SELECT * FROM `users` WHERE `fname` = '" . mysqli_real_escape_string($this->con, $fname) . "' AND `lname` = '" . mysqli_real_escape_string($this->con, $lname) . "'";

            $result = mysqli_query($this->con, $sql);

            return $result->num_rows == 0;

        }

     function GetNextSessionDate() {

         $sql = "SELECT * FROM `schedule` WHERE NOW() < TIMESTAMP(`date`, `stop`) ORDER BY `date`, `stop`";
         $result = mysqli_query($this->con, $sql);
         if ($row = mysqli_fetch_array($result)) {
             return date("Y-m-d", strtotime($row['date']));
         } else {
             return date("Y-m-d");
         }

     }

     function IsAdminPIN($pin) {

          $sql = "SELECT * FROM `users` WHERE `pin` = '" . $pin . "'";

          $result = mysqli_query($this->con, $sql);


          if ($row = mysqli_fetch_array($result)) {

            if ($row['role'] == 3) return true;

          }

          return false;

     }

     function AddUserPass($uid, $month, $year) {

      if ($hasPass = $this->HasUserPass($uid, $month, $year)) {
        return false;
      }
      $sql = "INSERT INTO `passes` (`uid`, `month`, `year`) VALUES ('" . mysqli_real_escape_string($this->con, $uid) . "', '" . mysqli_real_escape_string($this->con, $month) . "', '" . mysqli_real_escape_string($this->con, $year) . "')";

      $result = mysqli_query($this->con, $sql);

      if ($result) {
        $result2 = $this->AddPassCurrentMonth($uid, $month, $year);
      } else {
        return $result;
      }

      return $result2;

     }

     function AddPassCurrentMonth($uid, $month, $year) 
     {
        $month2 = $month + 1;
        if ($month2 > 12) { $month2 = 1; $year2 = $year + 1; }
        else $year2 = $year; 

        $dbDate1 = date("Y-m-d G:i:s", strtotime($year . "-" . $month . "-01 00:00:00"));
        $dbDate2 = date("Y-m-d G:i:s", strtotime($year2 . "-" . ($month2) . "-01 00:00:00"));

        $sql = "UPDATE `points` SET `pass` = '1' WHERE `uid` = '" . mysqli_real_escape_string($this->con, $uid) . "' AND DATE(`session`) >= '" . $dbDate1 . "' AND DATE(`session`) < '" . $dbDate2 . "'";
        $result = mysqli_query($this->con, $sql);

         $sql2 = "UPDATE `class_user` cu LEFT JOIN `classes` c ON cu.`classid` = c.`id` SET cu.`pass` = '1'  
            WHERE `uid` = '" . mysqli_real_escape_string($this->con, $uid) . "' AND DATE(c.`start`) >= '" . $dbDate1 . "' AND DATE(c.`end`) < '" . $dbDate2 . "'";
         $result2 = mysqli_query($this->con, $sql2);

        return $result;

     }

     function HasUserPass($uid, $month, $year) {
      
      $sql = "SELECT * FROM `passes` WHERE `uid` = '" . mysqli_real_escape_string($this->con, $uid) . "'";

      $result = mysqli_query($this->con, $sql);

      $validPass = false;
      while ($row = mysqli_fetch_array($result)) {

        if ($row['uid'] == $uid && $row['month'] == $month && $row['year'] == $year) {
          $validPass = true;
        }

      }

      return $validPass;

     }

     function AppendNote($note, $userId, $adminId) {

            if (strlen($note) == 0 || strlen($note) > 500) {
                return false;
            }

            $sql = "INSERT INTO `notes` (`userId`, `adminId`, `note`) 
                    VALUES (" . mysqli_real_escape_string($this->con, $userId) . ", '"
                            . mysqli_real_escape_string($this->con, $adminId) . "', '"
                            . mysqli_real_escape_string($this->con, $note) . "')";

            $result = mysqli_query($this->con, $sql);

            return $result;
     }

     function GetNotes($uid) {

         $sql = "SELECT * FROM `notes` WHERE `userId` = '" . mysqli_real_escape_string($this->con, $uid) . "' ORDER BY `entered` DESC";
         $result = mysqli_query($this->con, $sql);

         $notesArray = array();
         while ($row = mysqli_fetch_array($result)) {
             $notesArray[] = $row;
         }

         return $notesArray;

     }

     function GetNewAccounts() {

        $sqlDate = date("Y-m-d H:i:s", strtotime("-1 week"));
        $sql = "SELECT * FROM `users` WHERE `role` = 0 AND `created` > '" . $sqlDate . "'";

        $result = mysqli_query($this->con, $sql);

         $accountArray = array();
         while ($row = mysqli_fetch_array($result)) {
             $accountArray[] = $row;
         }

         return $accountArray;

     }

        function ApproveAccount($uid) {

            $sql = "UPDATE `users` SET `role` = 1 WHERE `id` = '" . mysqli_real_escape_string($this->con, $uid) . "'";

            $result = mysqli_query($this->con, $sql);

            return $result;

        }

        function DenyAccount($uid) {

            $sql = "UPDATE `users` SET `role` = -1 WHERE `id` = '" . mysqli_real_escape_string($this->con, $uid) . "'";

            $result = mysqli_query($this->con, $sql);

            return $result;

        }

	}

?>