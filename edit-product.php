<?php
	include_once('init.php');
	check_login();
	has_privilege();
	
	if(isset($_GET['product_id']) && $_GET['product_id']!='')
	{
		$product_id=substr(base64_decode($_GET['product_id']), 0, -5);
		$find_product= find('first', PRODUCT, '*', "WHERE id=:id", array(':id'=>$product_id));
		if(!empty($find_product))
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ungültige Produkt-ID.';
			header('location:'.DOMAIN_NAME_PATH.'product-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Produkt-ID fehlt.';
		header('location:'.DOMAIN_NAME_PATH.'product-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
	if(isset($_POST['edit_product']))
	{
		//print_r($_POST);
		if(isset($_POST['item_no']) && $_POST['item_no']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Artikel-Nr. erforderlich.';
		}
		else if(isset($_POST['item_name']) && $_POST['item_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Produktname erforderlich.';
		}
		else if(isset($_POST['price']) && $_POST['price']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Price erforderlich.';
		}
		else if(isset($_POST['tax_type']) && $_POST['tax_type']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Steuerart erforderlich.';
		}
		else if(isset($_POST['tax']) && $_POST['tax']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Steuer erforderlich.';
		}
		else
		{
			$product_id=substr(base64_decode($_GET['product_id']), 0, -5);
			$find_product_name=find('first', PRODUCT, 'id, item_no, item_name', "WHERE (item_no=:item_no OR item_name=:item_name) AND id <> :id", array(':item_no'=>stripcleantohtml($_POST['item_no']),':item_name'=>stripcleantohtml($_POST['item_name']), ':id'=>$product_id));
			if(empty($find_product_name))
			{
				$value_set="item_no=:item_no, item_name=:item_name, price=:price, tax_type=:tax_type, tax=:tax, status=:status";
				$execute=array(
					':item_no'=>stripcleantohtml($_POST['item_no']),
					':item_name'=>stripcleantohtml($_POST['item_name']),
					':price'=>stripcleantohtml($_POST['price']),
					':tax_type'=>stripcleantohtml($_POST['tax_type']), 
					':tax'=>stripcleantohtml($_POST['tax']),
					':status'=>stripcleantohtml($_POST['status']),
					':id'=>$product_id
				);
				$update_product=update(PRODUCT, $value_set, 'WHERE id=:id', $execute);
				if($update_product == true)
				{
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Produkt erfolgreich aktualisiert.';
					header('location:'.DOMAIN_NAME_PATH.'product-list.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
					exit;
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
				}
			}
			else
			{
				if($find_product_name['item_no']==$_POST['item_no'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Artikel-Nr. ist bereits vorhanden.';
				}
				else if($find_product_name['item_name']==$_POST['item_name'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Produktname ist bereits vergeben.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
				}
			}
		}
	}
	$tax_option_array=array("P"=>"Percentage", "F"=>"Fixed");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#edit_product_form").validationEngine();
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
					<h3 class="panel-title">Produkt bearbeiten</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="edit_product_form" name="edit_product_form" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Artikel-Nr.:</label>
							  <input class="form-control validate[required]" placeholder="Artikel-Nr." type="text" id="item_no" name="item_no" maxlength="255"  data-errormessage-value-missing="Artikel-Nr. erforderlich" tabindex="1" value="<?php echo((isset($_POST['item_no']) && $_POST['item_no']!='') ? $_POST['item_no'] : (isset($find_product['item_no']) && $find_product['item_no']!="" ? $find_product['item_no'] : ""));?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Produktname:</label>
							  <input class="form-control validate[required]" placeholder="Produktname" type="text" id="item_name" name="item_name" maxlength="255"  data-errormessage-value-missing="Produktname erforderlich" tabindex="2" value="<?php echo((isset($_POST['item_name']) && $_POST['item_name']!='') ? $_POST['item_name'] : (isset($find_product['item_name']) && $find_product['item_name']!="" ? $find_product['item_name'] : ""));?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Price:</label>
							  <input class="form-control validate[required, custom[number]]" placeholder="Price" type="text" id="price" name="price" maxlength="13"  data-errormessage-value-missing="Price erforderlich" tabindex="3" value="<?php echo((isset($_POST['price']) && $_POST['price']!='') ? $_POST['price'] : (isset($find_product['price']) && $find_product['price']!="" ? $find_product['price'] : ""));?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Steuerart:</label>
							  <select class="form-control validate[required]" name = "tax_type" tabindex="4" id="tax_type" data-errormessage-value-missing="Steuerart erforderlich">
								<?php
									foreach($tax_option_array as $opt_key => $opt_val)
									{
								?>
										<option value = "<?php echo $opt_key;?>" <?php echo(isset($_POST['tax_type']) && $_POST['tax_type']==$opt_key ? "selected='selected'" : (isset($find_product['tax_type']) && $find_product['tax_type']==$opt_key ? "selected='selected'" : ""));?>><?php echo $opt_val;?></option>
								<?php
									}
								?>
							  </select>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Steuer:</label>
							  <input class="form-control validate[required, custom[number]]" placeholder="Steuer" type="text" id="tax" name="tax" maxlength="13"  data-errormessage-value-missing="Steuer erforderlich" tabindex="5" value="<?php echo((isset($_POST['tax']) && $_POST['tax']!='') ? $_POST['tax'] : (isset($find_product['tax']) && $find_product['tax']!="" ? $find_product['tax'] : ""));?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Status:</label>
							  <select class="form-control" name = "status" id="status" tabindex="6">
								<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : (isset($find_product['status']) && $find_product['status']=="Y" ? "selected='selected'" : ""));?>>Aktiv</option>
								<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : (isset($find_product['status']) && $find_product['status']=="N" ? "selected='selected'" : ""));?>>Inaktiv</option>
							  </select>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "edit_product" tabindex="7"><b>Produkt bearbeiten</b></button>
							</div>
						</form>
					</div>
					<div class="col-md-3">&nbsp;</div>
				</div>
			</div>
		</div>
		<?php include_once('includes/right_sidebar.php');?>
	</div>
	<?php include_once('includes/inner_footer.php');?>
</body>
</html>
<?php include_once('includes/footer.php');?>