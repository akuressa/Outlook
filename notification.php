<?php
	include_once('init.php');
	check_login();
	has_privilege();
	
	// Only admin can view notifications
	if($admin_privilege != true)
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Sie haben keine Berechtigung, auf diese Seite zuzugreifen.';
		header('location:'.DOMAIN_NAME_PATH.'appointment.php');
		exit;
	}
	
	// Pagination setup
	if(!isset($_GET['page']))
	{
		unset($_SESSION['search_name']);
	}
	
	$where_clause = "WHERE 1=1";
	$execute = array();
	
	// Search functionality
	if(isset($_POST['search_name']) && $_POST['search_name']!="")
	{
		if(isset($_GET['page']))
		{
			unset($_GET['page']);
		}
		if(isset($_SESSION['search_name']))
		{
			unset($_SESSION['search_name']);
		}
		$where_clause .= " AND (u.person_name LIKE :search_name OR a.subject LIKE :search_name)";
		$execute[':search_name'] = '%'.$_POST['search_name'].'%';
		$_SESSION['search_name'] = $_POST['search_name'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		$where_clause .= " AND (u.person_name LIKE :search_name OR a.subject LIKE :search_name)";
		$execute[':search_name'] = '%'.$_SESSION['search_name'].'%';
	}
	
	// Get notification data with appointment and customer details
	$table = NOTIFICATIONS . " as n 
		LEFT JOIN " . APPOINMENTS . " as a ON n.appointment_id = a.id 
		LEFT JOIN " . USERS . " as u ON a.user_id = u.id";
	
	$count_result = find("first", $table, "count(n.id) as total_count", $where_clause, $execute);
	$total_result = $count_result['total_count'];
	
	$record_no = PAGELIMIT;
	$no_of_page = ceil($total_result / $record_no);
	
	if(isset($_GET['page']))
	{
		$page = $_GET['page'];
		if(($page > $no_of_page) && $no_of_page!=0)
		{
			$page=$no_of_page;
		}
		else if($page < 1)
		{
			$page=1;
		}
		else
		{
			$page=$_GET['page'];
		}
	}
	else
	{
		$page=1;
	}
	
	$offset = ($page-1) * $record_no;
	
	$fields = "n.id, n.appointment_id, n.is_email_success, n.is_sms_success, n.sent_at, 
		u.person_name as customer_name, 
		a.subject, a.start_date, a.end_date";
	
	$notification_list = find('all', $table, $fields, $where_clause . " ORDER BY n.sent_at DESC LIMIT " . $offset . "," . $record_no, $execute);
	
	// Process notifications for display (one record per appointment now)
	$grouped_notifications = array();
	foreach($notification_list as $notification) {
		$appointment_id = $notification['appointment_id'];
		$grouped_notifications[$appointment_id] = array(
			'customer_name' => $notification['customer_name'],
			'subject' => $notification['subject'],
			'start_date' => $notification['start_date'],
			'end_date' => $notification['end_date'],
			'email_status' => ($notification['is_email_success'] == 1) ? 'Erfolg' : (($notification['is_email_success'] === null || $notification['is_email_success'] === '') ? 'N/A' : 'Fehlgeschlagen'),
			'sms_status' => ($notification['is_sms_success'] == 1) ? 'Erfolg' : (($notification['is_sms_success'] === null || $notification['is_sms_success'] === '') ? 'N/A' : 'Fehlgeschlagen'),
			'latest_timestamp' => $notification['sent_at']
		);
	}
	
	$page_link = LINKPERPAGE;
	$mid_link = ceil($page_link/2);
	if($page_link%2==0)
	{
		$st_link = $mid_link;
		$end_link = $mid_link-1;
	}
	else
	{
		$st_link = $mid_link-1;
		$end_link = $mid_link-1;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#notification_search").validationEngine();
		});
	//-->
	</script>
</head>
<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title">Benachrichtigungsverlauf</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px">
					  <form method="post" action="" id="notification_search" name="notification_search">
							<input class="form-control search" id="focusedInput" placeholder="Suche nach Kundenname oder Betreff" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
						</form>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>Kunde</th>
								<th>Betreff</th>
								<th>Termindatum</th>
								<th>E-Mail Status</th>
								<th>SMS Status</th>
								<th>Gesendet am</th>
							</tr>
						</thead>
						<tbody>
						<?php
							if(!empty($grouped_notifications))
							{
								foreach($grouped_notifications as $appointment_id => $notification):
						?>
							<tr>
								<td><?php echo htmlspecialchars($notification['customer_name']);?></td>
								<td><?php echo htmlspecialchars($notification['subject']);?></td>
								<td><?php echo change_date_format($notification['start_date']);?></td>
								<td>
									<?php 
										if($notification['email_status'] == 'Erfolg') {
											echo '<span style="color: green;">✓ ' . $notification['email_status'] . '</span>';
										} else if($notification['email_status'] == 'Fehlgeschlagen') {
											echo '<span style="color: red;">✗ ' . $notification['email_status'] . '</span>';
										} else {
											echo $notification['email_status'];
										}
									?>
								</td>
								<td>
									<?php 
										if($notification['sms_status'] == 'Erfolg') {
											echo '<span style="color: green;">✓ ' . $notification['sms_status'] . '</span>';
										} else if($notification['sms_status'] == 'Fehlgeschlagen') {
											echo '<span style="color: red;">✗ ' . $notification['sms_status'] . '</span>';
										} else {
											echo $notification['sms_status'];
										}
									?>
								</td>
								<td><?php echo change_date_format($notification['latest_timestamp']);?></td>
							</tr>
						<?php
								endforeach;
							}
							else
							{
						?>
							<tr>
								<td colspan="6" class="no_record_cls">Kein Eintrag gefunden</td>
							</tr>
						<?php
							}
						?>
						</tbody>
					</table>
					<?php
						if($total_result>1)
						{
					?>
						<ul class="pagination pagination-sm" style="float:right; margin-right:5px; margin-top:0px">
						<?php
							if($page>1)
							{
						?>
							<li><a href="notification.php?page=<?php echo($page-1);?><?php echo(isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? "&search_name=".$_SESSION['search_name'] : "");?>">&laquo;</a></li>
						<?php
							}
							if($no_of_page<=$page_link)
							{
								$st_link=1;
								$end_link=$no_of_page;
							}
							else
							{
								if($page<=$st_link)
								{
									$st_link=1;
									$end_link=$page_link;
								}
								else if($page+$end_link>=$no_of_page)
								{
									$st_link=$no_of_page-$page_link+1;
									$end_link=$no_of_page;
								}
								else
								{
									$st_link=$page-$st_link;
									$end_link=$page+$end_link;
								}
							}
							for($i=$st_link;$i<=$end_link;$i++)
							{
						?>
							<li <?php echo($page==$i ? "class='active'" : "");?>><a href="notification.php?page=<?php echo $i;?><?php echo(isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? "&search_name=".$_SESSION['search_name'] : "");?>"><?php echo $i;?></a></li>
						<?php
							}
							if($page<$no_of_page)
							{
						?>
							<li><a href="notification.php?page=<?php echo($page+1);?><?php echo(isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? "&search_name=".$_SESSION['search_name'] : "");?>">&raquo;</a></li>
						<?php
							}
						?>
						</ul>
					<?php
						}
					?>
			  </div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>
