<?php
class ZoteroLocalDatabase {
    function index($f3) {
        $f3->set('title', 'Zotero Local Database');
        echo \Template::instance()->render('header.html');
        echo "ok";
        echo \Template::instance()->render('footer.html');
    }
}