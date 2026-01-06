<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$pdf_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$find_pdf= find('first', PDF, 'id', "WHERE id=:id", array(':id'=>$pdf_id));
		if(!empty($find_pdf))
		{
			$del_rcd=delete(PDF, 'WHERE id=:id', array(':id'=>$pdf_id));
			if($del_rcd==true)
			{
				$pdf_name="pdf_".$find_pdf['id'].".pdf";
				if(file_exists('pdf_folder/'.$pdf_name))
				{
					unlink('pdf_folder/'.$pdf_name);
				}
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Die Rechnungsdaten wurden erfolgreich gelöscht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'pdf-data-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Rechnungsdaten ID.';
			header('location:'.DOMAIN_NAME_PATH.'pdf-data-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
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
		$where_clause.=" AND (reference_no LIKE :reference_no OR invoice_no LIKE :invoice_no OR editor_name LIKE :editor_name) ";
		$execute[':reference_no']='%'.stripcleantohtml($_POST['search_name']).'%';
		$execute[':invoice_no']='%'.stripcleantohtml($_POST['search_name']).'%';
		$execute[':editor_name']='%'.stripcleantohtml($_POST['search_name']).'%';
		$_SESSION['search_name']=$_POST['search_name'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		$where_clause.=" AND (reference_no LIKE :reference_no OR invoice_no LIKE :invoice_no OR editor_name LIKE :editor_name) ";
		$execute[':reference_no']='%'.stripcleantohtml($_SESSION['search_name']).'%';
		$execute[':invoice_no']='%'.stripcleantohtml($_SESSION['search_name']).'%';
		$execute[':editor_name']='%'.stripcleantohtml($_SESSION['search_name']).'%';
	}
	$table=PDF;
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
	$pdf_list = find('all', $table, $fields, $where_clause." ORDER BY id ASC LIMIT ".$offset.",".$record_no, $execute);
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
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>pdf-data-list.php?del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}
		$(window).on("resize load", function() {
			$(".section").css("max-height", ($(window).height()-65));
		});
	//-->
	</script>
	<style type="text/css">
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
				<h3 class="panel-title">Listen der Rechnungsdaten</h3>
			  </div>
			  <div class=" content">
				<div class="form-group" style="padding:3px;position:relative;">
				  <form method="post" action="" id="name_search" name="name_search" autocomplete="off">
						<input class="form-control search" id="focusedInput" placeholder="Suche nach Rechnung Nr / Kunden-Nr / Herausgeber" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
					</form>
					<div class="auto_sugg_cls" id="auto_sug_content_div"></div>
				</div>
				<div class="form-group" style="padding:3px;text-align:right;">
				  <a href = "create-pdf-data.php"><button class="button" name = "btn_create"><b>Neues Rechnungsdaten erstellen</b></button></a>
				</div>
				<table id="myTable" class="table tablesorter">
					<thead class="add_new">
						<tr>
							<th>Rechnung Nr. <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
							<th>Datum  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
							<th>Seite  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></th>
							<th>Kunden-Nr.  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></th>
							<th>Herausgeber  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></th>
							<th style="text-align:center">Aktion</th>
						</tr>
					</thead>
					<tbody> 
				<?php
					if(!empty($pdf_list))
					{
						foreach($pdf_list as $pdf_key=>$pdf_val)
						{
							$pdf_name="pdf_".$pdf_val['id'].".pdf";
				?>
						 <tr>
							<td><?php echo $pdf_val['reference_no'];?></td>
							<td><?php echo change_date_format($pdf_val['pdf_date']);?></td>
							<td><?php echo $pdf_val['no_of_pages'];?></td>
							<td><?php echo $pdf_val['invoice_no'];?></td>
							<td><?php echo $pdf_val['editor_name'];?></td>
							<td style="text-align:center">
								<a href="edit-pdf-data.php?pdf_id=<?php echo base64_encode($pdf_val['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Rechnungsdaten bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>
								<?php
								if(file_exists('pdf_folder/'.$pdf_name))
								{
								?>
								<a href="pdf_folder/<?php echo $pdf_name;?>" target="_blank" title="PDF Herunterladen"><span class="glyphicon glyphicon-download-alt"></span></a>
								<?php
								}
								?>
								<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($pdf_val['id'].IDHASH));?>');" title="Rechnungsdaten löschen"><span class="glyphicon glyphicon-trash"></span></a>
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
						<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."pdf-data-list.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
					<?php
						if($no_of_page < $page_link)
						{
							for($l=1; $l<=$no_of_page; $l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."pdf-data-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
						else if($page>($no_of_page-$mid_link))
						{
							for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."pdf-data-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
						else if($page >$mid_link)
						{ 
							for($l=$page-$st_link;$l<=$page+$end_link;$l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."pdf-data-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
						else
						{
							for($l=1;$l<=$page_link;$l++)
							{
					?>
						<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."pdf-data-list.php?page=".$l);?>"><?php echo $l;?></a></li>
					<?php
							}
						}
					?>
					<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."pdf-data-list.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
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