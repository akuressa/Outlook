<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$branch_list = find('all', USERS, "id, branch_name", "WHERE user_type=:user_type AND status=:status ORDER BY branch_name ASC", array(':user_type'=>'B', ':status'=>'Y'));
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$branch_where="";$branch_execute=array();
		if($branch_privilege==true)
		{
			$branch_where=" AND parent_id=:parent_id";
			$branch_execute=array(":parent_id"=>$_SESSION['logged_user_id']);
		}
		$user_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$execute=array(':id'=>$user_id);
		$execute=array_merge($execute, $branch_execute);
		$find_user= find('first', USERS, 'id, picture', "WHERE id=:id".$branch_where, $execute);
		if(!empty($find_user))
		{
			$del_rcd=delete(USERS, 'WHERE id=:id', array(':id'=>$user_id));
			if($del_rcd==true)
			{
				if($find_user['picture']!='' && file_exists('img/user_image/'.$find_user['picture']))
				{
					unlink('img/user_image/'.$find_user['picture']);
				}
				$delete_user_notice=delete(USER_NOTICE, 'WHERE user_id=:user_id', array(':user_id'=>$user_id));
				$delete_user_medical_log=delete(USER_MEDICAL_LOG, 'WHERE user_id=:user_id', array(':user_id'=>$user_id));
				$find_user_documents=find('all', DOCUMENTS, '*', "WHERE user_id=:user_id ORDER BY id ASC", array(':user_id'=>$user_id));
				if(!empty($find_user_documents))
				{
					foreach($find_user_documents as $doc_key=>$doc_val)
					{
						if($doc_val['modified_name']!='' && file_exists('img/user_image/'.$doc_val['modified_name']))
						{
							unlink('img/user_image/'.$doc_val['modified_name']);
						}
					}
					$delete_user_documents=delete(DOCUMENTS, 'WHERE user_id=:user_id', array(':user_id'=>$user_id));
				}
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Benutzer erfolgreich gel&ouml;scht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'listing.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Benutzer-id.';
			header('location:'.DOMAIN_NAME_PATH.'listing.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	if(!isset($_GET['page']) && !isset($_POST['search_name']) && !isset($_POST['search_branch']))
	{
		unset($_SESSION['search_name']);
		unset($_SESSION['search_branch']);
	}
	if($admin_privilege==true)
	{
		$where_clause="WHERE u1.user_type=:user_type ";
		$execute=array(':user_type'=>'U');
	}
	else if($branch_privilege==true)
	{
		$where_clause="WHERE u1.user_type=:user_type AND u1.parent_id=:parent_id_1";
		$execute=array(':user_type'=>'U', ':parent_id_1'=>$_SESSION['logged_user_id']);
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
		if(isset($_SESSION['search_branch']) && $_SESSION['search_branch']!="")
		{
			if($_SESSION['search_branch']!="All")
			{
				$where_clause.=" AND u1.parent_id=:parent_id ";
				$execute=array_merge($execute, array(':parent_id'=>$_SESSION['search_branch']));
			}
		}
		$where_clause.=" AND (u1.person_name LIKE :person_name OR u1.email_address LIKE :email_address OR u1.phone_no_1 LIKE :phone_no_1 OR u1.phone_no_2 LIKE :phone_no_2) ";
		$execute=array_merge($execute, array(':person_name'=>'%'.stripcleantohtml($_POST['search_name']).'%', ':email_address'=>'%'.stripcleantohtml($_POST['search_name']).'%', ':phone_no_1'=>'%'.stripcleantohtml($_POST['search_name']).'%', ':phone_no_2'=>'%'.stripcleantohtml($_POST['search_name']).'%'));
		$_SESSION['search_name']=$_POST['search_name'];
	}
	else if(isset($_POST['search_branch']) && $_POST['search_branch']!="")
	{
		if(isset($_GET['page']))
		{
			unset($_GET['page']);
		}
		if(isset($_SESSION['search_branch']))
		{
			unset($_SESSION['search_branch']);
		}
		if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
		{
			$where_clause.=" AND u1.person_name LIKE :person_name";
			$execute=array_merge($execute, array(':person_name'=>'%'.stripcleantohtml($_SESSION['search_name']).'%'));
		}
		if($_POST['search_branch']!="All")
		{
			$where_clause.=" AND u1.parent_id=:parent_id ";
			$execute=array_merge($execute, array(':parent_id'=>$_POST['search_branch']));
		}
		$_SESSION['search_branch']=$_POST['search_branch'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		if(isset($_SESSION['search_branch']) && $_SESSION['search_branch']!="")
		{
			if($_SESSION['search_branch']!="All")
			{
				$where_clause.=" AND u1.parent_id=:parent_id ";
				$execute=array_merge($execute, array(':parent_id'=>$_SESSION['search_branch']));
			}
		}
		$where_clause.=" AND (u1.person_name LIKE :person_name OR u1.email_address LIKE :email_address OR u1.phone_no_1 LIKE :phone_no_1 OR u1.phone_no_2 LIKE :phone_no_2) ";
		$execute=array_merge($execute, array(':person_name'=>'%'.stripcleantohtml($_SESSION['search_name']).'%', ':email_address'=>'%'.stripcleantohtml($_SESSION['search_name']).'%', ':phone_no_1'=>'%'.stripcleantohtml($_SESSION['search_name']).'%', ':phone_no_2'=>'%'.stripcleantohtml($_SESSION['search_name']).'%'));
	}
	else if(isset($_SESSION['search_branch']) && $_SESSION['search_branch']!="")
	{
		if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
		{
			$where_clause.=" AND u1.person_name LIKE :person_name";
			$execute=array_merge($execute, array(':person_name'=>'%'.stripcleantohtml($_SESSION['search_name']).'%'));
		}
		if($_SESSION['search_branch']!="All")
		{
			$where_clause.=" AND u1.parent_id=:parent_id ON ";
			$execute=array_merge($execute, array(':parent_id'=>$_SESSION['search_branch']));
		}
	}
	$table=USERS." as u1 LEFT JOIN ".USERS." as u2 ON u1.parent_id=u2.id ";
	$count_result = find("first", $table, "count(u1.id) as total_count", $where_clause, $execute);
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
	$fields="u1.id, u2.branch_name, u1.person_name, u1.initial, u1.email_address, u1.phone_no_1, u1.status";
	$user_list = find('all', $table, $fields, $where_clause." ORDER BY u1.id DESC LIMIT ".$offset.",".$record_no, $execute);
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
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>listing.php?del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}

		function set_status(id, cur)
		{
			$.post("<?php echo(DOMAIN_NAME_PATH);?>set_user_status.php?id="+id,function(data){
				if(data!="")
				{
					if(data=='no' || data=='yes')
					{
						if(data=='yes')
						{
							showSuccess('Benutzer erfolgreich aktiviert wurde.');
							cur.attr('title', 'Click to inaktiv');
							cur.html('<font color = "green"><b>Aktiv</b></font>');
						}
						else
						{
							showSuccess('Benutzer erfolgreich deaktiviert wurde.');
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
						showError('Ung\xFCltige hinweis id.');
					}
				}
			});
		}
		$(function(){
			$("#focusedInput").keyup(function(){
				$("#focusedInput").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_user_name.php",
					//dataType: "json",
					data: {
						person_name:$("#focusedInput").val()
					},
					success: function( data ) {
						$("#auto_sug_content_div").show();
						$("#auto_sug_content_div").html( data );
						$("#focusedInput").removeClass('person_name_rotate');

					},
					error: function(){
					}
				});
			});
		});
		function value_send(value)
		{
			$("#focusedInput").val(value);
			$("#auto_sug_content_div").hide();
			$("#focusedInput").focus();
		}
	//-->
	</script>
	<style type="text/css">
		.person_name_rotate{ background:#F7F7F7 url('img/ajax-loader.gif') right center no-repeat !important; }
		.auto_sugg_cls{max-height:165px;overflow:auto;width:97%;top:65px;position:absolute;border: 1px solid rgb(204, 204, 204);border-radius:2px;background:#FFFFFF;display:none;height:auto;z-index:99999999;}
		.data_row{padding:5px;font-size:16px;cursor:pointer;}
		.data_row:hover{background:rgb(227, 239, 255);}
		.no_data_row{padding:5px;font-size:16px;color:red;text-align:center;}
	</style>
</head>
<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title">Listen der Kontakte</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px">
					  <form method="post" action="" id="name_search" name="name_search" autocomplete="off">
							<input class="form-control search" id="focusedInput" placeholder="Suche nach Name / E-Mail / Telefon" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
						</form>
						<div class="auto_sugg_cls" id="auto_sug_content_div"></div>
					</div>
					<div class="form-group" style="padding:3px">
						<div class="col-md-6">
							Filter nach zweig: 
							<form method="post" action="" id="filter_branch" name="filter_branch" style="display: inline-block;">
								<select name="search_branch" id="search_branch" onchange="$('#filter_branch').submit();">
									<option value = "All" <?php echo((isset($_POST['search_branch']) && $_POST['search_branch']=='All') ? "selected='selected'" : (isset($_SESSION['search_branch']) && $_SESSION['search_branch']=='All' ? "selected='selected'" : ""));?>>All</option>
									<?php
										if(!empty($branch_list))
										{
											foreach($branch_list as $branch_key=>$branch_value)
											{
									?>
									<option value = "<?php echo $branch_value['id'];?>" <?php echo(isset($_POST['search_branch']) && $_POST['search_branch']==$branch_value['id'] ? "selected='selected'" : (isset($_SESSION['search_branch']) && $_SESSION['search_branch']==$branch_value['id'] ? "selected='selected'" : ""));?>><?php echo $branch_value['branch_name'];?></option>
									<?php
											}
										}
									?>
								 </select>
							</form>
						</div>
						<div class="col-md-6" style = "text-align:right;">
							<a href = "create_user.php"><button class="button" name = "btn_create"><b>Neuen Kontakt erstellen</b></button></a>
						</div>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th style="text-align:center">Bearbeiten</th>
								<th>Vorname Nachname <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Kennzeichen  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Telefon</th>
								<th>zugeordnet Zweig  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody> 
					<?php
						if(!empty($user_list))
						{
							foreach($user_list as $user_key=>$user_val)
							{
								$user_notice="";
								$find_user_notice=find('all', USER_NOTICE, '*', "WHERE user_id=:user_id ORDER BY date DESC", array(':user_id'=>$user_val['id']));
								if(!empty($find_user_notice))
								{
									$sl_no=0;
									foreach($find_user_notice as $notice_key=>$notice_val)
									{
										$sl_no=$sl_no+1;
										$user_notice.="".$sl_no.". ".$notice_val['notice']." - <strong>Date:</strong> ".change_date_format($notice_val['date'])."<br/>";
									}
								}
								$user_medical_log="";
								$find_user_medical_log=find('all', USER_MEDICAL_LOG, '*', "WHERE user_id=:user_id ORDER BY date DESC", array(':user_id'=>$user_val['id']));
								if(!empty($find_user_medical_log))
								{
									$sl_no=0;
									foreach($find_user_medical_log as $medical_key=>$medical_val)
									{
										$sl_no=$sl_no+1;
										$user_medical_log.="".$sl_no.". ".$medical_val['medical_log']." - <strong>Date:</strong> ".change_date_format($medical_val['date'])."<br/>";
									}
								}
								$user_call_note="";
								$find_user_call_note=find('all', CALL_BACK, '*', "WHERE user_id=:user_id ORDER BY id ASC", array(':user_id'=>$user_val['id']));
								if(!empty($find_user_call_note))
								{
									$sl_no=0;
									foreach($find_user_call_note as $call_key=>$call_val)
									{
										$call_date_time=change_date_time_format($call_val['call_date'], "Y-m-d H:i:s");
										$next_call_date_time=change_date_time_format($call_val['next_call_date'], "Y-m-d H:i:s");
										$sl_no=$sl_no+1;
										$user_call_note.="".$sl_no.". ".$call_val['description']." <br/> <strong>Anrufdatum:</strong> ".$call_date_time." <br/> <strong>Nächstes Rückrufdatum:</strong> ".$next_call_date_time."<br/><br/>";
									}
								}
					?>
							 <tr>
								<td style="text-align:center">
									<a href="edit_contact.php?contact_id=<?php echo base64_encode($user_val['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Benutzer bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>
								</td>
								<td><?php echo $user_val['initial']." ".ucwords($user_val['person_name']);?></td>
								<td><?php echo $user_val['email_address'];?></td>
								<td><?php echo $user_val['phone_no_1'];?></td>
								<td><?php echo $user_val['branch_name'];?></td>
								<td>
									<?php
										if($user_val['status']=='Y')
										{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($user_val['id'].IDHASH));?>', $(this));" title="Click to inaktiv"><font color = "green"><b>Aktiv</b></font></a>
									<?php
										}
										else if($user_val['status']=='N')
										{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($user_val['id'].IDHASH));?>', $(this));" title="Click to aktiv"><font color = "red"><b>Inaktiv</b></font></a>
									<?php
										}
									?>
								</td>
								<td style="text-align:center">
									<?php
										if($admin_privilege==true)
										{
									?>&nbsp;&nbsp;<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($user_val['id'].IDHASH));?>');" title="Benutzer l&ouml;schen"><span class="glyphicon glyphicon-trash"></span></a>
									<?php
										}
									?>
									&nbsp;&nbsp;
									<?php if($user_notice!=""){ ?>
									<a href="javascript:void(0)" data-toggle="popover" title="Bemerken" data-content="<?php echo $user_notice;?>" data-placement="bottom" data-html="true"><span class="glyphicon glyphicon-list-alt"></span></a>
									<?php }?>
									&nbsp;&nbsp;
									<?php if($user_medical_log!=""){ ?>
									<a href="javascript:void(0)" data-toggle="popover" title="Behandlungsprotokoll" data-content="<?php echo $user_medical_log;?>" data-placement="bottom" data-html="true"><span class="glyphicon glyphicon-th"></span></a>
									<?php }?>
									&nbsp;&nbsp;
									<?php if($user_call_note!=""){ ?>
									<a href="javascript:void(0)" data-toggle="popover" title="Anrufsnotiz" data-content="<?php echo $user_call_note;?>" data-placement="bottom" data-html="true"><span class="glyphicon glyphicon-earphone"></span></a>
									<?php }?>
								</td>
							</tr>
					<?php
							}
						}
						else
						{
					?>
							<tr>
								<td colspan="7" class="no_record_cls">Kein Eintrag gefunden</td>
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
							<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."listing.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($no_of_page < $page_link)
							{
								for($l=1; $l<=$no_of_page; $l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."listing.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page>($no_of_page-$mid_link))
							{
								for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."listing.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page >$mid_link)
							{ 
								for($l=$page-$st_link;$l<=$page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."listing.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."listing.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."listing.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
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