<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_POST['create_product']))
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
			$find_product_name=find('first', PRODUCT, 'id, item_no, item_name', "WHERE item_no=:item_no OR item_name=:item_name", array(':item_no'=>stripcleantohtml($_POST['item_no']),':item_name'=>stripcleantohtml($_POST['item_name'])));
			if(empty($find_product_name))
			{
				$fields="item_no, item_name, price, tax_type, tax, status";
				$values=":item_no, :item_name, :price, :tax_type, :tax, :status";
				$execute=array(
					':item_no'=>stripcleantohtml($_POST['item_no']),
					':item_name'=>stripcleantohtml($_POST['item_name']),
					':price'=>stripcleantohtml($_POST['price']),
					':tax_type'=>stripcleantohtml($_POST['tax_type']),
					':tax'=>stripcleantohtml($_POST['tax']),
					':status'=>stripcleantohtml($_POST['status'])
				);
				$add_product = save(PRODUCT, $fields, $values,$execute);
				if($add_product > 0)
				{
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Produkt erfolgreich hinzugefügt.';
					header('location:'.DOMAIN_NAME_PATH.'product-list.php');
					exit;
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
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
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
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
			$("#add_product").validationEngine();
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
					<h3 class="panel-title">Neues Produkt erstellen</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="add_product" name="add_product" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Artikel-Nr.:</label>
							  <input class="form-control validate[required]" placeholder="Artikel-Nr." type="text" id="item_no" name="item_no" maxlength="255"  data-errormessage-value-missing="Artikel-Nr. erforderlich" tabindex="1" value="<?php echo((isset($_POST['item_no']) && $_POST['item_no']!='') ? $_POST['item_no'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Produktname:</label>
							  <input class="form-control validate[required]" placeholder="Produktname" type="text" id="item_name" name="item_name" maxlength="255"  data-errormessage-value-missing="Produktname erforderlich" tabindex="2" value="<?php echo((isset($_POST['item_name']) && $_POST['item_name']!='') ? $_POST['item_name'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Price:</label>
							  <input class="form-control validate[required, custom[number]]" placeholder="Price" type="text" id="price" name="price" maxlength="13"  data-errormessage-value-missing="Price erforderlich" tabindex="3" value="<?php echo((isset($_POST['price']) && $_POST['price']!='') ? $_POST['price'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Steuerart:</label>
							  <select class="form-control validate[required]" name = "tax_type" tabindex="4" id="tax_type" data-errormessage-value-missing="Steuerart erforderlich">
								<?php
									foreach($tax_option_array as $opt_key => $opt_val)
									{
								?>
										<option value = "<?php echo $opt_key;?>" <?php echo(isset($_POST['tax_type']) && $_POST['tax_type']==$opt_key ? "selected='selected'" : "");?>><?php echo $opt_val;?></option>
								<?php
									}
								?>
							  </select>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Steuer:</label>
							  <input class="form-control validate[required, custom[number]]" placeholder="Steuer" type="text" id="tax" name="tax" maxlength="13"  data-errormessage-value-missing="Steuer erforderlich" tabindex="5" value="<?php echo((isset($_POST['tax']) && $_POST['tax']!='') ? $_POST['tax'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Status:</label>
							  <select class="form-control" name = "status" id="status" tabindex="6">
								<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : "");?>>Aktiv</option>
								<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : "");?>>Inaktiv</option>
							  </select>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "create_product" tabindex="7"><b>Erstellen neuer Produkt</b></button>
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