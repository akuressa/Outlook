<?php
/*
=========================================================================================================================
COPYRIGHT: IDIGITALMIND ORGANIZATION
PRODUCT NAME: DOCUMENT MANAGEMENT SYSTEM
PAGE NAME: COMMON_FUNCTION.PHP
PAGE FUNCTIONALITY: THIS FILE CONSISTS OF DEFINATIONS OF ALL FUNCTIONS THAT ARE BEEN USED THROUGHOUT THE PRODUCT.
=========================================================================================================================
*/

/*
function find()

* fetch record from database
* @param string type: Can be all / first
* @param string table: Name of the table
* @param string value: Table fields values that needs to be fetched (filed1, field2)
* @param string where_clause: Where conditions based on which data needs to be fetched (WHERE field1=:defined_value1 AND field2=:defined_value2 OR field3=:defined_value3)
* @param array execute: Consists of actual values of defined variables (array(':defined_value1'=>$_POST['POST_DATA1'], ':defined_value2'=>$_POST['POST_DATA2'],))
* @returns an array of fetched records.
* @example1: find('first', 'table_name', 'field1, field2', 'WHERE field1:=defined_value1 AND field2:=defined_value2', array(':defined_value1'=>$_POST['DATA1'], ':defined_value2'=>$_POST['DATA2'])); 
* @example2: find('all', 'table_name', 'field1, field2', 'WHERE field1:=defined_value1 AND field2:=defined_value2', array(':defined_value1'=>$_POST['DATA1'], ':defined_value2'=>$_POST['DATA2']));
*/

function find($type, $table, $value='*', $where_clause, $execute)
{
	global $db;

	if($where_clause)
	{
		$sql = "SELECT ".$value." FROM ".$table." ".$where_clause."";
	}
	else
	{
		$sql = "SELECT ".$value." FROM ".$table;
	}
	//echo $sql;print_r($execute);
	$prepare_sql = $db->prepare($sql);
	foreach($execute As $key=>$value)
	{
		$execute[$key] = stripslashes($value);
	}
	$prepare_sql->execute($execute);

	if($prepare_sql->errorCode() == 0)
	{
		if($type == 'first')
		{
			//fetch single record from database
			$result = $prepare_sql->fetch();
		}
		else if($type == 'all')
		{
			//fetch multiple record from database
			$result = $prepare_sql->fetchAll();
		}
		return $result;
	} 
	else
	{
		$errors = $prepare_sql->errorInfo();
		echo '<pre>';
		print_r($errors[2]);
		echo '</pre>';
		die();
	}
}

/*
function save()

* insert record into database
* @param string table: Name of the table
* @param string fields: Lists of fields name of the corresponding database table within which data needs to be added (field1, field2)
* @param string values: Lists of defined values variables that will be used in ececute array reflect corresponding table fields (:defined_value1, :defined_value2)
* @param array execute: Lists of defined values variables along with the actual values that needs to be added within the database tables (array(':defined_value1'=>$_POST['POST_DATA1'], ':defined_value2'=>$_POST['POST_DATA2']))
* @ returns last inserted id.
* @ example: save('table_name', 'fields1, fields2', ':defined_value1, :defined_value2', array(':defined_value1'=>$_POST['POST_DATA1'], ':defined_value2'=>$_POST['POST_DATA2']))
*/

function save($table, $fields, $values, $execute)
{
	global $db;
	$result = false;
	$sql = "INSERT INTO ".$table." (".$fields.") VALUES (".$values.")";
	$prepare_sql = $db->prepare($sql);
	foreach($execute As $key=>$value){
		$execute[$key] =  ($value===NULL ? NULL : stripslashes($value));
	}
	$prepare_sql->execute($execute);
	if($prepare_sql->errorCode() == 0) 
	{
		$result = $db->lastInsertId();
		return $result;
	} 
	else 
	{
		$errors = $prepare_sql->errorInfo();
		echo '<pre>';
		print_r($errors[2]);
		echo '</pre>';
		die();
	}
}

/*
function update()

* update record into database
* @param string table: Name of the table
* @param string set fields: Database tables fields names that needs to be updated ('fields1=:defined_value1, fields2=:defined_value2')
* @param string where_clause: Where condition based on which the database table will be updated ('WHERE fields1=:defined_value1 AND WHERE fields2=:defined_value2')
* @param array execute:  Lists of defined values variables along with the actual values that needs to be updated within the database tables (array(':defined_value1'=>$_POST['POST_DATA1'], ':defined_value2'=>$_POST['POST_DATA2']))
* @return true or false
* @ example: update('table_name', 'fields1=:defined_value1, fields2=:defined_value2', 'WHERE fields1=:defined_value1 AND fields2=:defined_value2', array(':defined_value1'=>$_POST['POST_DATA1'], ':defined_value2'=>$_POST['POST_DATA2']))
*/

