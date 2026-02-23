<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function sr_conf($name_db){
	$confdb = array(
		'dsn'	=> '',
		'hostname' => getenv('SRDB_HOST'),
		'username' => getenv('SRDB_USERNAME'),
		'password' => getenv('SRDB_PASSWORD'),
		'database' => $name_db,
		'dbdriver' => 'mysqli',
		'dbprefix' => '',
		'pconnect' => TRUE,
		'db_debug' => getenv('APP_DEBUG'), //(ENVIRONMENT !== 'production'),
		'cache_on' => FALSE,
		'cachedir' => '',
		'char_set' => 'utf8',
		'dbcollat' => 'utf8_general_ci',
		'swap_pre' => '',
		'encrypt' => FALSE,
		'compress' => FALSE,
		'stricton' => FALSE,
		'failover' => array(),
		'save_queries' => FALSE
	);
    return $confdb;
}
