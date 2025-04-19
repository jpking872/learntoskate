$(document).ready( function () {

        var price = [];
        price[10] = 110;
        price[30] = 260;
        price[50] = 380;
        
        var initialSubmit = 0;

        if (typeof numberOfSkaters != "undefined") {

			addSkater = numberOfSkaters == "1" ? "skater" : "skaters"

        	$("#totalSkaters").html(numberOfSkaters);
			$("#skaterText").html(addSkater);
        	
        }

	$( "#scheduleDate" ).datepicker({

		onSelect: function() {
			var dateObject = $(this).datepicker('getDate');
			var showDate = $.datepicker.formatDate('MM d, yy',  dateObject);
			$(".selectedDate").text(showDate);
			var datephp = dateObject.toISOString().split('T')[0];
			selectedTime = dateObject.getTime();
			tomorrowTime = new Date().setHours(0,0,0,0) - 3600 * 1000;

			if (selectedTime >= tomorrowTime) {
				$(".timesDiv").show();
			} else {
				$(".timesDiv").hide();
			}

			$(".scheduleDate").val(datephp);
			CallAjax("/dynamic/ajax.php", {type : "getSchedule", date : datephp}, ShowSchedule);
		}

	});

	$( "#previousDate" ).datepicker({ dateFormat: "yy-mm-dd",  onClose : function (dateText) { 

		window.location.href = "/previous.php?date=" + dateText;

	}});

	$( "#signupDate" ).datepicker({ dateFormat: "yy-mm-dd",  onClose : function (dateText) { 

		window.location.href = "/points.php?date=" + dateText;

	}});

	$("#purchaseDate").datepicker();
	$("#classDate").datepicker({ dateFormat: "yy-mm-dd"});
	$(".addClassToggle").click(function () { $("#add_class_form").toggle() });
	$(".cancelClassToggle").click(function () { $("#cancel_class_form").toggle() });
	$(".deleteClassToggle").click(function () { $("#delete_class_form").toggle() });
	$(".addNoteToggle").click(function () { $("#addNote").toggle() });

    $("#purchase_form").submit(function (e) {
    	
    	if (initialSubmit == 1) {
    		return true;
    	}
    	
    	e.preventDefault();
    	VerifyPurchase($("input[name='skater']", this).val(), $("input[name='points']", this).val());
    
    });

	$("#add_class_form").submit(function (e) {
		if ($("#classDate").val() == "" || $(".classTitle").val().length <= 3 || $(".classStart").find(":selected").val() == -1 || $(".classEnd").find(":selected").val() == -1) {
			alert("Invalid parameters");
			return false;
		} else {
			return true;
		}
	})

	$("#historyTemplate").dialog( { width: 925, height: "auto", autoOpen: false, position: { my: "center top", at: "center top", of: window } });

	$("#editUser").dialog( { width: 725, height: "auto", autoOpen: false, position: { my: "center top", at: "center top", of: window } });
	$("#adjustUser").dialog( { width: 725, height: "auto", autoOpen: false, position: { my: "center top", at: "center top", of: window } });
	$("#emailUser").dialog( { width: 725, height: "auto", autoOpen: false, position: { my: "center top", at: "center top", of: window } });

	$(".emailStringLink").click(function () {
		var emailString = $(".emailString").text();
		navigator.clipboard.writeText(emailString);
	})

	$(".editLink").click( function (e) {
		e.preventDefault();
		var id = $(this).data("id");
		console.log(id)
		EditUser(id);
	});

	$(".deleteLink").click(function (e) {
		e.preventDefault();
		var id = $(this).data('id');
		DeleteUser(id);
	})

	$(".adjustLink").click(function (e) {
		e.preventDefault();
		var id = $(this).data('id');
		AdjustUser(id);
	})

	$(".emailLink").click(function (e) {
		e.preventDefault();
		var id = $(this).data('id');
		EmailUser(id);
	})

	$("#getHistory").click(function () {

		var selectedSkater = $("#skater").val();
		GetHistory(selectedSkater);


	})

	$(".timeSlot").change(function () {
		var num = 0;
		$.each($(".timeSlot"), function () {
			if ($(this).prop("checked")) {
				num += 1;
			}
		})
		$("#sessionPoints").html(num)
	})

	$(".searchSkater").focus();
	$(".searchSkater").keyup(function (e) {
		var key = e.which;
		if (key == 13) {
			var searchString = $(".searchSkater").val();
			if (searchString.length > 3) {
				$("#searchForm").submit();
			}
		}
	})

	CallAjax("/dynamic/ajax.php", {type : "getSchedule", date : "0"}, ShowSchedule);
	CallAjax("/dynamic/ajax.php", {type : "getScheduleList", date : "0"}, ShowScheduleList);

	//GetCurrentSkaters();

	/*$(".userLink").click( function (e) {
		e.preventDefault();
		var uid = $(this).data("uid");
		GetHistory(uid);
	})*/

	$("#editUserForm").submit(function () {

		if ($("input[name='fname']").val() == "" || $("input[name='lname']").val() == "" || $("input[name='pin']").val() == "" || $("input[name='email']").val() == "" || 
			($("input[name='password']").val() != $("input[name='confpassword']").val())) {

			alert("Data error");
			return false;

		} else {

			$("#editUser").dialog("close");
			$("#usertable, #searchForm").hide();
			$(".loadingDiv").show();
			return true;
		}
	})

	$("#adjustUserForm").submit(function () {

		if ($("input[name='price']").val() == "" || $("input[name='points']").val() == "" || $("input[name='date']").val() == "") {

			alert("Must fill out all fields.");
			return false;

		} else {

			$("#adjustUser").dialog("close");
			$("#usertable, #searchForm").hide();
			$(".loadingDiv").show();
			return true;
		}
	})

	$("#pinInput").keyup(function() {
		CallAjax("/dynamic/ajax.php", {type : "checkAdmin", pin : $(this).val() }, ShowAdminPass);
	})

	$(".toggleShow").click(function () {
		$(".showHideRow").toggle();
	})

	function CallAjax(action, dataObject, callback) {

		$.ajax({
			type: "POST",
			url: action,
			data: dataObject,
			})
			.done(function( data ) {
                            
                callback(data);

		});

	}

	function ShowSchedule(scheduleData) {

		var schedule = JSON.parse(scheduleData);
		sTable = "";

		for (m = 1; m <= 6; m++) {
			$("select[name='starttime[" + m.toString() + "]'").val("-1");
			$("select[name='endtime[" + m.toString() + "]'").val("-1");

		}

		for (i = 0; i < 21; i++) {

			for (j = 0; j < schedule[i].length; j++) {
				sTable += "<tr><td>" + schedule[i][j].date + "</td>\n";
				sTable += "<td>" + schedule[i][j].start + "</td>\n";
				sTable += "<td>" + schedule[i][j].stop + "</td></tr>\n";

				if (schedule[i][j].date == $(".scheduleDate").val() && schedule[i][j].start !== "-") {
					k = j + 1;
					$("select[name='starttime[" + k.toString() + "]'").val(schedule[i][j].start);
					$("select[name='endtime[" + k.toString() + "]'").val(schedule[i][j].stop);
				}
			}
		}
		$('.scheduleTable tbody').html("");
		$('.scheduleTable tbody').html(sTable);
	}

	function ShowScheduleList(scheduleData) {

		var schedule = JSON.parse(scheduleData);
		sTable = "";

		for (i = 0; i < 7; i++) {
			for (j = 0; j < schedule[i].length; j++) {

				if (schedule[i][j].start == false || schedule[i][j].stop == false) {
					sTable += "<li><span class=\"gold\">" + schedule[i][j].date + "</span> no freestyle</li>\n";
				} else {
					var jsstart = schedule[i][j].start;
					var jsstop = schedule[i][j].stop;
					var hourstart = jsstart.split(":");
					var hourstop = jsstop.split(":");

					if (hourstart[0] >= 12) {
						if (hourstart[0] != 12) {
							hourstart[0] = hourstart[0] - 12;
						}
						if (hourstart[0] < 9) {
							hourstart[0] = "0" + hourstart[0];
						}
						goldstart = true;
					} else {
						goldstart = false;
					}

					if (hourstop[0] >= 12) {
						if (hourstop[0] != 12) {
							hourstop[0] = hourstop[0] - 12;
						}
						if (hourstop[0] < 9) {
							hourstop[0] = "0" + hourstop[0];
						}
						goldstop = true;
					} else {
						goldstop = false;
					}

					//timestart = goldstart ? "<span class=\"goldtime\">" + hourstart[0] + ":" + hourstart[1] + "</span>" : hourstart[0] + ":" + hourstart[1];
					//timestop = goldstop ? "<span class=\"goldtime\">" + hourstop[0] + ":" + hourstop[1] + "</span>" : hourstop[0] + ":" + hourstop[1];

					timestart = goldstart ? hourstart[0] + ":" + hourstart[1] + "p" : hourstart[0] + ":" + hourstart[1] + "a";
					timestop = goldstop ? hourstop[0] + ":" + hourstop[1] + "p" : hourstop[0] + ":" + hourstop[1] + "a";

					//sTable += "<li><span class=\"gold\">" + schedule[i][j].date + "</span> " + schedule[i][j].start + "-" + schedule[i][j].stop + "</li>\n";
					sTable += "<li><span class=\"gold\">" + schedule[i][j].date + "</span> " + timestart + "-" + timestop + "</li>\n";

				}
			}
		}

		$('.schedule ul').html(sTable);
		if ($('.scheduleInfo ul').length > 0) {
			$('.scheduleInfo ul').html(sTable);
		}
	}

	function ShowAdminPass(adminCheck) {

		if (adminCheck == 1) {
			$(".adminWrapper").show();
		} else {
			$(".adminWrapper").hide();
		}
	}

	function DeleteUser(uid) {

		var c = confirm("Do really really want to delete user (" + uid + ")?");
		if (!c) return false;

		$("#usertable, #searchForm").hide();
		$(".loadingDiv").show();

		CallAjax("/dynamic/ajax.php", {type: "deleteUser", id: uid}, alertDelete);
	}


	function alertDelete() {
		alert("User deleted");
		window.location.reload();
	}

	function EditUser(uid) {

		$("#editUser").dialog("open");

		CallAjax("/dynamic/ajax.php", {type: "getUser", id: uid}, UpdateUserForm);

	}

	function UpdateUserForm(userdata) {

		var user = JSON.parse(userdata);

		$("input[name='userid']").val(user.id);
		$("input[name='fname']").val(user.fname);
		$("input[name='lname']").val(user.lname);
		$("input[name='pin']").val(user.pin);
		$("input[name='oldpin']").val(user.pin);
		$("input[name='email']").val(user.email);

		$("select[name='level']").val(user.level);
		isRegChecked = user.registration == 1;
		$("input[name='registration']").prop('checked', isRegChecked )
		isWaiverChecked = user.waiver == 1;
		$("input[name='waiver']").prop('checked', isWaiverChecked )


	}

	function AdjustUser(uid) {
		$("#adjustUser").dialog("open");
		$("input[name='userid']").val(uid);
		CallAjax("/dynamic/ajax.php", {type: "getUser", id: uid}, function(data) { var userdata = JSON.parse(data); $(".adjustName").html(userdata.fname + " " + userdata.lname) } );
	}

	function EmailUser(uid) {
		$("#emailUser").dialog("open");
		$("input[name='userid']").val(uid);
		
		CallAjax("/dynamic/ajax.php", {type: "getUser", id: uid}, function(data) { 

			var userdata = JSON.parse(data); 
			$("input[name='skaterName']").val(userdata.fname + " " + userdata.lname); 
			$("input[name='skaterEmail']").val(userdata.email); 

		});
	}

	function GetHistory(uid) {

		CallAjax("/dynamic/ajax.php", {type : "getHistory", id : uid}, ShowHistory);
		
	}

	function ShowHistory(historyData) {

		var history = JSON.parse(historyData);

		var formatPrice = parseFloat(Math.round(history.totalpayments * 100) / 100).toFixed(2);

		$("#totalPoints").html(history.totalpoints[0] + "<span class=\"green\">(" + history.totalpoints[1] + ")</span>");
		$("#totalPurchases").html(history.totalpurchases);
		$("#totalPayments").html("$" + formatPrice);

		stringCoach = history.userinfo.role == 2 ? " (coach)" : "";
		$("span.skaterName").html(history.userinfo.fname + " " + history.userinfo.lname + stringCoach)

		var pointBal = history.totalpurchases - history.totalpoints[0];

		$("#pointBalance").html(pointBal);
		if (pointBal < 0) {
			$("#pointBalance").addClass("red");
		} else {
			$("#pointBalance").removeClass("red");
		}

		var sTable1 = "";
		var sTable2 = "";

		for (i = 0; i < history.points.length; i++) {
			classEven =  i % 2 == 0 ? " class='evenRow'" : "";
			sTable1 += "<tr" + classEven + "><td>" + history.points[i].date + "</td>\n";
			sNumber = history.points[i].pass == 1 ? "<span class=\"green\">" + history.points[i].num + "</span>" : history.points[i].num;
			sTable1 += "<td>" + sNumber + "</td></tr>\n";
		}

		sTable1 += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>By Month</td><td>&nbsp;</td></tr>";

		for (i = 0; i < history.monthpoints.length; i++) {
			classEven =  i % 2 == 0 ? " class='evenRow'" : "";
			sTable1 += "<tr" + classEven + "><td>" + history.monthpoints[i].date + "</td>\n";
			sTable1 += "<td>" + history.monthpoints[i].num + "</td></tr>\n";
		}

		for (i = 0; i < history.purchases.length; i++) {
			classEven =  i % 2 == 0 ? " class='evenRow'" : "";
			sTable2 += "<tr" + classEven + "><td>" + history.purchases[i].date + "</td>\n";
			sTable2 += "<td>" + history.purchases[i].points + "</td>\n";
			sTable2 += "<td>" + history.purchases[i].pass + "</td>\n";
			sTable2 += "<td>" + history.purchases[i].price + "</td></tr>\n";
		}


		$('.pointsTable tbody').html(sTable1);
		$('.paymentTable tbody').html(sTable2);

		$("#historyTemplate").dialog("open");


	}

	function GetCurrentSkaters() {

		CallAjax("/dynamic/ajax.php", {type : "getCurrentSkaters"}, ShowSkaters);

	}

	function ShowSkaters(skaterData) {

		var skaters = JSON.parse(skaterData);
		var seconds = parseInt(new Date().getTime() / 1000);
		var returnSkaters = [];
		var onIceSkaters = [];

		//get all uids of registered skaters
		for (var uid in skaters) {

			for (i = 0; i < skaters[uid].length; i++) {

				if ((seconds > (skaters[uid][i]['session'] - 60)) && (seconds < (skaters[uid][i]['session'] + 60 + 1800))) {
					returnSkaters.push({uid: uid, order: skaters[uid][i]['order']});
					break;
				}

			}
				
		}

		//sort skaters by time entered
		returnSkaters.sort(function(a, b) {
			return parseInt(a.order) - parseInt(b.order);
		})

		//we just need uids
		for (i = 0; i < returnSkaters.length; i++) {
			onIceSkaters.push(returnSkaters[i]['uid']);
		}

		var tableHTML = "";
		var skater = [];
		var name, sessionString,timeString; 

		//build the strings for the table
		for (i = 0; i < onIceSkaters.length; i++) {

			name = "";
			sessionString = "";

			for (j = 0; j < skaters[onIceSkaters[i]].length; j++) {

				if (j == 0) {
					name = skaters[onIceSkaters[i]][j]['name'];
				}

				timeString = ConvertSecondsToTime(skaters[onIceSkaters[i]][j]['session'] - 5 * 3600);

				sessionString += timeString;

				if (j < (skaters[onIceSkaters[i]].length - 1)) {
					sessionString += ",";
				}

			}

			skater.push({ 'name' : name, 'sessions' : sessionString });

		}
		
		//populate the skater table
		tableHTML = "<table id=\"skaterTableLeft\" width=\"406\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";

		for (i = 0; i < 12; i++) {
			if (typeof skater[i] != "undefined") {
				tableHTML += "<tr><td width=\"150\" class=\"skater\">" + skater[i].name + "</td><td width=\"256\" class=\"time\">" + skater[i].sessions + "</td></tr>";
			} else {
				if (i == 0) {
					tableHTML += "<tr><td width=\"150\" class=\"skater\">No current skaters.</td><td width=\"256\" class=\"time\"></td></tr>";
				} else {
					tableHTML += "<tr><td width=\"150\" class=\"skater\"></td><td width=\"256\" class=\"time\"></td></tr>";
				}
			}
		}
		
		tableHTML += "</table>";
		tableHTML += "<table id=\"skaterTableRight\" width=\"406\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
		
		for (i = 12; i < 24; i++) {
			if (typeof skater[i] != "undefined") {
				tableHTML += "<tr><td  width=\"150\" class=\"skater\">" + skater[i].name + "</td><td  width=\"256\" class=\"time\">" + skater[i].sessions + "</td></tr>";
			} else {
				tableHTML += "<tr><td  width=\"150\" class=\"skater\"></td><td width=\"256\" class=\"time\"></td></tr>";
			}
		}
		
		tableHTML += "</table>";
		
		if (typeof skater[24] != "undefined") {
			
			tableHTML += "<table id=\"skaterTableLeft\" width=\"406\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
			
			for (i = 24; i < skater.length; i++) {
				if (typeof skater[i] != "undefined") {
					tableHTML += "<tr><td  width=\"150\" class=\"skater\">" + skater[i].name + "</td><td  width=\"256\" class=\"time\">" + skater[i].sessions + "</td></tr>";
				} else {
					tableHTML += "<tr><td  width=\"150\" class=\"skater\"></td><td width=\"256\" class=\"time\"></td></tr>";
				}
			}
			
			tableHTML += "</table>";
		}

		$(".skaters").html(tableHTML); 
		$("#totalSkaters").html(skater.length.toString());

	}

	function ConvertSecondsToTime(sessionSeconds) {

		var hours = parseInt( sessionSeconds / 3600 ) % 24;
		var minutes = parseInt( sessionSeconds / 60 ) % 60;
		var secs = sessionSeconds % 60;

		if (minutes < 10) {
			minutes = "0" + minutes;
		}

		var timeString = hours + ":" + minutes;		

		return timeString.toString();
	}
        
        function VerifyPurchase(uid, points) {
        
            
            CallAjax("/dynamic/ajax.php", {type : "isInvoiceOpen", userid : uid}, 
            		
            		IsValidPurchase
            
            );
            
            
        }
        
        function IsValidPurchase(valid) {
        	
        	console.log(valid)
        	
        	if (valid == "closed") {
        		
        		var c = confirm("Are you sure you want to purchase points?  You will be billed.");
        		
        		if (c) {
        			
        			initialSubmit = 1;
        			$("#purchase_form").submit();
        			
        		} else {
        			
        			initialSubmit = 0;
        			return false;
        		}
        	} else {
        		
        		alert("You are not allowed to make a purchase.");
        		
        	}
        }


                
                    


});