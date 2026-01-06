<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_POST['create_payment']))
	{
		//print_r($_POST);
		if(isset($_POST['client_name']) && $_POST['client_name']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Kundenname erforderlich.';
		}
		else if(isset($_POST['amount_due']) && $_POST['amount_due']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Offener Betrag erforderlich.';
		}
		else if(isset($_POST['amount_paid']) && $_POST['amount_paid']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Betrag bezahlt erforderlich.';
		}
		else if(isset($_POST['description']) && $_POST['description']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Zahlungsdetails erforderlich.';
		}
		else
		{
			$find_payment_client_name=find('first', PAYMENT, 'id, client_name', "WHERE client_name=:client_name", array(':client_name'=>stripcleantohtml($_POST['client_name'])));
			if(empty($find_payment_client_name))
			{
				$fields="client_name, amount_due, amount_paid, description";
				$values=":client_name, :amount_due, :amount_paid, :description";
				$execute=array(
					':client_name'=>stripcleantohtml($_POST['client_name']),
					':amount_due'=>stripcleantohtml($_POST['amount_due']),
					':amount_paid'=>stripcleantohtml($_POST['amount_paid']),
					':description'=>stripcleantohtml($_POST['description'])
				);
				$add_payment = save(PAYMENT, $fields, $values,$execute);
				if($add_payment > 0)
				{
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Zahlungsinformationen erfolgreich hinzugefügt.';
					header('location:'.DOMAIN_NAME_PATH.'payment-list.php');
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
				if($find_payment_client_name['client_name']==$_POST['client_name'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Kundenname ist bereits vorhanden.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&uuml;ter.';
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#add_payment_form").validationEngine();
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
					<h3 class="panel-title">Neues Zahlungsinformationen erstellen</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="add_payment_form" name="add_payment_form" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Kundenname:</label>
							  <input class="form-control validate[required]" placeholder="Kundenname" type="text" id="client_name" name="client_name" maxlength="255"  data-errormessage-value-missing="Kundenname erforderlich" tabindex="1" value="<?php echo((isset($_POST['client_name']) && $_POST['client_name']!='') ? $_POST['client_name'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Offener Betrag:</label>
							  <input class="form-control  validate[required, custom[number]]" placeholder="Offener Betrag" type="text" id="amount_due" name="amount_due" maxlength="13"  data-errormessage-value-missing="Offener Betrag erforderlich" tabindex="2" value="<?php echo((isset($_POST['amount_due']) && $_POST['amount_due']!='') ? $_POST['amount_due'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Betrag bezahlt:</label>
							  <input class="form-control validate[required, custom[number]]" placeholder="Betrag bezahlt" type="text" id="amount_paid" name="amount_paid" maxlength="13"  data-errormessage-value-missing="Betrag bezahlt erforderlich" tabindex="3" value="<?php echo((isset($_POST['amount_paid']) && $_POST['amount_paid']!='') ? $_POST['amount_paid'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Zahlungsdetails:</label>
							  <textarea name="description" class="form-control validate[required]" Placeholder="Zahlungsdetails" data-errormessage-value-missing="Zahlungsdetails ist erforderlich" id="description" tabindex="4"><?php echo(isset($_POST['description']) && $_POST['description']!="" ? $_POST['description'] : "");?></textarea>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "create_payment" tabindex="5"><b>Erstellen neuer Zahlungsinformationen</b></button>
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