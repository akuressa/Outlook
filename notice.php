<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$branch_where="";$branch_execute=array();
		if($branch_privilege==true)
		{
			$branch_where=" AND brunch_id=:brunch_id OR brunch_id=:brunch_id_1";
			$branch_execute=array(":brunch_id"=>$_SESSION['logged_user_id'], ":brunch_id_1"=>0);
		}
		$notice_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$execute=array(':id'=>$notice_id);
		$execute=array_merge($execute, $branch_execute);
		$find_notice= find('first', NOTICE_BOARD, '*', "WHERE id=:id".$branch_where, $execute);
		if(!empty($find_notice))
		{
			$del_rcd=delete(NOTICE_BOARD, 'WHERE id=:id', array(':id'=>$notice_id));
			if($del_rcd==true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Bemerken erfolgreich gel&ouml;scht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'notice.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Bemerken id.';
			header('location:'.DOMAIN_NAME_PATH.'notice.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	if(!isset($_GET['page']))
	{
		unset($_SESSION['search_name']);
	}
	if($admin_privilege==true)
	{
		$where_clause="WHERE :all ";
		$execute=array(':all'=>1);
	}
	else if($branch_privilege==true)
	{
		$where_clause="WHERE brunch_id=:brunch_id OR brunch_id=:brunch_id_1 ";
		$execute=array(':brunch_id'=>$_SESSION['logged_user_id'], ':brunch_id_1'=>0);
	}
	else if($user_privilege==true)
	{
		$where_clause="WHERE brunch_id=:brunch_id OR brunch_id=:brunch_id_1 ";
		$execute=array(':brunch_id'=>$_SESSION['logged_parent_id'], ':brunch_id_1'=>0);
	}
	else
	{
		$where_clause="WHERE :no ";
		$execute=array(':no'=>0);
	}
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
		$where_clause.=" AND heading LIKE :heading";
		$execute=array_merge($execute, array(':heading'=>'%'.$_POST['search_name'].'%'));
		$_SESSION['search_name']=$_POST['search_name'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		$where_clause.=" AND heading LIKE :heading";
		$execute=array_merge($execute, array(':heading'=>'%'.$_SESSION['search_name'].'%'));
	}
	$tables=NOTICE_BOARD." as nb LEFT JOIN ".USERS." as u ON nb.brunch_id=u.id ";
	$count_result = find("first", $tables, "count(nb.id) as total_count", $where_clause , $execute);
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
	$fields="nb.id as nb_id, nb.heading, nb.content, nb.brunch_id, nb.start_date, nb.end_date, nb.status, u.branch_name";
	$notice_list = find('all', $tables, $fields, $where_clause." ORDER BY nb.id DESC LIMIT ".$offset.",".$record_no, $execute);//print_r($notice_list);
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
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz gel\xF6scht werden soll?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>notice.php?del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}

		function set_status(id, cur)
		{
			$.post("<?php echo(DOMAIN_NAME_PATH);?>set_notice_status.php?id="+id,function(data){
				if(data!="")
				{
					if(data=='no' || data=='yes')
					{
						if(data=='yes')
						{
							showSuccess('Bemerken erfolgreich aktiviert wurde.');
							cur.attr('title', 'Click to inaktiv');
							cur.html('<font color = "green"><b>Aktiv</b></font>');
						}
						else
						{
							showSuccess('Bemerken erfolgreich deaktiviert wurde.');
							cur.attr('title', 'Click to aktiv');
							cur.html('<font color = "red"><b>Inaktiv</b></font>');
						}
					}
					else if(data=='error')
					{
						showError('Wir sind mit ein Problem. Bitte versuch es sp\xE4ter.');
					}
					else if(data=='error1')
					{
						showError('Ung\xFCltige Bemerken id.');
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
				<h3 class="panel-title">Listen der Bemerken</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px">
					  <form method="post" action="" id="notice_search" name="notice_search">
							<input class="form-control search" id="focusedInput" placeholder="Suche" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
						</form>
					</div>
					<?php
						if($user_privilege!=true)
						{
					?>
					<div class="form-group" style="padding:3px;text-align:right;">
					  <a href = "create_notice.php"><button class="button" name = "btn_create"><b>Neu erstellen und Datenschutz</b></button></a>
					</div>
					<?php
						}
					?>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>&uuml;berschrift</th>
								<th>inhalt</th>
								<th>Zweigname<span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Startdatum  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Schlussdatum  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Status</th>
								<?php
									if($admin_privilege==true)
									{
								?>
								<th style="text-align:center">Aktion</th>
								<?php
									}
								?>
							</tr>
						</thead>
						<tbody>
						<?php
							if(!empty($notice_list))
							{
								foreach($notice_list as $notice_key=>$notice_val)
								{
						?>
							<tr>
								<td><?php echo $notice_val['heading'];?></td>
								<td><?php echo $notice_val['content'];?></td>
								<td><?php echo($notice_val['branch_name']=="" ? "All" : $notice_val['branch_name']);?></td>
								<td><?php echo change_date_format($notice_val['start_date']);?></td>
								<td><?php echo change_date_format($notice_val['end_date']);?></td>
								<td>
									<?php
										if($notice_val['status']=='Y')
										{
									?>
									<?php
											if($admin_privilege==true)
											{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($notice_val['nb_id'].IDHASH));?>', $(this));" title="Click to inaktiv">
									<?php
											}
									?>
											<font color = "green"><b>Aktiv</b></font>
									<?php
											if($admin_privilege==true)
											{
									?>
										</a>
									<?php
											}
									?>
									<?php
										}
										else if($notice_val['status']=='N')
										{
									?>
									<?php
											if($admin_privilege==true)
											{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($notice_val['nb_id'].IDHASH));?>', $(this));" title="Click to aktiv">
									<?php
											}
									?>
											<font color = "red"><b>Inaktiv</b></font>
									<?php
											if($admin_privilege==true)
											{
									?>
										</a>
									<?php
											}
									?>
									<?php
										}
									?>
								</td>
								<?php
									if($admin_privilege==true)
									{
								?>
								<td style="text-align:center">
									<a href="edit_notice.php?notice_id=<?php echo base64_encode($notice_val['nb_id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Bemerken bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;
									<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($notice_val['nb_id'].IDHASH));?>');" title="Bemerken l&ouml;schen"><span class="glyphicon glyphicon-trash"></span></a>
								</td>
								<?php
									}
								?>
							</tr>
						<?php
								}
							}
							else
							{
						?>
								<tr>
									<td colspan="<?php echo($admin_privilege==true ? "7" : "6");?>" class="no_record_cls">Kein Eintrag gefunden</td>
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
							<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."notice.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($no_of_page < $page_link)
							{
								for($l=1; $l<=$no_of_page; $l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."notice.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page>($no_of_page-$mid_link))
							{
								for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."notice.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page >$mid_link)
							{ 
								for($l=$page-$st_link;$l<=$page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."notice.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."notice.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."notice.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
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