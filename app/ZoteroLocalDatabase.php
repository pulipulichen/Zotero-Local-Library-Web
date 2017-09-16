<?php
class ZoteroLocalDatabase {
    function index($f3) {
        $f3->set('title', 'Zotero Local Database');
        echo \Template::instance()->render('header.html');
        
        $rows = $f3->db->exec('select items.itemID, fields.fieldID, fieldName, value 
from items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)
where itemTypeID = 2
and (fields.fieldID = 110)
and items.itemID = 689
limit 100');
        foreach ($rows as $row) {
            echo $row['itemID']."<br />";
        }
        echo "ok";
        
        
        echo \Template::instance()->render('footer.html');
    }
}