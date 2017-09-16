<?php
class ZoteroLocalDatabase {
    function index($f3) {
        $f3->set('page_title', 'Zotero Local Database');
        echo \Template::instance()->render('layout/header.html');
        
        $rows = $f3->db->exec("SELECT
itemTitle.itemID as item_id,
itemTitle.value as item_title, 
group_concat(itemCreators.lastName, ', ') as item_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS item_date,
itemTitle.dateModified as item_modified_date
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
WHERE itemTitle.itemID = 689
and itemTitle.itemID = itemDate.itemID
and itemTitle.itemID = itemCreators.itemID
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
and itemCreators.creatorTypeID = 1
ORDER BY
itemTitle.dateModified DESC");
        /*
        $items_collection = array();
        foreach ($rows as $row) {
            $items_collection[] = $row;
            
            $f3->set('item_title', $row['item_title']);
            $f3->set('item_creators', $row['item_creators']);
            $f3->set('item_date', $row['item_date']);
            $f3->set('item_modified_date', $row['item_modified_date']);
            echo \Template::instance()->render('components/item.html');
        }
        */
        $f3->set('item_collection', $rows);
        echo \Template::instance()->render('components/item_collection.html');
        echo "ok";
        
        echo \Template::instance()->render('layout/footer.html');
    }
}