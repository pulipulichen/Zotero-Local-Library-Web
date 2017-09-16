<?php
class ZoteroLocalDatabase {
    function index($f3) {
        header('Location: ' . $f3->get("BASEURL") . '/item_collection');
    }
    
    function item_collection($f3) {
        
        
        $page = $f3->get('PARAMS.page');
        $page_limit = $f3->get('PAGE_LIMIT');
        $items_count = $this->get_items_count($f3);
        if (is_null($page) || $page > ceil($items_count / $page_limit)) {
            $page = 1;
        }
        $offset = ($page-1) * $page_limit;
        
        $f3->set('item_collection', $this->get_item_collection($f3, $offset));
        
        $f3->set('page_title', 'Zotero Local Database (page: ' . $page . ')');
        
        // -----------------------
        
        echo \Template::instance()->render('layout/header.html');
        
        $this->pagination($f3, $page, $items_count);
        
        echo \Template::instance()->render('components/item_collection.html');
        
        $this->pagination($f3, $page, $items_count);
        
        echo \Template::instance()->render('layout/footer.html');
    }
    
    function item($f3) {
        $item_id = intval($f3->get("PARAMS.item_id"));
        //echo $item_id;
        $item_collection = $this->get_item_collection($f3, 0, $item_id);
        $f3->set('item_collection', $item_collection);
        
        $f3->set('page_title', $item_collection[0]['item_title'] . ' - Zotero Local Database');
        $f3->set('page_header', $item_collection[0]['item_title']);
        
        // ----------------------
        
        echo \Template::instance()->render('layout/header.html');
        echo \Template::instance()->render('components/item.html');
        echo \Template::instance()->render('layout/footer.html');
    }

    // ----------------------------------------
    
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
    
    function get_item_collection($f3, $offset = 0, $item_id = NULL) {
        $page_limit = $f3->get('PAGE_LIMIT');
        
        $where_item_id = "";
        if (is_null($item_id) === FALSE) {
            $where_item_id = 'AND item_id = ' . $item_id;
            $offset = 0;
        }
        
        $sql = "SELECT
itemTitle.itemID as item_id,
itemTitle.value AS item_title, 
itemCreators.item_creators AS item_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS item_date,
itemTitle.dateModified AS item_modified_date
FROM
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemTitle,
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemDate,
(select 
    items.itemID,
    group_concat(creators.lastName, ', ') as item_creators
    FROM 
    items
    left join itemCreators using(itemID) 
    left join creators using(creatorID)
    where creatorTypeID = 1
    group by itemID
    order by orderIndex) as itemCreators

WHERE
itemTitle.itemID = itemDate.itemID
and itemTitle.itemID = itemCreators.itemID
and itemTitle.itemTypeID = 2
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
" . $where_item_id . "
ORDER BY
itemTitle.dateModified DESC
LIMIT " . $offset . ", " . $page_limit;
        
        //echo '<textarea>' . $sql . '</textarea>';
        
        $rows = $f3->db->exec($sql);
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
        return $rows;
    }
    
    function get_items_count($f3) {
        $rows = $f3->db->exec("select count(attachments_count) as items_count
from (
select count(items.itemID) as attachments_count
from items
join itemAttachments 
on items.itemID = itemAttachments.parentItemID 
and itemAttachments.contentType = 'application/pdf'
and items.itemTypeID = 2
group by items.itemID) as a");
        return $rows[0]["items_count"];
    }
}