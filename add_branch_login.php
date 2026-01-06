<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['branch_id']) && $_GET['branch_id']!='')
	{
		$user_id=substr(base64_decode($_GET['branch_id']), 0, -5);
		$find_user= find('first', USERS, '*', "WHERE id=:id", array(':id'=>$user_id));
		if(!empty($find_user))
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Zweig ID.';
			header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Zweig ID fehlt.';
		header('location:'.DOMAIN_NAME_PATH.'branch.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
	if(isset($_POST['create_branch_login']))
	{
		//print_r($_POST);
		if(isset($_POST['email_address']) && $_POST['email_address']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'E-Mail erforderlich.';
		}
		else
		{
			$find_branch_login=find('first', USERS, 'id, branch_name, email_address', "WHERE email_address=:email_address", array(':email_address'=>stripcleantohtml($_POST['email_address'])));
			if(empty($find_branch_login))
			{
				$user_id=substr(base64_decode($_GET['branch_id']), 0, -5);
				$pass=create_password(6);
				$fields="user_type, parent_id, email_address, password, status";
				$values=":user_type, :parent_id, :email_address, :password, :status";
				$execute=array(':user_type'=>'E',
					':parent_id'=>$user_id,
					':email_address'=>stripcleantohtml($_POST['email_address']),
					':password'=>md5($pass),
					':status'=>'Y'
				);
				$add_branch_login = save(USERS, $fields, $values,$execute);
				if($add_branch_login > 0)
				{
					$mail_Body = "Dear ,<br/><br/>Thank you for creating an account as branch. Your login details for you account are ,<br/><br/>"."<b>Email: ".$_POST['email_address']."<br/><br/>Password: ".$pass."</b><br/><br/>Regards,<br/>Administrator.";
					Send_HTML_Mail($_POST['email_address'], ADMIN_EMAIL, '', 'New login details', $mail_Body);
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Zweig Login erfolgreich hinzugefügt.';
					header('location:'.DOMAIN_NAME_PATH.'branch.php');
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
			$("#add_branch_login").validationEngine();
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
					<h3 class="panel-title">Erstellen neuer Zweig Anmelden</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="add_branch_login" name="add_branch_login" method="post" enctype="multipart/form-data" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> E-Mail-Addresse:</label>
							  <input class="form-control validate[required, custom[email]]" placeholder="E-Mail-Addresse" type="text" name="email_address" id="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : "");?>" data-errormessage-value-missing="E-Mail ist erforderlich!" maxlength="255" tabindex="1"/>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							  <button class="button" name = "create_branch_login" tabindex="2"><b>Erstellen neuer Zweig Anmelden</b></button>
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