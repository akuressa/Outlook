<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(!isset($_GET['page']) && !isset($_POST['search_month']))
	{
		unset($_SESSION['search_month']);
	}
	$where_clause="WHERE :no ";
	$execute=array(':no'=>0);
	if(isset($_POST['search_month']) && $_POST['search_month']!="")
	{
		if(isset($_GET['page']))
		{
			unset($_GET['page']);
		}
		if(isset($_SESSION['search_month']))
		{
			unset($_SESSION['search_month']);
		}
		$where_clause=" WHERE date <= DATE_FORMAT((NOW()-Interval ".$_POST['search_month']." Month), '%Y-%m-%d') ";
		$execute=array();
		$_SESSION['search_month']=$_POST['search_month'];
	}
	else if(isset($_SESSION['search_month']) && $_SESSION['search_month']!="")
	{
		$where_clause=" WHERE date <= DATE_FORMAT((NOW()-Interval ".$_SESSION['search_month']." Month), '%Y-%m-%d') ";
		$execute=array();
	}
	$where_clause=$where_clause;
	$table="(SELECT * FROM (SELECT * FROM ".USER_MEDICAL_LOG." ORDER BY date DESC) as temp GROUP BY user_id) as req";
	//$table='(SELECT * FROM '.USER_MEDICAL_LOG.' ORDER BY id DESC) as temp';
	$count_result = find("first", $table, "count(id) as total_count", $where_clause, $execute);
	//print_r($count_result);
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
	$fields="*";
	$user_list = find('all', $table, $fields, $where_clause." LIMIT ".$offset.",".$record_no, $execute);
	//print_r($user_list);
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
	//SELECT * FROM (SELECT * FROM user_medical_log ORDER BY id DESC) as temp WHERE date < '2015-12-05' GROUP BY user_id
	//SELECT * FROM (SELECT * FROM user_medical_log ORDER BY id DESC) as temp WHERE date < ('2016-02-09'-Interval 2 Month) GROUP BY user_id
	//SELECT count(id) as total_count FROM (SELECT * FROM user_medical_log ORDER BY id DESC) as temp WHERE date <  (NOW()-Interval 3 Month) GROUP BY user_id
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
</head>
<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title">Listen von Logs</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px">
					  <form method="post" action="" id="name_search" name="name_search">
							<div class="form-group col-md-6" style="padding:6px">
							  <label><font color = "red">*</font> Monat Dauer:</label>
							  <select class="form-control validate[required]" name="search_month" id="search_month" data-errormessage-value-missing="Please select month duration" tabindex="1">
								<option value = "" <?php echo((isset($_POST['search_month']) && $_POST['search_month']=='') ? "selected='selected'" : (isset($_SESSION['search_month']) && $_SESSION['search_month']=='' ? "selected='selected'" : ""));?>>Select</option>
								<?php
									for($i=1;$i<=12;$i++)
									{
								?>
								<option value = "<?php echo $i;?>" <?php echo(isset($_POST['search_month']) && $_POST['search_month']==$i ? "selected='selected'" : (isset($_SESSION['search_month']) && $_SESSION['search_month']==$i ? "selected='selected'" : ""));?>><?php echo $i;?></option>
								<?php
									}
								?>
							  </select>
							</div>
							<div class="form-group col-md-6">
							  <button class="btn btn-primary button" name = "search_btn" tabindex="2" style="margin-top:30px;"><b>Suche</b></button>
							</div>
							<div class="clearfix"></div>
						</form>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>Name <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Email  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Telefon</th>
								<th>zugeordnet Zweig <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Letzte Logs</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody> 
					<?php
						if(!empty($user_list))
						{
							foreach($user_list as $user_key=>$user_val)
							{
								$table_each=USERS." as u1 LEFT JOIN ".USERS." as u2 ON u1.parent_id=u2.id ";
								$fields_each="u1.id, u2.branch_name, u1.person_name, u1.initial, u1.email_address, u1.phone_no_1, u1.status";
								$find_user_details = find('first', $table_each, $fields_each, "WHERE u1.id=:user_id ", array(':user_id'=>$user_val['user_id']));

								if(empty($find_user_details))
								{
									continue;
								}
					?>
							 <tr>
								<td><?php echo $find_user_details['initial']." ".$find_user_details['person_name'];?></td>
								<td><?php echo $find_user_details['email_address'];?></td>
								<td><?php echo $find_user_details['phone_no_1'];?></td>
								<td><?php echo $find_user_details['branch_name'];?></td>
								<td><?php echo change_date_format($user_val['date']);?></td>
								<td>
									<?php
										if($find_user_details['status']=='Y')
										{
									?>
										<font color = "green"><b>Aktiv</b></font>
									<?php
										}
										else if($find_user_details['status']=='N')
										{
									?>
										<font color = "red"><b>Inaktiv</b></font>
									<?php
										}
									?>
								</td>
							</tr>
					<?php
							}
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
							<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."log_history.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($no_of_page < $page_link)
							{
								for($l=1; $l<=$no_of_page; $l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."log_history.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page>($no_of_page-$mid_link))
							{
								for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."log_history.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page >$mid_link)
							{ 
								for($l=$page-$st_link;$l<=$page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."log_history.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."log_history.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."log_history.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
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