<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");

	if (!$sessionUser) {
		header("Location: /login.php");
	}

	include_once("header.php");

?>

		<div class="infoBar">
			<div class="pageTitle">ICE SKATE USA INFORMATION SCREEN</div> 
		</div>

		<div class="headline"><div class="headlineText"><span class="gold">Welcome</span> to Ice Skate USA freestyle points application monitor! This is our new information page...</div>

		<div class="leftContent">

				<div class="scheduleInfo">
					<span class="largeGold">This week's schedule</span>
						<div class="infoTable">
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
		
			<div class="skaterInfo">
				<span class="largeGold">This week's activity</span>

					<div class="infoTable">
							<ul>

								<?php

									for ($i = 0; $i > -7; $i--) {

										$currentTime = time() + ($i * 24 * 3600);

										$currentDateSQL = date("Y-m-d", $currentTime);

										$currentDateDisplay = date("D n/d", $currentTime);

										$oDataModel = new DataModel(0, $dbconnection);

										$data = $oDataModel->GetDailySkaterData($currentDateSQL);

										$numskaters = $data['skaters'];
										$numsessions = $data['sessions'];

										echo "<li><span class=\"gold\">$currentDateDisplay</span> $numskaters <span class=\"gold\">skaters</span> $numsessions <span class=\"gold\">sessions</span></li>";

									}

								?>

							</ul>
					</div>
			</div>

		</div>

		<div class="news">
		<span class="largeGold">Ice Skate USA News</span>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed quis vehicula mauris. Proin convallis semper urna, sit amet venenatis augue maximus non. Vivamus consequat, 
			purus id ornare consequat, sem tortor iaculis elit, nec luctus nibh enim et augue. In hac habitasse platea dictumst. Sed auctor lobortis eros, quis consectetur mi luctus vel. 
			Fusce feugiat erat aliquet lorem tincidunt, vestibulum pellentesque lectus ultrices. </p>
			<p>Suspendisse vel dapibus dui, ac bibendum sem. Vivamus sed leo nec neque varius dignissim. Ut eleifend vel nibh sit amet fringilla. Nulla feugiat, odio vel fermentum consequat,
			 nunc leo pulvinar nisl, ut sagittis enim ligula id ipsum. Integer feugiat quis sapien ac mollis. Quisque lobortis sem a risus commodo, non ultricies quam iaculis.</p>
			<p>Duis ipsum neque, lobortis eget felis vitae, mattis ultricies nunc. Pellentesque sed aliquet odio, sit amet sagittis ex. Quisque bibendum massa vel augue vestibulum,
			 ut venenatis tellus facilisis. Vivamus quis nisi enim. Donec posuere odio ac finibus mattis. Aliquam quis ligula pharetra, dictum lectus porttitor, porta nisi.</p>
		</div>




		</div>

<?php 

	include_once("footer.php");

?>