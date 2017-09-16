<?php
class LayoutEngine {
    public static function header($title = "Zotero Local Database") {
        //$f3->set('title', $title);
        echo \Template::instance()->render('header.html');
    }
    
    public static function footer() {
        echo \Template::instance()->render('app/LayoutEngine/footer.html');
    }
}