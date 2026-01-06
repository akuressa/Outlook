<?php
	include_once('init.php');
	check_login();
	has_privilege();
	$branch_list = find('all', USERS, "id, branch_name", "WHERE user_type=:user_type AND status=:status ORDER BY branch_name ASC", array(':user_type'=>'B', ':status'=>'Y'));
	if(isset($_GET['notice_id']) && $_GET['notice_id']!='')
	{
		$branch_where="";$branch_execute=array();
		if($branch_privilege==true)
		{
			$branch_where=" AND brunch_id=:brunch_id OR brunch_id=:brunch_id_1";
			$branch_execute=array(":brunch_id"=>$_SESSION['logged_user_id'], ":brunch_id_1"=>0);
		}
		$notice_id=substr(base64_decode($_GET['notice_id']), 0, -5);
		$execute=array(':id'=>$notice_id);
		$execute=array_merge($execute, $branch_execute);
		$find_notice= find('first', NOTICE_BOARD, '*', "WHERE id=:id".$branch_where, $execute);
		if(!empty($find_notice))
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Bemerken id.';
			header('location:'.DOMAIN_NAME_PATH.'notice.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Bemerken id fehlenden.';
		header('location:'.DOMAIN_NAME_PATH.'notice.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
		exit;
	}
	if(isset($_POST['edit_notice']))
	{
		if(isset($_POST['heading']) && $_POST['heading']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Bemerken &uuml;berschrift ist erforderlich.';
		}
		else if(isset($_POST['content']) && $_POST['content']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Bemerken inhalt ist erforderlich.';
		}
		else if(isset($_POST['start_date']) && $_POST['start_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Startdatum ist erforderlich.';
		}
		else if(isset($_POST['end_date']) && $_POST['end_date']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Schlussdatum ist erforderlich.';
		}
		else
		{
			$notice_id=substr(base64_decode($_GET['notice_id']), 0, -5);
			if($admin_privilege==true)
			{
				$branch_values=", brunch_id=:brunch_id";
				$branch_execute=array(':brunch_id'=>$_POST['brunch_id']);
			}
			else
			{
				$branch_values="";
				$branch_execute=array();
			}
			$set_values="heading=:heading, content=:content, start_date=:start_date, end_date=:end_date, status=:status".$branch_values;
			$execute=array(
				':heading'=>stripcleantohtml($_POST['heading']),
				':content'=>stripcleantohtml($_POST['content']),
				':start_date'=>stripcleantohtml(date("Y-m-d", strtotime($_POST['start_date']))),
				':end_date'=>stripcleantohtml(date("Y-m-d", strtotime($_POST['end_date']))),
				':status'=>stripcleantohtml($_POST['status']),
				':id'=>$notice_id
			);
			$execute=array_merge($execute, $branch_execute);
			$update_user=update(NOTICE_BOARD, $set_values, 'WHERE id=:id', $execute);
			if($update_user == true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Bemerken erfolgreich aktualisiert.';
				header('location:'.DOMAIN_NAME_PATH.'notice.php'.(isset($_GET["page"]) && $_GET["page"]!="" ? "?page=".$_GET["page"] : ""));
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
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
			$("#edit_notice").validationEngine();
			$('#start_date').datetimepicker({
				timepicker:false,
				format:'d.m.Y',
				//formatDate:'d.m.Y',
				minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				//maxDate:'+1970/01/02' // and tommorow is maximum date calendar
				onShow:function( ct ){
					//alert(ct);
					this.setOptions({
						//maxDate:$('#end_date').val()?$('#end_date').val():false
					})
				}
			});
			$('#end_date').datetimepicker({
				timepicker:false,
				format:'d.m.Y',
				//formatDate:'d.m.Y',
				minDate:'-1970/01/01', //yesterday is minimum date(for today use 0 or -1970/01/01)
				//maxDate:'+1970/01/02' // and tommorow is maximum date calendar
				onShow:function( ct ){
					//alert($('#start_date').val());
					this.setOptions({
						minDate:$('#start_date').val()?$('#start_date').val():false
					})
				}
			});
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
					<h3 class="panel-title">Bearbeiten und Datenschutz</h3>
				</div>
				<div class=" content">
					<div class="col-md-3">&nbsp;</div>
					<div class="col-md-6">
						<form id="edit_notice" name="edit_notice" method="post" action="">
							<div class="form-group" style="padding:6px;text-align:left;">
								<font color = "red">*</font> Felder sind Pflichtfelder.
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Bemerkungstitel:</label>
							  <input class="form-control validate[required]" placeholder="Bemerkungstitel" type="text" id="heading" name="heading" maxlength="255"  data-errormessage-value-missing="Bemerkungstitel ist erforderlich" tabindex="1" value="<?php echo((isset($_POST['heading']) && $_POST['heading']!='') ? $_POST['heading'] : (isset($find_notice['heading']) && $find_notice['heading']!="" ? $find_notice['heading'] : ""));?>"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Bescheribung :</label>
							  <textarea class="form-control validate[required]" placeholder="Bescheribung " id="content" name="content" data-errormessage-value-missing="Bescheribung  erforderlich" tabindex="2"><?php echo((isset($_POST['content']) && $_POST['content']!='') ? $_POST['content'] : (isset($find_notice['content']) && $find_notice['content']!="" ? $find_notice['content'] : ""));?></textarea>
							</div>
							<?php
								if($admin_privilege==true)
								{
							?>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Bitte ordn Sie lhre Bemerkung zu:</label>
							  <select class="form-control validate[required]" name="brunch_id" id="brunch_id" data-errormessage-value-missing="Bitte w&auml;hlen Sie eine Zweig" tabindex="3">
								<option value = "0" <?php echo((isset($_POST['brunch_id']) && $_POST['brunch_id']=='0') ? "selected='selected'" : (isset($find_notice['brunch_id']) && $find_notice['brunch_id']=="0" ? "selected='selected'" : ""));?>>All</option>
								<?php
									if(!empty($branch_list))
									{
										foreach($branch_list as $branch_key=>$branch_value)
										{
								?>
								<option value = "<?php echo $branch_value['id'];?>" <?php echo(isset($_POST['brunch_id']) && $_POST['brunch_id']==$branch_value['id'] ? "selected='selected'" : (isset($find_notice['brunch_id']) && $find_notice['brunch_id']==$branch_value['id'] ? "selected='selected'" : ""));?>><?php echo $branch_value['branch_name'];?></option>
								<?php
										}
									}
								?>
							  </select>
							</div>
							<?php
								}
							?>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Startdatum:</label>
							  <input class="form-control validate[required]" placeholder="Startdatum" type="text" id = "start_date" readonly name="start_date" value="<?php echo((isset($_POST['start_date']) && $_POST['start_date']!='') ? $_POST['start_date'] : (isset($find_notice['start_date']) && $find_notice['start_date']!="" ? date("d.m.Y", strtotime($find_notice['start_date'])) : ""));?>" tabindex="4"  data-errormessage-value-missing="Startdatum ist erforderlich"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label><font color = "red">*</font> Schlussdatum:</label>
							  <input class="form-control validate[required]" placeholder="Schlussdatum" type="text" id = "end_date" readonly name="end_date" value="<?php echo((isset($_POST['end_date']) && $_POST['end_date']!='') ? $_POST['end_date'] : (isset($find_notice['end_date']) && $find_notice['end_date']!="" ? date("d.m.Y", strtotime($find_notice['end_date'])) : ""));?>" tabindex="5" data-errormessage-value-missing="Schlussdatum ist erforderlich"/>
							</div>
							<div class="form-group" style="padding:6px">
							  <label>Status:</label>
							  <select class="form-control" name = "status" id="status" tabindex="6">
								<option value = "Y" <?php echo(isset($_POST['status']) && $_POST['status']=="Y" ? "selected='selected'" : (isset($find_notice['status']) && $find_notice['status']=='Y' ? "selected='selected'" : ""));?>>Aktiv</option>
								<option value = "N" <?php echo(isset($_POST['status']) && $_POST['status']=="N" ? "selected='selected'" : (isset($find_notice['status']) && $find_notice['status']=='N' ? "selected='selected'" : ""));?>>Inaktiv</option>
							  </select>
							</div>
							<div class="form-group" style="padding:3px;text-align:right;">
							<button class="button" name = "cncel" tabindex="7" onclick="header.locltion.href='notice.php'"><b>Abbrechen</b></button>
							  <button class="button" name = "edit_notice" tabindex="7"><b>Speichern</b></button>
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