function update($table, $set_value, $where_clause, $execute)
{
	global $db;

	$sql = "UPDATE ".$table." SET ".$set_value." ".$where_clause."";
	
	
	$prepare_sql = $db->prepare($sql);
	foreach($execute As $key=>$value){
		$execute[$key] =  ($value===NULL ? NULL : stripslashes($value));
		//echo '<pre>';
		//print_r($execute);
		//echo '</pre>';
	}
	//exit;
	$prepare_sql->execute($execute);
	if($prepare_sql->errorCode() == 0) 
	{
		return true;
	} 
	else 
	{
		$errors = $prepare_sql->errorInfo();
		echo '<pre>';
		print_r($errors[2]);
		echo '</pre>';
		die();
	}
}

/*
function delete()

* delete record from database
* @param string table: Name of the table
* @param string where_clause: Where condition based on which the database table will be updated ('WHERE fields1=:defined_value1 AND WHERE fields2=:defined_value2')
* @param array execute:  Lists of defined values variables along with the actual values that needs to be updated within the database tables (array(':defined_value1'=>$_POST['POST_DATA1'], ':defined_value2'=>$_POST['POST_DATA2']))
* @return true or false
* @ example: delete('table_name', 'WHERE fields1=:defined_value1', array(':defined_value1'=>$_POST['POST_DATA1']))
*/

function delete($table, $where_clause, $execute)
{
	global $db;

	$sql = "DELETE FROM ".$table." ".$where_clause."";
	$prepare_sql = $db->prepare($sql);
	$prepare_sql->execute($execute);

	if($prepare_sql->errorCode() == 0) 
	{
		return true;
	} 
	else 
	{
		$errors = $prepare_sql->errorInfo();
		echo '<pre>';
		print_r($errors[2]);
		echo '</pre>';
		die();
	}
}

/*
function logout()

* logout from application
* @param string destination path
* @return: NONE. Redirect user to the provided destination path
*/

function logout($destinationPath)
{
	if(count($_SESSION))
	{
		foreach($_SESSION AS $key=>$value)
		{
			unset($_SESSION[$key]);
		}
		session_destroy();
	}
	session_start();
	$_SESSION['SET_TYPE'] = 'success';
	$_SESSION['SET_FLASH'] = 'Sie sind erfolgreich angemeldet.';
	header("location:".$destinationPath);
}

/*
function validation_check()

* check whether the page is accessable without login or not.
* @param string checkingVariable: Consists of the variable value based on whcih validation needs to be done.
* @return: If true redirects user to the destination path, otherwise return true.
*/

function validation_check($checkingVariable, $destinationPath)
{
	if($checkingVariable =='')
	{
		header("location:".$destinationPath);
	}
}

/*
function Send_HTML_Mail()

* send HTML or text email without SMTP validation.
* @param string mail_To: Email address which which email needs to be sent.
* @param string mail_From: Email address from which email needs to be sent.
* @param string mail_CC: Enter email address that you wish to send a cc copy (optional).
* @param string mail_subject: Email subject line.
* @param string mail_Body: Email content either in HTML format or simple text format.
* @returns true or false.
*/

function Send_HTML_Mail($mail_To, $mail_From, $mail_CC, $mail_subject, $mail_Body)
{
        /*echo $mail_To;
	echo $mail_From;
	echo $mail_subject;
	echo $mail_Body;*/
	
	$mail_Headers  = "MIME-Version: 1.0\r\n";
	$mail_Headers .= "Content-type: text/html; charset=iso-8859-1\r\n";		
	$mail_Headers .= "From: ${mail_From}\r\n";

	if($mail_CC != '')
	{
		$mail_Headers .= "CC: ${mail_CC}";
	}

	if(mail($mail_To, $mail_subject, $mail_Body, $mail_Headers))
	{		//echo "ok";	
		return true;
	}
	else
	{        //echo "not";	
		return false;
	}
    /*include_once ("class.phpmailer.php");
    include_once ("class.smtp.php");
    $mail = new PHPMailer();

    //Your SMTP servers details
    $mail->IsSMTP();               // set mailer to use SMTP
    $mail->Host = "localhost"; // specify main and backup server or localhost
    $mail->SMTPAuth = true;     // turn on SMTP authentication
    $mail->Username = "test_neo@techinnvo.com";  // SMTP username
    $mail->Password = "ucuZh=T^LEl3";
    //It should be same as that of the SMTP user
    $mail->SMTPDebug = 1;
    $mail->Port = 587;  
    $mail->From = $mail_From;    //Default From email same as smtp user
    $mail->FromName = "Web Base Software";
    
    $mail->AddAddress($mail_To, ""); 

    //$mail->WordWrap = 50; // set word wrap to 50 characters
    $mail->IsHTML(true);  // set email format to HTML
    $mail->Subject = $mail_subject;
    $mail->Body = $mail_Body;


    if($mail->Send())
    {
        //echo "ok1";
        //echo $mail->ErrorInfo;
        //exit;
        //header('location:index.html');
    }
    else
    {
        //echo "not";
    	//echo $mail->ErrorInfo;
        //header('location:index.html');
    }*/
}

