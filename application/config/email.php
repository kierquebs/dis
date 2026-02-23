<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol'] = getenv('MAIL_DRIVER');
$config['smtp_host'] = 'smtp.sendgrid.net'; //getenv('MAIL_HOST');
$config['smtp_port'] = getenv('MAIL_PORT'); 
$config['smtp_user'] = 'apikey';
$config['smtp_pass'] = 'REMOVED_SECRET';


$config['smtp_timeout'] = '7';
$config['mailtype'] =  'html'; // or html
$config['wordwrap'] = TRUE;
$config['charset'] = "utf-8"; //"iso-8859-1"
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";
$config['validation'] = TRUE; // bool whether to validate email or not    

