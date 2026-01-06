<?php
/*
=========================================================================================================================
COPYRIGHT: IDIGITALMIND ORGANIZATION
PRODUCT NAME: DOCUMENT MANAGEMENT SYSTEM
PAGE NAME: DBCONNECTION.PHP
PAGE FUNCTIONALITY: THIS SCRIPT IS USED TO CONNECT MYSQL DATABASE IN NON PERSISTANCE ORDER. $db IS A GLOBAL VARIABLE. SO ONCE CONNECTION IS ESTABLISHED YOU CAN GET THE DATABASE INSTANCE AND RUN ANY QUERY WITHIN THE PAGE WITHIN WHICH THIS AND OTHER CONFIGURATION PAGES ARE INCLUDED.
=========================================================================================================================
*/

function Db_Connect()
{
	global $db;
	if(DATABASE_NAME=='')
	{
		return '';
	}
	else
	{
		$db = new PDO("mysql:dbname=".DATABASE_NAME."; host=".DATABASE_SERVER."", DATABASE_USERNAME, DATABASE_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		//$db = new PDO("mysql:dbname=".DATABASE_NAME."; host=".DATABASE_SERVER."", DATABASE_USERNAME, DATABASE_PASSWORD);
		return $db;
	}
}
?>