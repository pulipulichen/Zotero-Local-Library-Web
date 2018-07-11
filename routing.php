<?php
include 'app/ZoteroLocalDatabase.php';
$f3->route('GET /', 'ZoteroLocalDatabase->index');
$f3->route('GET /item_collection', 'ZoteroLocalDatabase->item_collection');
$f3->route('GET /item_collection/@tag', 'ZoteroLocalDatabase->item_collection');
$f3->route('GET /item_collection/@tag/@page', 'ZoteroLocalDatabase->item_collection');
$f3->route('GET /item/@item_id', 'ZoteroLocalDatabase->item');