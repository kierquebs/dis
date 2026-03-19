<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol'] = getenv('MAIL_DRIVER') ? getenv('MAIL_DRIVER') : 'smtp';
$config['smtp_host'] = getenv('MAIL_HOST') ? getenv('MAIL_HOST') : 'avm414.sgvps.net';
$config['smtp_port'] = getenv('MAIL_PORT') ? getenv('MAIL_PORT') : '587';
$config['smtp_user'] = getenv('MAIL_USERNAME') ? getenv('MAIL_USERNAME') : 'apikey';
$config['smtp_pass'] = getenv('MAIL_PASSWORD') ? getenv('MAIL_PASSWORD') : '';

/*
$config['smtp_crypto'] = getenv('MAIL_ENCRYPTION'); //tls or ssl
$config['sendmail'] = "/usr/sbin/sendmail -bs";
*/
/*
$config['protocol']    = 'smtp';
$config['smtp_host']    = 'smtp.office365.com';
$config['smtp_port']    = '587';
$config['smtp_user']    = 'it.svc.ph@sodexo.com';
$config['smtp_pass']    = 'Tru3l3g3nd0123';
*/
$config['smtp_timeout'] = '7';
$config['mailtype'] =  'html'; // or html
$config['wordwrap'] = TRUE;
$config['charset'] = "utf-8"; //"iso-8859-1"
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";
$config['validation'] = TRUE; // bool whether to validate email or not    

