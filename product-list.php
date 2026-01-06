<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$product_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$find_product= find('first', PRODUCT, 'id', "WHERE id=:id", array(':id'=>$product_id));
		if(!empty($find_product))
		{
			$del_rcd=delete(PRODUCT, 'WHERE id=:id', array(':id'=>$product_id));
			if($del_rcd==true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Das Produkt wurde erfolgreich gelöscht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'product-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Zweig ID.';
			header('location:'.DOMAIN_NAME_PATH.'product-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	if(!isset($_GET['page']))
	{
		unset($_SESSION['search_name']);
	}
	$where_clause="WHERE :all ";
	$execute=array(':all'=>1);
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
		$where_clause.=" AND (item_name LIKE :item_name OR item_no LIKE :item_no)";
		$execute=array_merge($execute, array(':item_name'=>'%'.$_POST['search_name'].'%', ':item_no'=>'%'.$_POST['search_name'].'%'));
		$_SESSION['search_name']=$_POST['search_name'];
	}
	else if(isset($_SESSION['search_name']) && $_SESSION['search_name']!="")
	{
		$where_clause.=" AND (item_name LIKE :item_name OR item_no LIKE :item_no)";
		$execute=array_merge($execute, array(':item_name'=>'%'.$_SESSION['search_name'].'%', ':item_no'=>'%'.$_SESSION['search_name'].'%'));
	}
	$count_result = find("first", PRODUCT, "count(id) as total_count", $where_clause, $execute);
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
	$product_list = find('all', PRODUCT, $fields, $where_clause." ORDER BY item_name ASC LIMIT ".$offset.",".$record_no, $execute);
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
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>product-list.php?del_id='+id+'<?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>';
			}
		}

		function set_status(id, cur)
		{
			$.post("<?php echo(DOMAIN_NAME_PATH);?>set-product-status.php?id="+id,function(data){
				if(data!="")
				{
					if(data=='no' || data=='yes')
					{
						if(data=='yes')
						{
							showSuccess('Produkt erfolgreich aktiviert wurde.');
							cur.attr('title', 'Click to inactive');
							cur.html('<font color = "green"><b>AKTIV</b></font>');
						}
						else
						{
							showSuccess('Produkt erfolgreich deaktiviert.');
							cur.attr('title', 'Click to active');
							cur.html('<font color = "red"><b>INAKTIV</b></font>');
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
	//-->
	</script>
</head>
<body>
    <div class="container"> 
		<?php include_once('includes/navigation.php');?>
		<div class="col-md-8">
			<div class="panel panel-primary">
			  <div class="panel-heading">
				<h3 class="panel-title">Liste der Produkte</h3>
			  </div>
			  <div class=" content">
					<div class="form-group" style="padding:3px">
						<form method="post" action="" id="product_search" name="product_search">
							<input class="form-control search" id="focusedInput" placeholder="Suche nach Produktname / Artikel-Nr" type="text" name="search_name" value="<?php echo((isset($_POST['search_name']) && $_POST['search_name']!='') ? $_POST['search_name'] : (isset($_SESSION['search_name']) && $_SESSION['search_name']!="" ? $_SESSION['search_name'] : ""));?>"/>
						</form>
					</div>
					<div class="form-group" style="padding:3px;text-align:right;">
					  <a href = "create-product.php"><button class="button" name = "btn_create"><b>Neues Produkt erstellen</b></button></a>
					</div>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>Artikel-Nr.<span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Produktname  <span class="glyphicon glyphicon-chevron-up" style="float:right;margin: 0px 5px;"></span><span class="glyphicon glyphicon-chevron-down" style="float:right"></span></th>
								<th>Price</th>
								<th>Steuer</th>
								<th>Status</th>
								<th style="text-align:center">Aktion</th>
							</tr>
						</thead>
						<tbody>
					<?php
						if(!empty($product_list))
						{
							foreach($product_list as $product_key=>$product_val)
							{
					?>
							<tr>
								<td><?php echo $product_val['item_no'];?></td>
								<td><?php echo $product_val['item_name'];?></td>
								<td><?php echo ($product_val['price'] ? "&euro;".$product_val['price'] : "N/A");?></td>
								<td><?php echo ($product_val['tax_type']=="F" ? "&euro;" : "").$product_val['tax'].($product_val['tax_type']=="P" ? "%" : "");?></td>
								<td>
									<?php
										if($product_val['status']=='Y')
										{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($product_val['id'].IDHASH));?>', $(this));" title="Click to inaktiv"><font color = "green"><b>AKTIV</b></font></a>
									<?php
										}
										else if($product_val['status']=='N')
										{
									?>
										<a href="javascript:void(0)" onclick = "set_status('<?php echo(base64_encode($product_val['id'].IDHASH));?>', $(this));" title="Click to aktive"><font color = "red"><b>INAKTIV</b></font></a>
									<?php
										}
									?>
								</td>
								<td style="text-align:center">
									<a href="edit-product.php?product_id=<?php echo base64_encode($product_val['id'].IDHASH);?><?php echo(isset($_GET["page"]) && $_GET["page"]!="" ? "&page=".$_GET["page"] : "");?>" title="Produkt bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;
									<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($product_val['id'].IDHASH));?>');" title="Produkt entfernen"><span class="glyphicon glyphicon-trash"></span></a>
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
							<li class="<?php echo($page <= 1 ? "disabled" : "");?>"><a href="<?php echo($page > 1 ? DOMAIN_NAME_PATH."product-list.php?page=".($page-1) : "javascript:void(0)");?>">&laquo;</a></li>
						<?php
							if($no_of_page < $page_link)
							{
								for($l=1; $l<=$no_of_page; $l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."product-list.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page>($no_of_page-$mid_link))
							{
								for($l=$no_of_page-$page_link+1;$l<=$no_of_page;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."product-list.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else if($page >$mid_link)
							{ 
								for($l=$page-$st_link;$l<=$page+$end_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."product-list.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
							else
							{
								for($l=1;$l<=$page_link;$l++)
								{
						?>
							<li class="<?php echo($page==$l ? "active" : '');?>"><a href="<?php echo($page==$l ? "javascript:void(0)" : DOMAIN_NAME_PATH."product-list.php?page=".$l);?>"><?php echo $l;?></a></li>
						<?php
								}
							}
						?>
						<li class="<?php echo($page >= $no_of_page ? "disabled" : "");?>"><a href="<?php echo($page < $no_of_page ? DOMAIN_NAME_PATH."product-list.php?page=".($page+1) : "javascript:void(0)");?>">&raquo;</a></li>
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