/*
function Send_SMTP_Mail()

* send HTML or text email with SMTP validation.
* @param string mail_To: Email address which which email needs to be sent.
* @param string mail_From: Email address from which email needs to be sent.
* @param string mail_CC: Enter email address that you wish to send a cc copy (optional).
* @param string mail_subject: Email subject line.
* @param string mail_Body: Email content either in HTML format or simple text format.
* @returns true or false.
*/

function Send_SMTP_Mail($mail_To, $mail_From, $mail_CC, $mail_subject, $mail_Body)
{
	$mail_Headers  = "MIME-Version: 1.0\r\n";
	$mail_Headers .= "Content-type: text/html; charset=iso-8859-1\r\n";		
	$mail_Headers .= "From: ${mail_From}\r\n";

	if($mail_CC != '')
	{
		$mail_Headers .= "Cc: ${mail_CC}\r\n";
	}

	/*echo $mail_To;
	echo $mail_From;
	echo $mail_subject;
	echo $mail_Body;*/

	if(mail($mail_To, $mail_subject, $mail_Body, $mail_Headers))
	{			
		return true;
	}
	else
	{        	
		return false;
	}
}

/*
function Send_SMTP_Mail_With_Attachment()

* send HTML or text email with SMTP validation and provided attachments.
* @param string mail_To: Email address which which email needs to be sent.
* @param string mail_From: Email address from which email needs to be sent.
* @param string mail_CC: Enter email address that you wish to send a cc copy (optional).
* @param string mail_subject: Email subject line.
* @param string mail_Body: Email content either in HTML format or simple text format.
* @param string attachmentVal: Consists of path of the attached document.
* @returns true or false.
*/

function Send_SMTP_Mail_With_Attachment($mail_To, $mail_From, $mail_CC, $mail_subject, $mail_Body, $attachmentVal)
{
	
}

/*
function create_password()

* create random number with maximum length of 10.
* @param length: Can be any interger value starting from 1 to 10.
* @returns randon generated string with specified length.
*/

function create_password($length=10)
{
   $phrase = "";
   $chars = array (
				  "1","2","3","4","5","6","7","8","9","0",
				  "a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J",
				  "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T",
				  "u","U","v","V","w","W","x","X","y","Y","z","Z"
				  );

   $count = count ($chars) - 1;
   srand ((double) microtime() * 1234567);
   for ($i = 0; $i < $length; $i++)
	  $phrase .= $chars[rand (0, $count)];
   return $phrase;
}

/*
function get_visitor_location_details()

* find visitor details by accessing the IP address. 
* @param id_address: IP address of the user
* @returns details such as country, city, state, and zip code if available.
*/

function get_visitor_location_details($id_address)
{
	$url = "http://api.ipinfodb.com/v3/ip-city/?key=".API_KEY_IPINFODB."&ip=".$id_address."&format=json";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 0 );
	$response = curl_exec( $ch );
	curl_close($ch);
	$ip_details = json_decode($response);

	if($ip_details->statusCode == 'OK') 
	{
		return $ip_details;
	}else
	{
		return false;
	}
}

/*
function generateFormToken()

* generate a token from an unique value
* @param form: name of the form.
* @returns random generated security token
*/

function generateFormToken($form) 
{
	// generate a token from an unique value
	$token = md5(uniqid(microtime(), true));  
	// Write the generated token to the session variable to check it against the hidden field when the form is sent
	$_SESSION[$form.'_token'] = $token; 
	return $token;
}

/*
function verifyFormToken()

* check with the submitted token through form submission
* @param form: name of the form.
* @returns true if match found or false in not found.
*/

function verifyFormToken($form) 
{
    // check if a session is started and a token is transmitted, if not return an error
	if(!isset($_SESSION[$form.'_token'])) 
	{ 
		return false;
    }
	
	// check if the form is sent with token in it
	if(!isset($_POST['token'])) 
	{
		return false;
    }
	
	// compare the tokens against each other if they are still the same
	if ($_SESSION[$form.'_token'] !== $_POST['token']) 
	{
		return false;
    }
	return true;
}

/*
function stripcleantohtml()

* Restores the added slashes (ie.: " I\'m John " for security in output, and escapes them in htmlentities(ie.:  &quot; etc.). Also strips any <html> tags it may encouter
* @param form: form field value.
* @Use: Anything that shouldn't contain html (pretty much everything that is not a textarea)
* @returns formatted form filed value.
*/

function stripcleantohtml($s)
{
	return htmlentities(trim(strip_tags(stripslashes($s))), ENT_NOQUOTES, "UTF-8");
}

/*
function cleantohtml()

* Restores the added slashes (ie.: " I\'m John " for security in output, and escapes them in htmlentities(ie.:  &quot; etc.)
  It preserves any <html> tags in that they are encoded aswell (like &lt;html&gt;)
  As an extra security, if people would try to inject tags that would become tags after stripping away bad characters
  we do still strip tags but only after htmlentities, so any genuine code examples will stay
* @param form: form field value.
* @Use: For input fields that may contain html, like a textarea
* @returns formatted form filed value.
*/

function cleantohtml($s)
{
	return strip_tags(htmlentities(trim(stripslashes($s))), ENT_NOQUOTES);
}
?>