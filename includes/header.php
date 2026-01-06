	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>outlook</title>
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/round-about.css" rel="stylesheet">
	<link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css"/>
	<link rel="stylesheet" href="css/notifyBar.css" type="text/css"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<script src="js/jquery.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.tablesorter.min.js"></script>
	<script src="js/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.notifyBar.js" type="text/javascript" charset="utf-8"></script>
	<!-- Datepicker of right side -->
	<link rel="stylesheet" href="css/jquery-ui.css">
	<script src="js/jquery-ui.js"></script>
	<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/>
	<script src="js/jquery.datetimepicker.full.js"></script>
	<!-- Full calender -->
	<link href='css/fullcalendar.css' rel='stylesheet' />
	<link href='css/fullcalendar.print.css' rel='stylesheet' media='print' />
	<script src='js/moment.min.js' charset="utf-8"></script>
	<script src='js/fullcalendar.min.js' charset="utf-8"></script>
	<script src='js/de.js' charset="utf-8"></script>
	<script type="text/javascript">
		$(function() {
			$( "#datepicker" ).datepicker({
				inline: true,
				onSelect: function()
				{
					window.location.href="appointment.php?selected_date="+$( "#datepicker" ).val();
				}
			});
			<?php
				if(isset($_GET['selected_date']) && $_GET['selected_date']!=''){
			?>
				var defaultdate=new Date("<?php echo $_GET['selected_date'];?>");
				$( "#datepicker" ).datepicker("setDate", defaultdate);
			<?php
				}
			?>
			$("#myTable").tablesorter();
			$('[data-toggle="popover"]').popover();
			$(window).on("resize load", function() {
				$('.content').css('height',$(window).height()-65);
			});
		});
	</script>
	<style type="text/css">
		.popover{width: 250px;}
		.active_menu{background: rgb(227, 239, 255);}
		.cate {
			padding-left: 15px;
			color: #555;
			font-size: 12px;
			margin: 5px 0px;
			font-weight: bold;
			cursor: pointer;
			padding: 5px 0px 5px 15px;
		}
		.no_record_cls
		{
			text-align:center;
			color:red;
		}
	</style>
	<script type="text/javascript">
	<!--
		// Set timeout variables.
		var timoutWarning = 25 * 60 * 1000; // Display warning in 25 Mins.
		var timoutNow = 30 * 60 * 1000; // Timeout in 30 mins.
		var logoutUrl = '<?php echo DOMAIN_NAME_PATH;?>logout.php'; // URL to logout page.

		var warningTimer;
		var timeoutTimer;

		// Start timers.
		function StartTimers() {
			//warningTimer = setTimeout("IdleWarning()", timoutWarning);
			timeoutTimer = setTimeout("IdleTimeout()", timoutNow);
		}

		// Reset timers.
		function ResetTimers() {
			//clearTimeout(warningTimer);
			clearTimeout(timeoutTimer);
			StartTimers();
		}

		// Show idle timeout warning dialog.
		function IdleWarning() {
			/*if(confirm("You are idle for 4 minutes. You will be logged out soon."))
			{
			}
			else
			{
				ResetTimers();
			}*/
		}

		// Logout the user.
		function IdleTimeout() {
			window.location = logoutUrl;
		}
		$(function(){
			StartTimers();
			$("body").mousemove(function(){
				ResetTimers()
			}).keyup(function(){
				ResetTimers()
			});
		});
	//-->
	</script>