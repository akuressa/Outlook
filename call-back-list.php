<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$branch_list = find('all', USERS, "id, branch_name", "WHERE user_type=:user_type AND status=:status ORDER BY branch_name ASC", array(':user_type'=>'B', ':status'=>'Y'));
	if(!isset($_GET['top_page']) && !isset($_POST['top_search_name']) && !isset($_POST['top_search_branch']))
	{
		unset($_SESSION['top_search_name']);
		unset($_SESSION['top_search_branch']);
	}
	if($admin_privilege==true)
	{
		$top_where_clause="WHERE DATE(c.next_call_date)=:next_call_date ";
		$top_execute[':next_call_date']=date("Y-m-d");
	}
	else if($branch_privilege==true)
	{
		$top_where_clause="WHERE DATE(c.next_call_date)=:next_call_date AND u.parent_id=:parent_id_1 ";
		$top_execute[':next_call_date']=date("Y-m-d");
		$top_execute[':parent_id_1']=$_SESSION['logged_user_id'];
	}
	else
	{
		$where_clause="WHERE :no ";
		$execute=array(':no'=>0);
	}
	if(isset($_POST['top_search_name']) && $_POST['top_search_name']!="")
	{
		if(isset($_GET['top_page']))
		{
			unset($_GET['top_page']);
		}
		if(isset($_SESSION['top_search_name']))
		{
			unset($_SESSION['top_search_name']);
		}
		if($admin_privilege==true && isset($_SESSION['top_search_branch']) && $_SESSION['top_search_branch']!="")
		{
			if($_SESSION['top_search_branch']!="All")
			{
				$top_where_clause.=" AND u.parent_id=:parent_id ";
				$top_execute[':parent_id']=$_SESSION['top_search_branch'];
			}
		}
		$top_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
		$top_execute[':person_name']='%'.stripcleantohtml($_POST['top_search_name']).'%';
		$top_execute[':email_address']='%'.stripcleantohtml($_POST['top_search_name']).'%';
		$top_execute[':phone_no_1']='%'.stripcleantohtml($_POST['top_search_name']).'%';
		$top_execute[':phone_no_2']='%'.stripcleantohtml($_POST['top_search_name']).'%';
		$_SESSION['top_search_name']=$_POST['top_search_name'];
	}
	else if($admin_privilege==true && isset($_POST['top_search_branch']) && $_POST['top_search_branch']!="")
	{
		if(isset($_GET['top_page']))
		{
			unset($_GET['top_page']);
		}
		if(isset($_SESSION['top_search_branch']))
		{
			unset($_SESSION['top_search_branch']);
		}
		if(isset($_SESSION['top_search_name']) && $_SESSION['top_search_name']!="")
		{
			$top_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
			$top_execute[':person_name']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
			$top_execute[':email_address']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
			$top_execute[':phone_no_1']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
			$top_execute[':phone_no_2']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
		}
		if($_POST['top_search_branch']!="All")
		{
			$top_where_clause.=" AND u.parent_id=:parent_id ";
			$top_execute[':parent_id']=$_POST['top_search_branch'];
		}
		$_SESSION['top_search_branch']=$_POST['top_search_branch'];
	}
	else if(isset($_SESSION['top_search_name']) && $_SESSION['top_search_name']!="")
	{
		if($admin_privilege==true && isset($_SESSION['top_search_branch']) && $_SESSION['top_search_branch']!="")
		{
			if($_SESSION['top_search_branch']!="All")
			{
				$top_where_clause.=" AND u.parent_id=:parent_id ";
				$top_execute[':parent_id']=$_SESSION['top_search_branch'];
			}
		}
		$top_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
		$top_execute[':person_name']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
		$top_execute[':email_address']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
		$top_execute[':phone_no_1']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
		$top_execute[':phone_no_2']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
	}
	else if($admin_privilege==true && isset($_SESSION['top_search_branch']) && $_SESSION['top_search_branch']!="")
	{
		if(isset($_SESSION['top_search_name']) && $_SESSION['top_search_name']!="")
		{
			$top_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
			$top_execute[':person_name']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
			$top_execute[':email_address']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
			$top_execute[':phone_no_1']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
			$top_execute[':phone_no_2']='%'.stripcleantohtml($_SESSION['top_search_name']).'%';
		}
		if($_SESSION['top_search_branch']!="All")
		{
			$top_where_clause.=" AND u.parent_id=:parent_id ";
			$top_execute[':parent_id']=$_SESSION['top_search_branch'];
		}
	}
	$table=USERS." as u INNER JOIN ".CALL_BACK." as c ON u.id=c.user_id INNER JOIN (SELECT Max(id) as MaxID FROM ".CALL_BACK." as c1 GROUP BY user_id) as c2 ON c.id=c2.MaxID ";
	$top_count_result = find("first", $table, "count(u.id) as total_count", $top_where_clause, $top_execute);
	$top_total_result = $top_count_result['total_count'];
	$record_no = PAGELIMIT;
	$top_no_of_page = ceil($top_total_result / $record_no);
	if(isset($_GET['top_page']))
	{
		$top_page = $_GET['top_page'];
		if(($top_page > $top_no_of_page) && $top_no_of_page!=0)
		{
			$top_page=$top_no_of_page;
		}
		else if($top_page < 1)
		{
			$top_page=1;
		}
		else
		{
			$top_page=$_GET['top_page'];
		}
	}
	else
	{
		$top_page=1;
	}
	$top_offset = ($top_page-1) * $record_no;
	$top_fields="u.id, u.person_name, u.initial, u.email_address, u.phone_no_1, c.next_call_date, c.id as c_id";
	$top_user_list = find('all', $table, $top_fields, $top_where_clause." ORDER BY u.id DESC LIMIT ".$top_offset.",".$record_no, $top_execute);
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

	if(!isset($_GET['bottom_page']) && !isset($_POST['bottom_search_name']) && !isset($_POST['bottom_search_branch']))
	{
		unset($_SESSION['bottom_search_name']);
		unset($_SESSION['bottom_search_branch']);
	}
	if($admin_privilege==true)
	{
		$bottom_where_clause="WHERE :all ";
		$bottom_execute[':all']=1;
	}
	else if($branch_privilege==true)
	{
		$bottom_where_clause="WHERE :all AND u.parent_id=:parent_id_1 ";
		$bottom_execute[':all']=1;
		$bottom_execute[':parent_id_1']=$_SESSION['logged_user_id'];
	}
	else
	{
		$where_clause="WHERE :no ";
		$execute=array(':no'=>0);
	}
	if(isset($_POST['bottom_search_name']) && $_POST['bottom_search_name']!="")
	{
		if(isset($_GET['bottom_page']))
		{
			unset($_GET['bottom_page']);
		}
		if(isset($_SESSION['bottom_search_name']))
		{
			unset($_SESSION['bottom_search_name']);
		}
		if($admin_privilege==true && isset($_SESSION['bottom_search_branch']) && $_SESSION['bottom_search_branch']!="")
		{
			if($_SESSION['bottom_search_branch']!="All")
			{
				$bottom_where_clause.=" AND u.parent_id=:parent_id ";
				$bottom_execute[':parent_id']=$_SESSION['bottom_search_branch'];
			}
		}
		$bottom_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
		$bottom_execute[':person_name']='%'.stripcleantohtml($_POST['bottom_search_name']).'%';
		$bottom_execute[':email_address']='%'.stripcleantohtml($_POST['bottom_search_name']).'%';
		$bottom_execute[':phone_no_1']='%'.stripcleantohtml($_POST['bottom_search_name']).'%';
		$bottom_execute[':phone_no_2']='%'.stripcleantohtml($_POST['bottom_search_name']).'%';
		$_SESSION['bottom_search_name']=$_POST['bottom_search_name'];
	}
	else if($admin_privilege==true && isset($_POST['bottom_search_branch']) && $_POST['bottom_search_branch']!="")
	{
		if(isset($_GET['bottom_page']))
		{
			unset($_GET['bottom_page']);
		}
		if(isset($_SESSION['bottom_search_branch']))
		{
			unset($_SESSION['bottom_search_branch']);
		}
		if(isset($_SESSION['bottom_search_name']) && $_SESSION['bottom_search_name']!="")
		{
			$bottom_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
			$bottom_execute[':person_name']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
			$bottom_execute[':email_address']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
			$bottom_execute[':phone_no_1']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
			$bottom_execute[':phone_no_2']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
		}
		if($_POST['bottom_search_branch']!="All")
		{
			$bottom_where_clause.=" AND u.parent_id=:parent_id ";
			$bottom_execute[':parent_id']=$_POST['bottom_search_branch'];
		}
		$_SESSION['bottom_search_branch']=$_POST['bottom_search_branch'];
	}
	else if(isset($_SESSION['bottom_search_name']) && $_SESSION['bottom_search_name']!="")
	{
		if($admin_privilege==true && isset($_SESSION['bottom_search_branch']) && $_SESSION['bottom_search_branch']!="")
		{
			if($_SESSION['bottom_search_branch']!="All")
			{
				$bottom_where_clause.=" AND u.parent_id=:parent_id ";
				$bottom_execute[':parent_id']=$_SESSION['bottom_search_branch'];
			}
		}
		$bottom_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
		$bottom_execute[':person_name']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
		$bottom_execute[':email_address']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
		$bottom_execute[':phone_no_1']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
		$bottom_execute[':phone_no_2']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
	}
	else if($admin_privilege==true && isset($_SESSION['bottom_search_branch']) && $_SESSION['bottom_search_branch']!="")
	{
		if(isset($_SESSION['bottom_search_name']) && $_SESSION['bottom_search_name']!="")
		{
			$bottom_where_clause.=" AND (u.person_name LIKE :person_name OR u.email_address LIKE :email_address OR u.phone_no_1 LIKE :phone_no_1 OR u.phone_no_2 LIKE :phone_no_2) ";
			$bottom_execute[':person_name']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
			$bottom_execute[':email_address']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
			$bottom_execute[':phone_no_1']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
			$bottom_execute[':phone_no_2']='%'.stripcleantohtml($_SESSION['bottom_search_name']).'%';
		}
		if($_SESSION['bottom_search_branch']!="All")
		{
			$bottom_where_clause.=" AND u.parent_id=:parent_id ";
			$bottom_execute[':parent_id']=$_SESSION['bottom_search_branch'];
		}
	}
	$table=USERS." as u INNER JOIN ".CALL_BACK." as c ON u.id=c.user_id INNER JOIN (SELECT Max(id) as MaxID FROM ".CALL_BACK." as c1 GROUP BY user_id) as c2 ON c.id=c2.MaxID ";
	$bottom_count_result = find("first", $table, "count(u.id) as total_count", $bottom_where_clause, $bottom_execute);
	$bottom_total_result = $bottom_count_result['total_count'];
	$bottom_no_of_page = ceil($bottom_total_result / $record_no);
	if(isset($_GET['bottom_page']))
	{
		$bottom_page = $_GET['bottom_page'];
		if(($bottom_page > $bottom_no_of_page) && $bottom_no_of_page!=0)
		{
			$bottom_page=$bottom_no_of_page;
		}
		else if($bottom_page < 1)
		{
			$bottom_page=1;
		}
		else
		{
			$bottom_page=$_GET['bottom_page'];
		}
	}
	else
	{
		$bottom_page=1;
	}
	$bottom_offset = ($bottom_page-1) * $record_no;
	$bottom_fields="u.id, u.person_name, u.initial, u.email_address, u.phone_no_1, c.next_call_date, c.id as c_id";
	$bottom_user_list = find('all', $table, $bottom_fields, $bottom_where_clause." ORDER BY c.id DESC LIMIT ".$bottom_offset.",".$record_no, $bottom_execute);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#focusedInput").keyup(function(){
				$("#focusedInput").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_user_name_1.php",
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
			$("#focusedInput_bottom").keyup(function(){
				$("#focusedInput_bottom").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_user_name_2.php",
					//dataType: "json",
					data: {
						person_name:$("#focusedInput_bottom").val()
					},
					success: function( data ) {
						$("#auto_sug_content_div_bottom").show();
						$("#auto_sug_content_div_bottom").html( data );
						$("#focusedInput_bottom").removeClass('person_name_rotate');

					},
					error: function(){
					}
				});
			});
		});
		$(window).on("resize load", function() {
			$(".bottom_section, .top_section").css("max-height", ($(window).height()-65)/2);
		});
		function value_send(value)
		{
			$("#focusedInput").val(value);
			$("#auto_sug_content_div").hide();
			$("#focusedInput").focus();
		}
		function value_send_2(value)
		{
			$("#focusedInput_bottom").val(value);
			$("#auto_sug_content_div_bottom").hide();
			$("#focusedInput_bottom").focus();
		}
	//-->
	</script>
	<style type="text/css">
		.person_name_rotate{ background:#F7F7F7 url('img/ajax-loader.gif') right center no-repeat !important; }
		.auto_sugg_cls{max-height:165px;overflow:auto;width:97%;top:36px;position:absolute;border: 1px solid rgb(204, 204, 204);border-radius:2px;background:#FFFFFF;display:none;height:auto;z-index:99999999;}
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
				<h3 class="panel-title">Listen der Andere</h3>
			  </div>
			  <div class=" content">
				<div class="top_section">
					<div class="panel-heading">
						<h3 class="panel-title">Der heutige Rückruf</h3>
					</div>
					<div class="form-group" style="padding:3px;position:relative;">
					  <form method="post" action="" id="name_search" name="name_search" autocomplete="off">
							<input class="form-control search" id="focusedInput" placeholder="Suche nach Name / E-Mail / Telefon" type="text" name="top_search_name" value="<?php echo((isset($_POST['top_search_name']) && $_POST['top_search_name']!='') ? $_POST['top_search_name'] : (isset($_SESSION['top_search_name']) && $_SESSION['top_search_name']!="" ? $_SESSION['top_search_name'] : ""));?>"/>
						</form>
						<div class="auto_sugg_cls" id="auto_sug_content_div"></div>
					</div>
					<div class="form-group" style="padding:3px">
						<div class="col-md-6">
							Filter nach zweig: 
							<form method="post" action="" id="filter_branch" name="filter_branch" style="display: inline-block;">
								<select name="top_search_branch" id="top_search_branch" onchange="$('#filter_branch').submit();">
									<option value = "All" <?php echo((isset($_POST['top_search_branch']) && $_POST['top_search_branch']=='All') ? "selected='selected'" : (isset($_SESSION['top_search_branch']) && $_SESSION['top_search_branch']=='All' ? "selected='selected'" : ""));?>>All</option>
									<?php
										if(!empty($branch_list))
										{
											foreach($branch_list as $branch_key=>$branch_value)
											{
									?>
									<option value = "<?php echo $branch_value['id'];?>" <?php echo(isset($_POST['top_search_branch']) && $_POST['top_search_branch']==$branch_value['id'] ? "selected='selected'" : (isset($_SESSION['top_search_branch']) && $_SESSION['top_search_branch']==$branch_value['id'] ? "selected='selected'" : ""));?>><?php echo $branch_value['branch_name'];?></option>
									<?php
											}
										}
									?>
								 </select>
							</form>
						</div>
						<div class="col-md-6" style = "text-align:right;">
							
						</div>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th style="text-align:center">Edit</th>
								<th>Name <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Email  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Telefon</th>
								<th style="text-align:center">Aktion</th>
							</tr>
						</thead>
						<tbody> 
					<?php
						if(!empty($top_user_list))
						{
							foreach($top_user_list as $user_key=>$user_val)
							{
								$user_call_note="";
								$find_user_call_note=find('all', CALL_BACK, '*', "WHERE user_id=:user_id ORDER BY id DESC", array(':user_id'=>$user_val['id']));
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
									<a href="edit_contact.php?contact_id=<?php echo base64_encode($user_val['id'].IDHASH);?><?php echo(isset($_GET["top_page"]) && $_GET["top_page"]!="" ? "&top_page=".$_GET["top_page"] : "");?>&from_top=1" title="Benutzer bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>
								</td>
								<td><?php echo $user_val['initial']." ".ucwords($user_val['person_name']);?></td>
								<td><?php echo $user_val['email_address'];?></td>
								<td><?php echo $user_val['phone_no_1'];?></td>
								<td style="text-align:center">
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
								<td colspan="100%" class="no_record_cls">Kein Eintrag gefunden</td>
							</tr>
					<?php
						}
					?>
						</tbody>
					</table>
					<?php
						if($top_total_result>1)
						{
					?>
						<ul class="pagination pagination-sm" style="float:right; margin-right:5px; margin-top:0px">
							<li class="<?php echo($top_page <= 1 ? "disabled" : "");?>"><a href="<?php echo($top_page > 1 ? DOMAIN_NAME_PATH."call-back-list.php?top_page=".($top_page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($top_no_of_page < $page_link)
							{
								for($l=1; $l<=$top_no_of_page; $l++)
								{
						?>
							<li class="<?php echo($top_page==$l ? "active" : '');?>"><a href="<?php echo($top_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?top_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($top_page>($top_no_of_page-$mid_link))
							{
								for($l=$top_no_of_page-$page_link+1;$l<=$top_no_of_page;$l++)
								{
						?>
							<li class="<?php echo($top_page==$l ? "active" : '');?>"><a href="<?php echo($top_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?top_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($top_page >$mid_link)
							{ 
								for($l=$top_page-$st_link;$l<=$top_page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($top_page==$l ? "active" : '');?>"><a href="<?php echo($top_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?top_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($top_page==$l ? "active" : '');?>"><a href="<?php echo($top_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?top_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($top_page >= $top_no_of_page ? "disabled" : "");?>"><a href="<?php echo($top_page < $top_no_of_page ? DOMAIN_NAME_PATH."call-back-list.php?top_page=".($top_page+1) : "javascript:void(0)");?>">&raquo;</a></li>
						</ul>
					<?php
						}
					?>
					<div class="clearfix"></div>
				</div>
				<div class="bottom_section">
					<div class="panel-heading">
						<h3 class="panel-title">Rückrufliste</h3>
					</div>
					<div class="form-group" style="padding:3px;position:relative;">
					  <form method="post" action="" id="bottom_name_search" name="bottom_name_search" autocomplete="off">
							<input class="form-control search" id="focusedInput_bottom" placeholder="Suche nach Name / E-Mail / Telefon" type="text" name="bottom_search_name" value="<?php echo((isset($_POST['bottom_search_name']) && $_POST['bottom_search_name']!='') ? $_POST['bottom_search_name'] : (isset($_SESSION['bottom_search_name']) && $_SESSION['bottom_search_name']!="" ? $_SESSION['bottom_search_name'] : ""));?>"/>
						</form>
						<div class="auto_sugg_cls" id="auto_sug_content_div_bottom"></div>
					</div>
					<div class="form-group" style="padding:3px">
						<div class="col-md-6">
							Filter nach zweig: 
							<form method="post" action="" id="bottom_filter_branch" name="bottom_filter_branch" style="display: inline-block;">
								<select name="bottom_search_branch" id="bottom_search_branch" onchange="$('#bottom_filter_branch').submit();">
									<option value = "All" <?php echo((isset($_POST['bottom_search_branch']) && $_POST['bottom_search_branch']=='All') ? "selected='selected'" : (isset($_SESSION['bottom_search_branch']) && $_SESSION['bottom_search_branch']=='All' ? "selected='selected'" : ""));?>>All</option>
									<?php
										if(!empty($branch_list))
										{
											foreach($branch_list as $branch_key=>$branch_value)
											{
									?>
									<option value = "<?php echo $branch_value['id'];?>" <?php echo(isset($_POST['bottom_search_branch']) && $_POST['bottom_search_branch']==$branch_value['id'] ? "selected='selected'" : (isset($_SESSION['bottom_search_branch']) && $_SESSION['bottom_search_branch']==$branch_value['id'] ? "selected='selected'" : ""));?>><?php echo $branch_value['branch_name'];?></option>
									<?php
											}
										}
									?>
								 </select>
							</form>
						</div>
						<div class="col-md-6" style = "text-align:right;">
							
						</div>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th style="text-align:center">Edit</th>
								<th>Name <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Email  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Telefon</th>
								<th style="text-align:center">Aktion</th>
							</tr>
						</thead>
						<tbody> 
					<?php
						if(!empty($bottom_user_list))
						{
							foreach($bottom_user_list as $user_key=>$user_val)
							{
								$user_call_note="";
								$find_user_call_note=find('all', CALL_BACK, '*', "WHERE user_id=:user_id ORDER BY id DESC", array(':user_id'=>$user_val['id']));
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
									<a href="edit_contact.php?contact_id=<?php echo base64_encode($user_val['id'].IDHASH);?><?php echo(isset($_GET["bottom_page"]) && $_GET["bottom_page"]!="" ? "&bottom_page=".$_GET["bottom_page"] : "");?>&from_bottom=1" title="Benutzer bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>
								</td>
								<td><?php echo $user_val['initial']." ".ucwords($user_val['person_name']);?></td>
								<td><?php echo $user_val['email_address'];?></td>
								<td><?php echo $user_val['phone_no_1'];?></td>
								<td style="text-align:center">
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
								<td colspan="100%" class="no_record_cls">Kein Eintrag gefunden</td>
							</tr>
					<?php
						}
					?>
						</tbody>
					</table>
					<?php
						if($bottom_total_result>1)
						{
					?>
						<ul class="pagination pagination-sm" style="float:right; margin-right:5px; margin-top:0px">
							<li class="<?php echo($bottom_page <= 1 ? "disabled" : "");?>"><a href="<?php echo($bottom_page > 1 ? DOMAIN_NAME_PATH."call-back-list.php?bottom_page=".($bottom_page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($bottom_no_of_page < $page_link)
							{
								for($l=1; $l<=$bottom_no_of_page; $l++)
								{
						?>
							<li class="<?php echo($bottom_page==$l ? "active" : '');?>"><a href="<?php echo($bottom_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?bottom_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($bottom_page>($bottom_no_of_page-$mid_link))
							{
								for($l=$bottom_no_of_page-$page_link+1;$l<=$bottom_no_of_page;$l++)
								{
						?>
							<li class="<?php echo($bottom_page==$l ? "active" : '');?>"><a href="<?php echo($bottom_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?bottom_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($bottom_page >$mid_link)
							{ 
								for($l=$bottom_page-$st_link;$l<=$bottom_page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($bottom_page==$l ? "active" : '');?>"><a href="<?php echo($bottom_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?bottom_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($bottom_page==$l ? "active" : '');?>"><a href="<?php echo($bottom_page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."call-back-list.php?bottom_page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($bottom_page >= $bottom_no_of_page ? "disabled" : "");?>"><a href="<?php echo($bottom_page < $bottom_no_of_page ? DOMAIN_NAME_PATH."call-back-list.php?bottom_page=".($bottom_page+1) : "javascript:void(0)");?>">&raquo;</a></li>
						</ul>
					<?php
						}
					?>
					<div class="clearfix"></div>
				</div>
			  </div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>