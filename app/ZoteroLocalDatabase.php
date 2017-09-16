<?php
class ZoteroLocalDatabase {
    function index($f3) {
        $f3->set('title', 'Zotero Local Database');
        echo \Template::instance()->render('header.html');
        
        $rows = $f3->db->exec("SELECT
itemTitle.value as field_title, 
group_concat(itemCreators.lastName, ', ') as field_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS field_date
FROM
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemTitle,
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemDate,
(items
left join itemCreators using(itemID) 
left join creators using(creatorID)) as itemCreators
WHERE itemTitle.itemID = 1
and itemTitle.itemID = itemDate.itemID
and itemTitle.itemID = itemCreators.itemID
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
and itemCreators.creatorTypeID = 1");
        foreach ($rows as $row) {
            echo $row['itemID']."<br />";
        }
        echo "ok";
        
        
        echo \Template::instance()->render('footer.html');
    }
}