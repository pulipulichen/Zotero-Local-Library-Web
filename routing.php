<?php
include 'app/ZoteroLocalDatabase.php';
$f3->route('GET /', 'ZoteroLocalDatabase->index');
$f3->route('GET /close_zotero', 'ZoteroLocalDatabase->close_zotero');
$f3->route('GET /start_zotero', 'ZoteroLocalDatabase->start_zotero');
$f3->route('GET /locked_zotero', 'ZoteroLocalDatabase->locked_zotero');
$f3->route('GET /item_collection', 'ZoteroLocalDatabase->item_collection');
$f3->route('GET /item_collection/@tag', 'ZoteroLocalDatabase->item_collection');
$f3->route('GET /item_collection/@tag/@page', 'ZoteroLocalDatabase->item_collection');
$f3->route('GET /item/@item_id', 'ZoteroLocalDatabase->item');