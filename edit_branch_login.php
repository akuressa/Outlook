<?php
	include_once('init.php');
	check_login();
	has_privilege();
	
	if(isset($_GET['employee_id']) && $_GET['employee_id']!='')
	{
		$user_id=substr(base64_decode($_GET['employee_id']), 0, -5);
		$find_user= find('first', USERS, '*', "WHERE id=:id", array(':id'=>$user_id));
		if(!empty($find_user))
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ungültige Zweig Login-ID.';
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Zweig Login-ID fehlt.';
		header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
	if(isset($_POST['edit_branch_login']))
	{
		if(isset($_POST['email_address']) && $_POST['email_address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'E-Mail ist erforderlich.';
		}
		else
		{
			$user_id=substr(base64_decode($_GET['employee_id']), 0, -5);
			$find_branch_login=find('first', USERS, 'id, branch_name, email_address', "WHERE email_address=:email_address AND id <> :id", array(':email_address'=>stripcleantohtml($_POST['email_address']), ':id'=>$user_id));
			if(empty($find_branch_login))
			{
				$pass_val="";$pass_execute=array();
				if(isset($_POST['password']) && $_POST['password']!="")
				{
					$pass_val=", password=:password";
					$pass_execute=array(':password'=>md5($_POST['password']));
				}
				$value_set="email_address=:email_address, status=:status".$pass_val;
				$execute=array(
					':email_address'=>stripcleantohtml($_POST['email_address']),
					':status'=>stripcleantohtml($_POST['status']),
					':id'=>$user_id
				);
				$execute=array_merge($execute, $pass_execute);
				$update_user=update(USERS, $value_set, 'WHERE id=:id', $execute);
				if($update_user == true)
				{
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Zweig Login erfolgreich aktualisiert.';
					header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
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
				if($find_branch_login['email_address']==$_POST['email_address'])
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Diese E-Mail Adresse ist bereits vergeben.';
				}
				else
				{
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
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
			$("#edit_branch_login").validationEngine();
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
					<h3 class="panel-title">Niederlassung &auml;ndern Anmelden</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="edit_branch_login" name="edit_branch_login" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font>Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> E-Mail-Addresse:</label>
							  <input class="form-control validate[required, custom[email]]" placeholder="E-Mail-Addresse" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : (isset($find_user['email_address']) && $find_user['email_address']!="" ? $find_user['email_address'] : ""));?>" data-errormessage-value-missing="E-Mail ist erforderlich!" maxlength="255" tabindex="1"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Passwort:</label>
							  <input class="form-control" placeholder="Passwort" type="text" name="password" id="password" value="<?php echo((isset($_POST['password']) && $_POST['password']!='') ? $_POST['password'] : "");?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Status:</label>
							  <select class="form-control" name = "status" id="status" tabindex="2">
								<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : (isset($find_user['status']) && $find_user['status']=="Y" ? "selected='selected'" : ""));?>>Aktiv</option>
								<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : (isset($find_user['status']) && $find_user['status']=="N" ? "selected='selected'" : ""));?>>Inaktiv</option>
							  </select>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "edit_branch_login" tabindex="3"><b>Niederlassung &auml;ndern Anmelden</b></button>
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