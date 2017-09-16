<?php
class ZoteroLocalDatabase {
    function index() {
        echo \Template::instance()->render('header.html');
        echo "ok";
        LayoutEngine::footer();
    }
}