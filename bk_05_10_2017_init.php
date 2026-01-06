<?php
session_start();
include_once('includes/config.php');
include_once('includes/dbconnection.php');
include_once('includes/database_tables.php');
include_once('includes/common_function.php');
$link = Db_Connect();
if(!$link)
{
	exit;
}
$current_page_name = basename($_SERVER['PHP_SELF']);
if($current_page_name!="create_user.php")
{
	if(isset($_SESSION['notice']))
		unset($_SESSION['notice']);
	if(isset($_SESSION['notice_date']))
		unset($_SESSION['notice_date']);
	if(isset($_SESSION['medical_log']))
		unset($_SESSION['medical_log']);
	if(isset($_SESSION['medical_date']))
		unset($_SESSION['medical_date']);
}
$admin_privilege=$branch_privilege=$user_privilege=false;
if(isset($_SESSION['logged_user_type']) && $_SESSION['logged_user_type']=='A')
{
	$admin_privilege=true;
}
else if(isset($_SESSION['logged_user_type']) && ($_SESSION['logged_user_type']=='B' || $_SESSION['logged_user_type']=='E'))
{
	$branch_privilege=true;
}
else if(isset($_SESSION['logged_user_type']) && $_SESSION['logged_user_type']=='U')
{
	$user_privilege=true;
}
function check_login()
{
	if(!isset($_SESSION['logged_user_id']) || !isset($_SESSION['logged_user_type']))
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'You are unauthorized to view this page.';
		header("location:".DOMAIN_NAME_PATH."index.php");
		exit;
	}
}
function logged_user()
{
	if(isset($_SESSION['logged_user_id']) && isset($_SESSION['logged_user_type']))
	{
		$_SESSION['SET_TYPE'] = 'success';
		$_SESSION['SET_FLASH'] = 'You are already logged in.';
		header("location:".DOMAIN_NAME_PATH."listing.php");
		exit;
	}
}
function has_privilege()
{
	$current_page_name = basename($_SERVER['PHP_SELF']);
	$admin_privilege=$branch_privilege=$user_privilege=false;
	if(isset($_SESSION['logged_user_type']) && $_SESSION['logged_user_type']=='A')
	{
		$admin_privilege=true;
	}
	else if(isset($_SESSION['logged_user_type']) && ($_SESSION['logged_user_type']=='B' || $_SESSION['logged_user_type']=='E'))
	{
		$branch_privilege=true;
	}
	else if(isset($_SESSION['logged_user_type']) && $_SESSION['logged_user_type']=='U')
	{
		$user_privilege=true;
	}
	if($user_privilege==true)
	{
		if($current_page_name=="notice.php" || $current_page_name=="appointment.php" || $current_page_name=="fetch_event.php" || $current_page_name=="view_edit_appointment.php")
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'You are unauthorized to view this page.';
			header("location:".DOMAIN_NAME_PATH."notice.php");
			exit;
		}
	}
	else if($branch_privilege==true)
	{
		if($current_page_name=="create_user.php" || $current_page_name=="listing.php" || $current_page_name=="edit_contact.php" || $current_page_name=="delete_user_doc.php" || $current_page_name=="create_notice.php" || $current_page_name=="notice.php" || $current_page_name=="edit_notice.php" || $current_page_name=="set_notice_status.php" || $current_page_name=="appointment.php" || $current_page_name=="create_appointment.php" || $current_page_name=="edit_appointment.php" || $current_page_name=="fetch_event.php" || $current_page_name=="view_edit_appointment.php" || $current_page_name=="find_user_name.php" || $current_page_name=="call-back-list.php")
		{
			//do nothing
		}
		else
		{
			$_SESSION['SET_TYPE'] = 'error';
			$_SESSION['SET_FLASH'] = 'You are unauthorized to view this page.';
			header("location:".DOMAIN_NAME_PATH."listing.php");
			exit;
		}
	}
	else if($admin_privilege==true)
	{
		//do nothing
	}
	else
	{
		$_SESSION['SET_TYPE'] = 'error';
		$_SESSION['SET_FLASH'] = 'You are unauthorized to view this page.';
		header("location:".DOMAIN_NAME_PATH."listing.php");
		exit;
	}
}

$option_array=array(
	"Assistant"=>'Assistant',
	"Business"=>'Business',
	"Business 2"=>'Business 2',
	"Business Fax"=>'Business Fax',
	"Call back"=>'Call back',
	"Car"=>'Car',
	"Company"=>'Company',
	"Home"=>'Home',
	"Home 2"=>'Home 2',
	"Home Fax"=>'Home Fax',
	"ISDN"=>'ISDN',
	"Mobile"=>'Mobile',
	"Other"=>'Other',
	"Other Fax"=>'Other Fax',
	"Pager"=>'Pager',
	"Primary"=>'Primary',
	"Radio"=>'Radio',
	"Telax"=>'Telax',
	"TTY/TDD"=>'TTY/TDD'
);
$img_ext_array=array('jpg', 'jpeg', 'png', 'gif');
$file_ext_array=array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'php');
$initial_arr=array(
	"Herr"=>'Herr.',
	"Frau"=>'Frau.'
	);
$appointment_categories=array(
	"#0000FF"=>'Beratung',
	"#FF0000"=>'Feiertags',
	"#006400"=>'Ganzkoerper',
	"#FFE4E1"=>'Gesicht',
	"#000000"=>'Maenner',
	"#90EE90"=>'Oberkoerper',
	"#90EE90"=>'Unterkoerper'
);

ob_start();
?>
<div style="margin-bottom:10px;padding-bottom:10px;">
	<div style="width:50%;float:left;">
		<h3 style="margin: 10px 0px;color: blue;">Lorem Ipsum is simply dummy</h3>
		text of the printing<br/>
		typesetting industry
	</div>
	<div style="width:40%;float:right;">
		<img src="img/no_Image.png" style="width:auto;height:89px;">
	</div>
	<div style="clear:both"></div>
</div>
<?php
$pdf_header=ob_get_clean();
ob_start();
?>
<div style="border-top:1px solid #999;padding:10px 0px;">
	<div style="width:30%;float:left;">
		Lorem Ipsum is simply<br/>
		dummy text of the <br/>
	</div>
	<div style="width:30%;float:left;">
		Tel: Lorem Ipsum is simply<br/>
		Fax: dummy text of the <br/>
		Email: dummy text of the <br/>
	</div>
	<div style="width:30%;float:left;">
		Lorem Ipsum is simply<br/>
		dummy text of the <br/>
		printing and typesetting<br/>
		industry
	</div>
	<div style="clear:both"></div>
</div>
<?php
$pdf_footer=ob_get_clean();

function change_date_format($date)
{
	if($date!="" && $date!="0000-00-00" && $date!=NULL)
	{
		return date("d.m.Y", strtotime($date));
	}
	else
	{
		return "";
	}
}
function change_date_time_format($date_time, $cur_format)
{
	if($date_time!="" && $date_time!="0000-00-00 00:00:00" && $date_time!=NULL)
	{
		$date_time_obj=date_create_from_format($cur_format, $date_time);
		$final_format=date_format($date_time_obj, "d.m.Y H:i");
		return $final_format;
	}
	else
	{
		return "N/A";
	}
}
function generateinvoiceid($str)
{
	$max_len=4;
	$str_req="BB".sprintf("%04s", $str);
	return $str_req;
}
?>