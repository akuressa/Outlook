<?php
	include_once('init.php');
	logged_user();
	$err_suc=$forget_err_suc="";
	$err_suc_1=$forget_err_suc_1="glyphicon-chevron-right";
	$err_suc_msg="Geben E-Mail und Passwort.";
	$forget_err_suc_msg="Geben Sie Ihre E-Mail.";
	if(isset($_POST['login_submit']))
	{
		if(isset($_POST['email_address']) && $_POST['email_address']=='')
		{
			$err_suc="error";
			$err_suc_1="glyphicon-remove error";
			$err_suc_msg="E-Mail ist erforderlich.";
		}
		else if(isset($_POST['password']) && $_POST['password']=='')
		{
			$err_suc="error";
			$err_suc_1="glyphicon-remove error";
			$err_suc_msg="Passwort wird ben&ouml;tigt.";
		}
		else
		{
			$where_clause="WHERE email_address=:email_address AND password=:password";
			$execute=array(':email_address'=>stripcleantohtml($_POST['email_address']), ':password'=>stripcleantohtml(md5($_POST['password'])));
			$find_user=find('first', USERS, 'id, email_address, user_type, person_name, parent_id, status', $where_clause, $execute);
			if(!empty($find_user))
			{
				if($find_user['status']=='Y')
				{
					if($find_user['user_type']=='E')
					{
						$find_user=find('first', USERS, 'id, email_address, user_type, person_name, parent_id, status', "WHERE id=:id", array(':id'=>$find_user['parent_id']));
						if(!empty($find_user))
						{
							if($find_user['status']=='Y')
							{
								$_SESSION['logged_user_id'] = $find_user['id'];
								$_SESSION['logged_email_address'] = $find_user['email_address'];
								$_SESSION['logged_user_type'] = $find_user['user_type'];
								$_SESSION['logged_person_name'] = $find_user['person_name'];
								$_SESSION['logged_parent_id'] = $find_user['parent_id'];
								if(isset($_POST['remember_me']) && $_POST['remember_me']=='Y')
								{
									setcookie("cookie_user_email", $_POST['email_address'], time() + (86400 * 30 * 365), "/");
									setcookie("cookie_user_password", $_POST['password'], time() + (86400 * 30 * 365), "/");
									setcookie("cookie_user_remember", $_POST['remember_me'], time() + (86400 * 30 * 365), "/");
								}
								$err_suc="success";
								$err_suc_1="glyphicon-ok success";
								$err_suc_msg="Erfolgreich angemeldet.";
								$_SESSION['SET_TYPE'] = 'success';
								$_SESSION['SET_FLASH'] = 'Sie sind erfolgreich angemeldet.';
								header('location:'.DOMAIN_NAME_PATH.'notice.php');
								exit;
							}
							else
							{
								$err_suc="error";
								$err_suc_1="glyphicon-remove error";
								$err_suc_msg="Ihr Konto ist inaktiv.";
							}
						}
						else
						{
							$err_suc="error";
							$err_suc_1="glyphicon-remove error";
							$err_suc_msg="Ung&uuml;ltige Zweig Login.";
						}
					}
					else
					{
						$_SESSION['logged_user_id'] = $find_user['id'];
						$_SESSION['logged_email_address'] = $find_user['email_address'];
						$_SESSION['logged_user_type'] = $find_user['user_type'];
						$_SESSION['logged_person_name'] = $find_user['person_name'];
						$_SESSION['logged_parent_id'] = $find_user['parent_id'];
						if(isset($_POST['remember_me']) && $_POST['remember_me']=='Y')
						{
							setcookie("cookie_user_email", $_POST['email_address'], time() + (86400 * 30 * 365), "/");
							setcookie("cookie_user_password", $_POST['password'], time() + (86400 * 30 * 365), "/");
							setcookie("cookie_user_remember", $_POST['remember_me'], time() + (86400 * 30 * 365), "/");
						}
						$err_suc="success";
						$err_suc_1="glyphicon-ok success";
						$err_suc_msg="Erfolgreich angemeldet.";
						$_SESSION['SET_TYPE'] = 'success';
						$_SESSION['SET_FLASH'] = 'Sie sind erfolgreich angemeldet.';
						header('location:'.DOMAIN_NAME_PATH.'notice.php');
						exit;
					}
				}
				else
				{
					$err_suc="error";
					$err_suc_1="glyphicon-remove error";
					$err_suc_msg="Ihr Konto ist inaktiv.";
				}
			}
			else
			{
				$err_suc="error";
				$err_suc_1="glyphicon-remove error";
				$err_suc_msg="Ung&uuml;ltige E-Mail / Passwort.";
			}
		}
	}
	else if(isset($_POST['forget_submit']))
	{
		if(isset($_POST['forget_email']) && $_POST['forget_email']=='')
		{
			$forget_err_suc="error";
			$forget_err_suc_1="glyphicon-remove error";
			$forget_err_suc_msg="E-Mail ist erforderlich.";
		}
		else
		{
			$check_exists_forgot = find('first', USERS, 'id, email_address, person_name', "WHERE email_address=:email_address", array(':email_address'=>stripcleantohtml($_POST['forget_email'])));
			if(!empty($check_exists_forgot))
			{
				$pass=create_password(6);
				$update_user=update(USERS, 'password=:password', 'WHERE id=:id', array(':id'=>$check_exists_forgot['id'], ':password'=>md5($pass)));
				if($update_user==true)
				{
					$mail_Body = "Dear ".$check_exists_forgot['person_name'].",<br/><br/>Your new account details .<br/><br/>"."<b>Email: ".$check_exists_forgot['email_address']."<br/><br/>New Password: ".$pass."</b><br/><br/>Regards,<br/>Administrator.";
					Send_HTML_Mail($check_exists_forgot['email_address'], ADMIN_EMAIL, '', 'New login details', $mail_Body);
					$_SESSION['SET_TYPE'] = 'success';
					$_SESSION['SET_FLASH'] = 'Passwort wurde an Ihre E-Mail gesendet wurde. Bitte überprüfen Sie Ihre E-Mail-Adresse ein.';
					$forget_err_suc="success";
					$forget_err_suc_1="glyphicon-ok success";
					$forget_err_suc_msg="Passwort wurde an Ihre E-Mail gesendet wurde.";
				}
				else
				{
					$forget_err_suc="error";
					$forget_err_suc_1="glyphicon-remove error";
					$forget_err_suc_msg="Internal error.";	
					$_SESSION['SET_TYPE'] = 'error';
					$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
				}
			}
			else
			{
				$forget_err_suc="error";
				$forget_err_suc_1="glyphicon-remove error";
				$forget_err_suc_msg="Ung&uuml;ltige E-Mail.";
			}
		}
	}
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('includes/header.php');?>
	<style>
		.form-control{
			/*margin-bottom:15px*/
		}
	</style>
	<script type="text/javascript">
	<!--
		$(function(){
			$("#login-form").validationEngine();
			$("#lost-form").validationEngine();
		});
	//-->
	</script>
