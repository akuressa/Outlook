<?php
	include_once('init.php');
	check_login();
	has_privilege();
	if(isset($_GET['del_id']) && $_GET['del_id']!='')
	{
		$appointment_message_id=substr(base64_decode($_GET['del_id']), 0, -5);
		$execute=array(':id'=>$appointment_message_id);
		$find_appointment_message= find('first', APPOINMENT_SHORT_MESSAGE, 'id', "WHERE id=:id", $execute);
		if(!empty($find_appointment_message))
		{
			$del_rcd=delete(APPOINMENT_SHORT_MESSAGE, 'WHERE id=:id', array(':id'=>$appointment_message_id));
			if($del_rcd==true)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Kurznachricht erfolgreich gelöscht.';
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
			header('location:'.DOMAIN_NAME_PATH.'view_edit_appointment.php?appointment_id='.$_GET['appointment_id']);
			exit;
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Kurznachricht-ID.';
			header('location:'.DOMAIN_NAME_PATH.'view_edit_appointment.php?appointment_id='.$_GET['appointment_id']);
			exit;
		}
	}
	if(isset($_POST['btn_add_msg']))
	{
		if(isset($_POST['message']) && $_POST['message']=='')
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Nachricht benötigt.';
		}
		else
		{
			$appointment_id=substr(base64_decode($_GET['appointment_id']), 0, -5);
			$fields="appointment_id, message, date";
			$values=":appointment_id, :message, :date";
			$execute=array(
				':message'=>stripcleantohtml($_POST['message']),
				':appointment_id'=>$appointment_id,
				':date'=>date("Y-m-d")
			);
			$add_message = save(APPOINMENT_SHORT_MESSAGE, $fields, $values,$execute);
			if($add_message > 0)
			{
				$_SESSION['SET_TYPE'] = 'success';
				$_SESSION['SET_FLASH'] = 'Kurznachricht erfolgreich hinzugef&uuml;gt.';
				header('location:'.DOMAIN_NAME_PATH.'view_edit_appointment.php?appointment_id='.$_GET['appointment_id']);
				exit;
			}
			else
			{
				$_SESSION['SET_TYPE'] = 'error';
				$_SESSION['SET_FLASH'] = 'Wir sind mit ein Problem. Bitte versuch es sp&auml;ter.';
			}
		}
	}
	if(isset($_GET['appointment_id']) && $_GET['appointment_id']!='')
	{
		$appointment_where="";$appointment_execute=array();
		if($user_privilege==true)
		{
			$appointment_where=" AND a.user_id=:user_id ";
			$appointment_execute=array(":user_id"=>$_SESSION['logged_user_id']);
		}
		else if($branch_privilege==true)
		{
			$appointment_where=" AND a.branch_id=:branch_id ";
			$appointment_execute=array(":branch_id"=>$_SESSION['logged_user_id']);
		}
		$appointment_id=substr(base64_decode($_GET['appointment_id']), 0, -5);
		$table=APPOINMENTS." as a, ".USERS." as u_1, ".USERS." as u_2";
		$where="WHERE a.id=:id AND a.user_id=u_1.id AND a.branch_id=u_2.id".$appointment_where;
		$fields="a.id, a.subject, a.location, a.start_date, a.end_date, a.description, u_1.person_name as customer, u_2.branch_name";
		$execute=array(':id'=>$appointment_id);
		$execute=array_merge($execute, $appointment_execute);
		$find_appointment= find('first', $table, $fields, $where, $execute);
		if(!empty($find_appointment))
		{
			//do nothing
			$find_short_message= find('all', APPOINMENT_SHORT_MESSAGE, "*", "WHERE appointment_id=:appointment_id ORDER BY date DESC", array(':appointment_id'=>$appointment_id));
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'Ung&uuml;ltige Termin-ID.';
			header('location:'.DOMAIN_NAME_PATH.'appointment.php');
			exit;
		}
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'Termin-ID fehlt.';
		header('location:'.DOMAIN_NAME_PATH.'appointment.php');
		exit;
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
			if(confirm('Sind Sie sicher, dass Sie diesen Datensatz wirklich l\xD6schen?'))
			{
				window.location.href = '<?php echo(DOMAIN_NAME_PATH)?>view_edit_appointment.php?appointment_id=<?php echo $_GET['appointment_id'];?>&del_id='+id;
			}
		}
		$(function(){
			$("#short_msg_form").validationEngine();
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
					<h3 class="panel-title">Anzeigen und Bearbeiten Termin</h3>
				</div>
				<div class=" content">
					<br/>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>Kunde</th>
								<th>Filiale</th>
								<th>Kurze Beschreibung</th>
								<th>Ort</th>
								<th>Anfangsdatum</th>
								<th>Enddatum</th>
								<th style="text-align:center">Aktion</th>
							</tr>
						</thead>
						<tbody> 
					<?php
						if(isset($find_appointment) && !empty($find_appointment)):
					?>
							<tr>
								<td><?php echo $find_appointment['customer'];?></td>
								<td><?php echo $find_appointment['branch_name'];?></td>
								<td><?php echo $find_appointment['subject'];?></td>
								<td><?php echo $find_appointment['location'];?></td>
								<td><?php echo change_date_format($find_appointment['start_date']);?></td>
								<td><?php echo change_date_format($find_appointment['end_date']);?></td>
								<td style="text-align:center">
									<?php
										if($admin_privilege==true)
										{
									?>
									<a href="edit_appointment.php?appointment_id=<?php echo(base64_encode($find_appointment['id'].IDHASH));?>"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;&nbsp;
									<?php
										}
									?>
									<a href="javascript:void(0)" onclick="$(this).closest('tr').next('tr').slideToggle('slow');"><span class="glyphicon glyphicon-info-sign"></span></a>

								</td>
							</tr>
							<tr>
								<td colspan="7">
									<b>Beschreibung:</b><br/><?php echo nl2br($find_appointment['description']);?>
								</td>
							</tr>
						<?php
						else:
						?>
							<tr>
								<td colspan="7" class="no_record_cls">Kein Eintrag gefunden</td>
							</tr>
						<?php
						endif;
						?>
						</tbody>
					</table>
					<h3 class="panel-title">&nbsp;&nbsp;Liste der Short Message</h3>
					<br/>
					<table id="myTable" class="table tablesorter">
						<thead class="add_new">
							<tr>
								<th>Nachricht</th>
								<th>Datum</th>
								<?php
									if($admin_privilege==true || $branch_privilege==true)
									{
								?>
								<th style="text-align:center">Aktion</th>
								<?php
									}
								?>
							</tr>
						</thead>
						<tbody>
						<?php
							if(isset($find_short_message) && !empty($find_short_message)):
								foreach($find_short_message as $message_key=>$message_value):
						?>
							<tr>
								<td><?php echo nl2br($message_value['message']);?></td>
								<td><?php echo change_date_format($message_value['date']);?></td>
								<?php
									if($admin_privilege==true || $branch_privilege==true)
									{
								?>
								<td style="text-align:center">
									<a href="javascript:void(0)" onclick = "delete_record('<?php echo(base64_encode($message_value['id'].IDHASH));?>');" title="Delete Short Message"><span class="glyphicon glyphicon-trash"></span></a>
								</td>
								<?php
									}
								?>
							</tr>
						<?php
								endforeach;
							else:
						?>
							<tr>
								<td colspan="<?php echo($admin_privilege==true || $branch_privilege==true ? "3" : "2");?>" class="no_record_cls">Kein Eintrag gefunden</td>
							</tr>
						<?PHP
							endif;
						?>
						</tbody>
					</table>
					<div class="form-group" style="padding:3px;text-align:right;">
					  <a href = "javascript:void(0)" onclick="$('#message_main_div').slideToggle('slow');"><button class="button" name = "btn_create"><b>Neue Nachrichat erstellen</b></button></a>
					</div>
					<div class="message_main_div" id="message_main_div">
						<div class="col-md-3">&nbsp;</div>
						<div class="col-md-6">
							<form method="post" action="" name="short_msg_form"  id="short_msg_form">
								<div class="form-group" style="padding:6px;text-align:left;">
									<font color = "red">*</font> Felder sind Pflichtfelder.
								</div>
								<div class="form-group" style="padding:6px">
								  <label><font color = "red">*</font> Nachricht</label>
								  <textarea name="message" class="form-control validate[required]" Placeholder="Nachricht" id="message" data-errormessage-value-missing="Nachricht ben&ouml;tigt" tabindex="1"><?php echo((isset($_POST['message']) && $_POST['message']!='') ? $_POST['message'] : "");?></textarea>
								</div>
								<div class="form-group" style="padding:3px;text-align:right;">
								  <button class="button" name = "btn_add_msg" tabindex="2"><b>Nachrichten</b></button>
								</div>
							</form>
						</div>
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