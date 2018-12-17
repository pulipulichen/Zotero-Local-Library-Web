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

$cache = \Cache::instance();
$cache->load("folder:tmp/cache/");

// Database

//header( "refresh:3;url=http://blog.pulipuli.info" );

global $db;
/*
$sqlite_path = $f3->get('ZOTERO_DATA_PATH') . '\zotero.sqlite';
$sqlite_journal_path = $f3->get('ZOTERO_DATA_PATH') . '\zotero.sqlite-journal';
//if (file_exists($sqlite_journal_path) === FALSE) {
    $zotero_sqlite = $f3->get('ZOTERO_DATA_PATH') . '\zotero.sqlite';
    $db = new \DB\SQL('sqlite:' . $zotero_sqlite);
    $f3->db = $db;
//}
 */

//set_time_limit(3);

include 'routing.php';

$f3->run();
