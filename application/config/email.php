<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol'] = (getenv('MAIL_DRIVER')  ?: 'smtp');
$config['smtp_host'] = (getenv('MAIL_HOST')    ?: 'smtp.sendgrid.net');
$config['smtp_port'] = (getenv('MAIL_PORT')    ?: '587');
$config['smtp_user'] = (getenv('MAIL_USERNAME') ?: 'apikey');
$config['smtp_pass'] = (getenv('MAIL_PASSWORD') ?: getenv('SENDGRID_API_KEY'));


$config['smtp_timeout'] = '7';
$config['mailtype'] =  'html'; // or html
$config['wordwrap'] = TRUE;
$config['charset'] = "utf-8"; //"iso-8859-1"
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";
$config['validation'] = TRUE; // bool whether to validate email or not

