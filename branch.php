<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$user_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$find_user= find('first', USERS, 'id, picture', "WHERE id=:id", array(':id'=>$user_id));
		if(!empty($find_user))
		{
			$del_rcd=delete(USERS, 'WHERE id=:id', array(':id'=>$user_id));
			if($del_rcd==true)
			{
				if($find_user['picture']!='' && file_exists('img/branch_image/'.$find_user['picture']))
				{
					unlink('img/branch_image/'.$find_user['picture']);
				}
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Niederlassung erfolgreich gel&Ouml;scht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Zweig ID.';
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	if(isset($_GET['login_del_id']) && $_GET['login_del_id']!='')
	{
		$user_id=substr(base64_decode($_GET['login_del_id']), 0, -5);
		$find_user= find('first', USERS, 'id, picture', "WHERE id=:id", array(':id'=>$user_id));
		if(!empty($find_user))
		{
			$del_rcd=delete(USERS, 'WHERE id=:id', array(':id'=>$user_id));
			if($del_rcd==true)
			{
				if($find_user['picture']!='' && file_exists('img/branch_image/'.$find_user['picture']))
				{
					unlink('img/branch_image/'.$find_user['picture']);
				}
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Zweig Login erfolgreich gel&ouml;scht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Zweig Login-ID.';
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	if(!isset($_GET['page']))
	{
		unset($_SESSION['search_name']);
	}
	$where_clause="WHERE user_type=:user_type ";
	$execute=array(':user_type'=>'B');
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
		$where_clause.=" AND branch_name LIKE :branch_name";
		$execute=array_merge($execute, array(':branch_name'=>'%'.$_POST['search_name'].'%'));
		$_SESSION['search_name']=$_POST['search_name'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		$where_clause.=" AND branch_name LIKE :branch_name";
		$execute=array_merge($execute, array(':branch_name'=>'%'.$_SESSION['search_name'].'%'));
	}
	$count_result = find("first", USERS, "count(id) as total_count", $where_clause, $execute);
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
	$fields="id, branch_name, person_name, email_address, phone_no_1, status";
	$branch_list = find('all', USERS, $fields, $where_clause." ORDER BY id DESC LIMIT ".$offset.",".$record_no, $execute);
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
		function delete_record(id)
		{
			if(confirm('Are you sure you wish to delete this record?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>branch.php?del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}

		function set_status(id, cur)
		{
			$.post("<?php echo(DOMAIN_NAME_PATH);?>set_branch_status.php?id="+id,function(data){
				if(data!="")
				{
					if(data=='no' || data=='yes')
					{
						if(data=='yes')
						{
							showSuccess('Niederlassung erfolgreich aktiviert wurde.');
							cur.attr('title', 'Click to inactive');
							cur.html('<font color = "green"><b>ACTIVE</b></font>');
						}
						else
						{
							showSuccess('Niederlassung erfolgreich deaktiviert.');
							cur.attr('title', 'Click to active');
							cur.html('<font color = "red"><b>INACTIVE</b></font>');
						}
					}
					else if(data=='error')
					{
						showError('Wir sind mit ein Problem. Bitte versuch es sp\xC4ter.');
					}
					else if(data=='error1')
					{
						showError('Ung\xDCltige Zweig ID.');
					}
				}
			});
		}
		function delete_record_login(id)
		{
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz wirklich l\xD6schen?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>branch.php?login_del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}

		function set_status_login(id, cur)
		{
			$.post("<?php echo(DOMAIN_NAME_PATH);?>set_branch_status.php?id="+id,function(data){
				if(data!="")
				{
					if(data=='no' || data=='yes')
					{
						if(data=='yes')
						{
							showSuccess('Zweig Login erfolgreich aktiviert wurde.');
							cur.attr('title', 'Click to inaktiv');
							cur.html('<font color = "green"><b>AKTIV</b></font>');
						}
						else
						{
							showSuccess('Branch login has been disabled successfully.');
							cur.attr('title', 'Click to aktiv');
							cur.html('<font color = "red"><b>INAKTIV</b></font>');
						}
					}
					else if(data=='error')
					{
						showError('Wir sind mit ein Problem. Bitte versuch es sp\xE4ter.');
					}
					else if(data=='error1')
					{
						showError('Ung\xDCltige Zweig ID.');
					}
				}
			});
		}
	//-->
	</script>
</head>
<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title">Liste der Niederlassungen</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px">
						<form method="post" action="" id="branch_search" name="branch_search">
							<input class="form-control search" id="focusedInput" placeholder="Suche" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
						</form>
					</div>
					<div class="form-group" style="padding:3px;text-align:right;">
					  <a href = "create_branch.php"><button class="button" name = "btn_create"><b>Neue Filiale erslellen</b></button></a>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>Bezeichnung der Filiale<span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<!-- <th>Authorized Person <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th> -->
								<th>Email  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Telefon</th>
								<th>Status</th>
								<th style="text-align:center">Aktion</th>
							</tr>
						</thead>
						<tbody>
					<?php
						if(!empty($branch_list))
						{
							foreach($branch_list as $branch_key=>$branch_val)
							{
					?>
							<tr>
								<td><?php echo $branch_val['branch_name'];?></td>
								<!-- <td><?php //echo $branch_val['person_name'];?></td> -->
								<td><?php echo $branch_val['email_address'];?></td>
								<td><?php echo $branch_val['phone_no_1'];?></td>
								<td>
									<?php
										if($branch_val['status']=='Y')
										{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($branch_val['id'].IDHASH));?>', $(this));" title="Click to inaktiv"><font color = "green"><b>AKTIV</b></font></a>
									<?php
										}
										else if($branch_val['status']=='N')
										{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($branch_val['id'].IDHASH));?>', $(this));" title="Click to aktive"><font color = "red"><b>INAKTIV</b></font></a>
									<?php
										}
									?>
								</td>
								<td style="text-align:center">
									<a href="edit_branch.php?branch_id=<?php echo base64_encode($branch_val['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Zweig bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;
									<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($branch_val['id'].IDHASH));?>');" title="Zweig l&ouml;schen"><span class="glyphicon glyphicon-trash"></span></a>&nbsp;&nbsp;<a href="add_branch_login.php?branch_id=<?php echo base64_encode($branch_val['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Add New Anmelden"><span class="glyphicon glyphicon-plus"></span></a>
								</td>
							</tr>
					<?php
								$find_other_login=find('all', USERS, 'id, email_address, user_type, person_name, parent_id, status', "WHERE parent_id=:id AND user_type=:user_type", array(':id'=>$branch_val['id'], ':user_type'=>'E'));
								if(!empty($find_other_login))
								{
									foreach($find_other_login as $login_key_value)
									{
					?>
										<tr>
											<td><b>Andere <?php echo $branch_val['branch_name'];?> Login-Daten: </b></td>
											<!-- <td>&nbsp;</td> -->
											<td><?php echo $login_key_value['email_address'];?></td>
											<td>&nbsp;</td>
											<td>
												<?php
													if($login_key_value['status']=='Y')
													{
												?>
													<a href="javascript:void(0)" onclick = "set_status_login('<?php echo(base64_encode($login_key_value['id'].IDHASH));?>', $(this));" title="Klicken Sie auf inaktiv"><font color = "green"><b>AKTIV</b></font></a>
												<?php
													}
													else if($login_key_value['status']=='N')
													{
												?>
													<a href="javascript:void(0)" onclick = "set_status_login('<?php echo(base64_encode($login_key_value['id'].IDHASH));?>', $(this));" title="Klicken Sie auf das aktive"><font color = "red"><b>INAKTIV</b></font></a>
												<?php
													}
												?>
											</td>
											<td style="text-align:center">
												<a href="edit_branch_login.php?employee_id=<?php echo base64_encode($login_key_value['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Niederlassung &auml;ndern Anmelden"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;
												<a href="javascript:void(0)" onclick = "delete_record_login('<?php echo(base64_encode($login_key_value['id'].IDHASH));?>');" title="L&ouml;schen Niederlassung Anmelden"><span class="glyphicon glyphicon-trash"></span></a>
											</td>
										</tr>
					<?php
									}
								}
							}
						}
						else
						{
					?>
							<tr>
								<td colspan="5" class="no_record_cls">Kein Eintrag gefunden</td>
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
							<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."branch.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($no_of_page < $page_link)
							{
								for($l=1; $l<=$no_of_page; $l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."branch.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page>($no_of_page-$mid_link))
							{
								for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."branch.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page >$mid_link)
							{ 
								for($l=$page-$st_link;$l<=$page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."branch.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."branch.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."branch.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
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