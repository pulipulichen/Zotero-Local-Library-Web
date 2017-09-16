<?php
include 'app/LayoutEngine/LayoutEngine.php';

include 'app/ZoteroLocalDatabase/ZoteroLocalDatabase.php';
$f3->route('GET /', 'ZoteroLocalDatabase->index');