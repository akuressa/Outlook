<?php
	include_once('init.php');
	check_login();
	has_privilege();
	
	$user_id=$_SESSION['logged_user_id'];
	$find_user= find('first', USERS, '*', "WHERE id=:id", array(':id'=>$user_id));
	if(!empty($find_user))
	{
		if(isset($_POST['edit_admin_btn']))
		{
			if(isset($_POST['email_address']) && $_POST['email_address']=='')
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'E-Mail ist erforderlich.';
			}
			else
			{
				$user_id=$_SESSION['logged_user_id'];
				$find_admin_login=find('first', USERS, 'id, email_address', "WHERE email_address=:email_address AND id <> :id", array(':email_address'=>stripcleantohtml($_POST['email_address']), ':id'=>$user_id));
				if(empty($find_admin_login))
				{
					$pass_val="";$pass_execute=array();
					if(isset($_POST['password']) && $_POST['password']!="")
					{
						$pass_val=", password=:password";
						$pass_execute=array(':password'=>md5($_POST['password']));
					}
					$value_set="email_address=:email_address".$pass_val;
					$execute=array(
						':email_address'=>stripcleantohtml($_POST['email_address']),
						':id'=>$user_id
					);
					$execute=array_merge($execute, $pass_execute);
					$update_admin=update(USERS, $value_set, 'WHERE id=:id', $execute);
					if($update_admin == true)
					{
						$_SESSION['SET_TYPE'] = 'success';
						$_SESSION['SET_FLASH'] = 'Profil erfolgreich aktualisiert.';
					}
					else
					{
						$_SESSION['SET_TYPE'] = 'error';
						$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
					}
				}
				else
				{
					if($find_admin_login['email_address']==$_POST['email_address'])
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
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Ungültige Admin-ID.';
		header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#edit_admin_login").validationEngine();
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
					<h3 class="panel-title">Profil bearbeiten</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="edit_admin_login" name="edit_admin_login" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font>Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> E-Mail-Addresse:</label>
							  <input class="form-control validate[required, custom[email]]" placeholder="E-Mail-Addresse" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : (isset($find_user['email_address']) && $find_user['email_address']!="" ? $find_user['email_address'] : ""));?>" data-errormessage-value-missing="E-Mail ist erforderlich!" maxlength="255" tabindex="1"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Passwort:</label>
							  <input class="form-control" placeholder="Passwort" type="password" name="password" id="password" value="" tabindex="2"/>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "edit_admin_btn" tabindex="3"><b>Aktualisieren</b></button>
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