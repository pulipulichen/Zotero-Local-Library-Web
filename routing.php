<?php
include 'app/ZoteroLocalDatabase.php';
$cache_timeout = $f3->get("CACHE_TIMEOUT");
$f3->route('GET /', 'ZoteroLocalDatabase->index', $cache_timeout);
$f3->route('GET /close_zotero', 'ZoteroLocalDatabase->close_zotero', $cache_timeout);
$f3->route('GET /start_zotero', 'ZoteroLocalDatabase->start_zotero', $cache_timeout);
$f3->route('GET /locked_zotero', 'ZoteroLocalDatabase->locked_zotero', $cache_timeout);
$f3->route('GET /item_collection', 'ZoteroLocalDatabase->item_collection', $cache_timeout);
$f3->route('GET /item_collection/@tag', 'ZoteroLocalDatabase->item_collection', $cache_timeout);
$f3->route('GET /item_collection/@tag/@page', 'ZoteroLocalDatabase->item_collection', $cache_timeout);
$f3->route('GET /item/@item_id', 'ZoteroLocalDatabase->item', $cache_timeout);