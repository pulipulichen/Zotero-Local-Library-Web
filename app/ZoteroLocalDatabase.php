<?php
class ZoteroLocalDatabase {
    function index($f3) {
        header('Location: ' . $f3->get("BASEURL") . '/item_collection');
    }
    
    function item_collection($f3) {
        $f3->set('page_title', 'Zotero Local Database');
        echo \Template::instance()->render('layout/header.html');
        $page = $f3->get('PARAMS.page');
        $page_limit = $f3->get('PAGE_LIMIT');
        if (is_null($page)) {
            $page = 1;
        }
        echo "page: " . $page;
        
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
        
        $this->pagination($f3, $page, 998);
        
        echo \Template::instance()->render('layout/footer.html');
    }
    
    function pagination($f3, $current_page, $items_count) {
        
        $page_limit = $f3->get('PAGE_LIMIT');
        $page_near_limit = $f3->get('PAGE_NEAR_LIMIT');
        
        // 先計算最多的頁數
        $page_number_last = ceil($items_count / $page_limit);
        
        // --------------------
        
        $pagination_first = array();
        // 大概要4以上才要
        if ($current_page > 1) {
            $pagination_first[] = 1;
        }
        $f3->set('pagination_first', $pagination_first);
        
        $pagination_before_skip = array();
        if ($current_page - $page_near_limit > (1+1)) {
            $pagination_before_skip[] = "...";
        }
        $f3->set('pagination_before_skip', $pagination_before_skip);
        
        $pagination_before = array();
        for ($i = $page_near_limit; $i > 0; $i--) {
            if ($current_page - $i > 1) {
                $pagination_before[] = $current_page - $i;
            }
        }
        $f3->set('pagination_before', $pagination_before);
        
        $pagination_current = array($current_page);
        $f3->set('pagination_current', $pagination_current);
        
        $pagination_after = array();
        for ($i = 1; $i < $page_near_limit+1; $i++) {
            if ($current_page + $i < $page_number_last) {
                $pagination_after[] = $current_page + $i;
            }
        }
        $f3->set('pagination_after', $pagination_after);
        
        $pagination_after_skip = array();
        if ($current_page + $page_near_limit < ($page_number_last) - 1) {
            $pagination_after_skip[] = "...";
        }
        $f3->set('pagination_after_skip', $pagination_after_skip);
        
        $pagination_last = array();
        if ($current_page < $page_number_last) {
            $pagination_last[] = $page_number_last;
        }
        $f3->set('pagination_last', $pagination_last);
        
        echo \Template::instance()->render('components/pagination.html');
    }
}