</head>
<body style="background:#fff !important">
	<div class="container"> 
	<!-- BEGIN # BOOTSNIP INFO -->
		<div class="container">
			<div class="row">
			</div>
		</div>
	<!-- END # BOOTSNIP INFO -->

<!-- BEGIN # MODAL LOGIN -->
		<div>
			<div style=" width:300px; margin:5% auto">
				<div class="modal-content">
					
					<!-- Begin # DIV Form -->
					<div id="div-forms">
						<!-- Begin # Login Form -->
						<form id="login-form" action="" method="post" <?php echo(isset($_POST['forget_submit']) ? 'style="display:none;"' : "");?>>
							<div class="modal-body" >
								<div id="div-login-msg" class="<?php echo $err_suc;?>" style="margin-bottom:13px">
									<div id="icon-login-msg" class="glyphicon <?php echo $err_suc_1;?>"></div>
									<span id="text-login-msg"><?php echo $err_suc_msg;?></span>
								</div>
								<input id="login_username" class="form-control validate[required, custom[email]]" type="text" placeholder="E-Mail" name="email_address" value="<?php echo((isset($_POST['email_address']) && $_POST['email_address']!='') ? $_POST['email_address'] : (isset($_COOKIE['cookie_user_email']) && $_COOKIE['cookie_user_email']!="" ? $_COOKIE['cookie_user_email'] : ""));?>" data-errormessage-value-missing="E-Mail ist erforderlich." maxlength="255" tabindex="1"/>
								<input id="login_password" class="form-control validate[required]" type="password" placeholder="Passwort" name="password" data-errormessage-value-missing="Passwort wird ben&ouml;tigt" value="<?php echo(isset($_COOKIE['cookie_user_password']) && $_COOKIE['cookie_user_password']!="" ? $_COOKIE['cookie_user_password'] : "");?>" tabindex="2"/>
								
							</div>
							<div class="modal-footer">
								<div>
									<button type="submit" class="btn btn-primary btn-lg btn-block" name="login_submit" id="login_submit" tabindex="4">Anmeldung</button>
								</div>
								
							</div>
						</form>
						<!-- End # Login Form -->
						
						<!-- Begin | Lost Password Form -->
						<form id="lost-form" <?php echo(!isset($_POST['forget_submit']) ? 'style="display:none;"' : "");?> action="" method="post">
							<div class="modal-body">
								<div id="div-lost-msg" class="<?php echo $forget_err_suc;?>" style="margin-bottom:13px">
									<div id="icon-lost-msg" class="glyphicon <?php echo $forget_err_suc_1;?>"></div>
									<span id="text-lost-msg"><?php echo $forget_err_suc_msg;?></span>
								</div>
								<input id="lost_email" class="form-control validate[required, custom[email]]" type="text" placeholder="E-Mail"  name="forget_email" value="<?php echo((isset($_POST['forget_email']) && $_POST['forget_email']!='') ? $_POST['forget_email'] : "");?>" data-errormessage-value-missing="E-Mail ist erforderlich">
							</div>
							<div class="modal-footer">
								<div>
									<button type="submit" class="btn btn-primary btn-lg btn-block" name="forget_submit">Senden</button>
								</div>
								<div>
									<button id="lost_login_btn" type="button" class="btn btn-link">Anmeldung</button>
								</div>
							</div>
						</form>
						<!-- End | Lost Password Form -->
						
						<!-- Begin | Register Form -->
						<form id="register-form" style="display:none;">
							<div class="modal-body">
								<div id="div-register-msg" style="margin-bottom:13px">
									<div id="icon-register-msg" class="glyphicon glyphicon-chevron-right"></div>
									<span id="text-register-msg">Register an account.</span>
								</div>
								<input id="register_username" class="form-control" style="margin-bottom:13px"type="text" placeholder="Username (type ERROR for error effect)" required>
								<input id="register_email" class="form-control" type="text" placeholder="E-Mail" required>
								<input id="register_password" class="form-control" type="password" placeholder="Password" required>
							</div>
							<div class="modal-footer">
								<div>
									<button type="submit" class="btn btn-primary btn-lg btn-block">Register</button>
								</div>
								<div>
									<button id="register_login_btn" type="button" class="btn btn-link">Log In</button>
									<button id="register_lost_btn" type="button" class="btn btn-link">Lost Password?</button>
								</div>
							</div>
						</form>
						<!-- End | Register Form -->
						
					</div>
					<!-- End # DIV Form -->
				</div>
			</div>
		</div>
    <!-- END # MODAL LOGIN -->
	</div> 
	<script type="text/javascript">
	<!--
		/* #####################################################################
   #
   #   Project       : Modal Login with jQuery Effects
   #   Author        : Rodrigo Amarante (rodrigockamarante)
   #   Version       : 1.0
   #   Created       : 07/29/2015
   #   Last Change   : 08/04/2015
   #
   ##################################################################### */
   
	$(function() {
		var $formLogin = $('#login-form');
		var $formLost = $('#lost-form');
		var $formRegister = $('#register-form');
		var $divForms = $('#div-forms');
		var $modalAnimateTime = 300;
		var $msgAnimateTime = 150;
		var $msgShowTime = 2000;
		$('#login_register_btn').click( function () { modalAnimate($formLogin, $formRegister) });
		$('#register_login_btn').click( function () { modalAnimate($formRegister, $formLogin); });
		$('#login_lost_btn').click( function () { modalAnimate($formLogin, $formLost); });
		$('#lost_login_btn').click( function () { modalAnimate($formLost, $formLogin); });
		$('#lost_register_btn').click( function () { modalAnimate($formLost, $formRegister); });
		$('#register_lost_btn').click( function () { modalAnimate($formRegister, $formLost); });
		
		function modalAnimate ($oldForm, $newForm) {
			var $oldH = $oldForm.height();
			var $newH = $newForm.height();
			$divForms.css("height",$oldH);
			$oldForm.fadeToggle($modalAnimateTime, function(){
				$divForms.animate({height: $newH}, $modalAnimateTime, function(){
					$newForm.fadeToggle($modalAnimateTime);
				});
			});
		}
	});
	//-->
	</script>
</body>
</html>
<?php include_once('includes/footer.php');?>