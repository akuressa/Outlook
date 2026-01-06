<?php
	include_once('init.php');
	check_login();
	has_privilege();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$('#calendar').fullCalendar({
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
				},
				defaultDate: $.fullCalendar.moment("<?php echo(isset($_GET['selected_date']) && $_GET['selected_date']!='' ? date('Y-m-d', strtotime($_GET['selected_date'])) : date('Y-m-d'));?>"),
				defaultView: "<?php echo(isset($_GET['selected_date']) && $_GET['selected_date']!='' ? 'agendaDay' : 'month');?>",
				editable: false,
				droppable: false,
				weekNumbers: true,
				timeFormat: 'H:mm',
				//eventLimit: true, // allow "more" link when too many events
				events: "<?php echo(DOMAIN_NAME_PATH)?>fetch_event.php?selected_date=<?php echo(isset($_GET['selected_date']) && $_GET['selected_date']!='' ? $_GET['selected_date'] : '');?>",
				eventRender: function(event, element) {
					$('.fc-title', element).html('<span data-toggle="tooltip" data-placement="top" data-original-title="'+event.tip+'" title="'+event.tip+'">'+event.title+'</span>');
					//$(element).tooltip({title: event.desc});
				},
				dayClick: function(date, jsEvent, view) {
					//alert('Clicked on: ' + date.format());
					//alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
					//alert('Current view: ' + view.name);
					// change the day's background color just for fun
					//$(this).css('background-color', 'red');
					window.location.href="create_appointment.php?selected_date="+date.format();
				}
			});
			$('.fc-prev-button').click(function(){
				var b = $('#calendar').fullCalendar('getDate');
				//alert(b.format('L'));
				//alert($(".fc-state-active").text());
				$.ajax({
					url: '<?php echo(DOMAIN_NAME_PATH)?>fetch_event.php',
					dataType: 'json',
					type: 'POST',
					data:{
						req_date: b.format('L')
					},
					beforeSend: function() {
						$("#loading_img_bg").show();
						$('#calendar').fullCalendar( 'removeEvents');
						$('#calendar').fullCalendar( 'removeEventSource');
					},
					success: function(response){
						//alert(JSON.stringify(response, null, 4));
						$('#calendar').fullCalendar( 'removeEvents');
						$('#calendar').fullCalendar( 'removeEventSource');
						$('#calendar').fullCalendar('addEventSource', response);
						$('#Calendar').fullCalendar('refetchEvents');
						$("#loading_img_bg").hide();
					},
					error: function(){
						$("#loading_img_bg").hide();
					}
				});
			});

			$('.fc-next-button').click(function(){
				var b = $('#calendar').fullCalendar('getDate');
				//alert(b.format('L'));
				//alert($(".fc-state-active").text());
				$.ajax({
					url: '<?php echo(DOMAIN_NAME_PATH)?>fetch_event.php',
					dataType: 'json',
					type: 'POST',
					data:{
						req_date: b.format('L')
					},
					beforeSend: function() {
						$("#loading_img_bg").show();
						$('#calendar').fullCalendar( 'removeEvents');
						$('#calendar').fullCalendar( 'removeEventSource');
					},
					success: function(response){
						//alert(JSON.stringify(response, null, 4));
						$('#calendar').fullCalendar( 'removeEvents');
						$('#calendar').fullCalendar( 'removeEventSource');
						$('#calendar').fullCalendar('addEventSource', response);
						$('#Calendar').fullCalendar('refetchEvents');
						$("#loading_img_bg").hide();
					},
					error: function(){
						$("#loading_img_bg").hide();
					}
				});
			});
		});
	//-->
	</script>
</head>

<body>
	<div id="loading_img_bg" ><img src="img/ajax-loader.gif"/></div>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title">Termin</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px;text-align:right;">
					  <a href = "create_appointment.php"><button class="button" name = "btn_create"><b>Neuen Termin erstellen</b></button></a>
					</div>
					<div class="form-group" style="padding:3px;text-align:right;margin:10px 0px;">
					  <button class="button" name = "refress_page" onclick="window.location.href='appointment.php'"><b>Aktualisieren</b></button>
					</div>
					<div id='calendar'></div>
			  </div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>