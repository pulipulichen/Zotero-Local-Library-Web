<?php

// Kickstart the framework
$f3=require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9) {
	trigger_error('PCRE version is out of date');
}

// Load configuration
if (file_exists('config.ini')) {
    $f3->config('config.ini');
}
else {
    $f3->config('config_sample.ini');
}

// Database
global $db;
$zotero_sqlite = $f3->get('ZOTERO_PATH') . '\zotero.sqlite';
$db = new \DB\SQL('sqlite:' . $zotero_sqlite);
$f3->db = $db;

include 'routing.php';

$f3->run();
