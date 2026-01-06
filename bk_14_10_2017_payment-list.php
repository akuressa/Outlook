<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$payment_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$find_payment= find('first', PAYMENT, 'id', "WHERE id=:id", array(':id'=>$payment_id));
		if(!empty($find_payment))
		{
			$del_rcd=delete(PAYMENT, 'WHERE id=:id', array(':id'=>$payment_id));
			if($del_rcd==true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Die Zahlungsinformationen wurden erfolgreich gelöscht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'payment-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Zweig ID.';
			header('location:'.DOMAIN_NAME_PATH.'payment-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	if(!isset($_GET['page']) && !isset($_POST['search_name']))
	{
		unset($_SESSION['search_name']);
	}
	if($admin_privilege==true)
	{
		$where_clause="WHERE :all ";
		$execute[':all']=1;
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
		$where_clause.=" AND client_name LIKE :client_name ";
		$execute[':client_name']='%'.stripcleantohtml($_POST['search_name']).'%';
		$_SESSION['search_name']=$_POST['search_name'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		$where_clause.=" AND client_name LIKE :client_name ";
		$execute[':client_name']='%'.stripcleantohtml($_SESSION['search_name']).'%';
	}
	$table=PAYMENT;
	$count_result = find("first", $table, "COUNT(id) as total_count", $where_clause, $execute);
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
	$payment_list = find('all', $table, $fields, $where_clause." ORDER BY client_name ASC LIMIT ".$offset.",".$record_no, $execute);
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
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>payment-list.php?del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}
		$(function(){
			$("#focusedInput").keyup(function(){
				$("#focusedInput").addClass('person_name_rotate');
				$.ajax({
					type:'post',
					url: "<?php echo(DOMAIN_NAME_PATH);?>find_payment_client_name.php",
					//dataType: "json",
					data: {
						client_name:$("#focusedInput").val()
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
		$(window).on("resize load", function() {
			$(".section").css("max-height", ($(window).height()-65));
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
				<h3 class="panel-title">Listen der Offene Zahlungen</h3>
			  </div>
			  <div class=" content">
				<div class="form-group" style="padding:3px;position:relative;">
				  <form method="post" action="" id="name_search" name="name_search" autocomplete="off">
						<input class="form-control search" id="focusedInput" placeholder="Suche nach Name" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
					</form>
					<div class="auto_sugg_cls" id="auto_sug_content_div"></div>
				</div>
				<div class="form-group" style="padding:3px;text-align:right;">
				  <a href = "create-payment-info.php"><button class="button" name = "btn_create"><b>Neues Zahlungsinformationen erstellen</b></button></a>
				</div>
				<table id="myTable" class="table tablesorter">
					<thead class="add_new">
						<tr>
							<th>Name <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
							<th>Offener Betrag  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
							<th>Betrag bezahlt  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></th>
							<th style="text-align:center">Aktion</th>
						</tr>
					</thead>
					<tbody> 
				<?php
					if(!empty($payment_list))
					{
						foreach($payment_list as $payment_key=>$payment_val)
						{
				?>
						 <tr>
							<td><?php echo ucwords($payment_val['client_name']);?></td>
							<td><?php echo ($payment_val['amount_due'] ? "&euro;".$payment_val['amount_due'] : "");?></td>
							<td><?php echo ($payment_val['amount_paid'] ? "&euro;".$payment_val['amount_paid'] : "");?></td>
							<td style="text-align:center">
								<a href="edit-payment-info.php?payment_id=<?php echo base64_encode($payment_val['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>&from_top=1" title="Bearbeitungsinformationen bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>
								<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($payment_val['id'].IDHASH));?>');" title="Zahlungsinformationen löschen"><span class="glyphicon glyphicon-trash"></span></a>
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
					if($total_result>1)
					{
				?>
					<ul class="pagination pagination-sm" style="float:right; margin-right:5px; margin-top:0px">
						<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."payment-list.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
					<?php
						if($no_of_page < $page_link)
						{
							for($l=1; $l<=$no_of_page; $l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."payment-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
						else if($page>($no_of_page-$mid_link))
						{
							for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."payment-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
						else if($page >$mid_link)
						{ 
							for($l=$page-$st_link;$l<=$page+$end_link;$l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."payment-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
						else
						{
							for($l=1;$l<=$page_link;$l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."payment-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
					?>
					<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."payment-list.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
					</ul>
				<?php
					}
				?>
				<div class="clearfix"></div>
			  </div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>