<?php include_once('init.php');?>
<?php
    
    $mail_Body = "Dear NEO2,<br/><br/>Your user account password is been updated successfully. Here is your updated account access details.<br/>Regards,<br/>Administrator, <br>".$restaurant_name.".";
    Send_HTML_Mail('neocoderz04@gmail.com', 'neocoderz02@gmail.com', '', 'Your Updated User Access Details', $mail_Body);

    
        
?>