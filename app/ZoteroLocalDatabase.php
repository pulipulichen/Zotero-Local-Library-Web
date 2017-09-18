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
    
    // ----------------------------
    
    function item($f3) {
        $item_id = intval($f3->get("PARAMS.item_id"));
        //echo $item_id;
        $item_collection = $this->get_item_collection($f3, 0, $item_id);
        $f3->set('item_collection', $item_collection);
        
        // 查詢item的attachments.sql
        $sql = "select 
replace(itemAttachments.path, 'storage:', '') as attachment_title, 
items.key as attachment_key, 
items.dateModified as attachment_date_modified
from itemAttachments
left join items using (itemID)
where
itemAttachments.parentItemID = " . $item_id . " 
and itemAttachments.contentType = 'application/pdf'
order by attachment_title + 0";
        $rows = $f3->db->exec($sql);
        $f3->set('attachment_collection', $rows);
        
        $f3->set('page_title', $item_collection[0]['item_title'] . ' - Zotero Local Database');
        $f3->set('page_header', $item_collection[0]['item_title']);
        
        if (isset($item_collection[0]['item_link'])) {
            $f3->set('folder_link', $item_collection[0]['item_link']);
        }
        
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
    
    function get_item_collection_sql($f3) {
        $where_search = "";
        if (isset($_GET["q"])) {
            $q = $_GET["q"];
            $where_search = "AND (itemTitle.value LIKE '%" . $q . "%' OR itemCreators.item_creators LIKE '%" . $q . "%' )";
        }
        
        $sql = "SELECT
itemTitle.itemID AS item_id, 
itemTitle.value AS item_title, 
itemCreators.item_creators AS item_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS item_date,
itemTitle.dateModified AS item_modified_date,
itemCover.item_cover_path AS item_cover_path,
ifnull(item_attachment_count, 0) AS item_attachment_count,
itemLink.value as item_link
FROM

(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemTitle

join 
(select 
    count(itemID) as item_attachment_count, 
    itemAttachments.parentItemID
    from itemAttachments
    where contentType = 'application/pdf'
    group by itemAttachments.parentItemID) as itemAttachmentCount on itemCreators.itemID = itemAttachmentCount.parentItemID,

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

left join 
(select 
    replace(itemAttachments.path, 'storage:', '" . $f3->get("ZOTERO_PATH") . "' || '/storage/' || items.key || '/') as item_cover_path, 
    items.key, 
    items.dateModified,
    itemAttachments.parentItemID
    from itemAttachments
    left join items using (itemID)
    where
    (contentType = 'image/png' or contentType = 'image/gif' or contentType = 'image/jpeg')
    group by itemAttachments.parentItemID
    order by dateModified DESC) as itemCover on itemCreators.itemID = itemCover.parentItemID

left join
(select 
    itemAttachments.parentItemID,
    itemLink.value
    from 
    itemAttachments 
        join (
            select items.itemID, value, items.dateModified from items join itemData using (itemID) 
                join itemDataValues on itemDataValues.valueID = itemData.valueID and itemData.fieldID = 110 
            ) as itemTitle using (itemID)
        join (
            select items.itemID, value from items join itemData using (itemID) 
                join itemDataValues on itemDataValues.valueID = itemData.valueID and itemData.fieldID = 1 
            ) as itemLink using (itemID)
    where 
    linkMode = 3
    group by parentItemID
    order by dateModified DESC) as itemLink on itemLink.parentItemID = itemCreators.itemID

WHERE
itemTitle.itemID = itemDate.itemID
and itemTitle.itemID = itemCreators.itemID
and itemTitle.itemTypeID = 2
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
" . $where_search . "
ORDER BY
itemTitle.dateModified DESC";
        
        return $sql;
    }
    
    function get_item_collection($f3, $offset = 0, $item_id = NULL) {
        $page_limit = $f3->get('PAGE_LIMIT');
        
        $where_item_id = "";
        if (is_null($item_id) === FALSE) {
            $where_item_id = 'WHERE item_id = ' . $item_id;
            $offset = 0;
        }
        
        $item_collection_sql = $this->get_item_collection_sql($f3);
        $sql = "SELECT * FROM (" . $item_collection_sql . ") as a
" . $where_item_id . "
LIMIT " . $offset . ", " . $page_limit;
        
        echo "<!-- \n\n" . $sql . "\n\n -->";
        
        $rows = $f3->db->exec($sql);
        
        for ($i = 0; $i < count($rows); $i++) {
            // 取代搜尋詞彙
            if (isset($_GET["q"])) {
                $q = $_GET["q"];
                $rows[$i]["item_title"] = str_replace($q, '<b>' . $q . '</b>', $rows[$i]["item_title"]);
                $rows[$i]["item_creators"] = str_replace($q, '<b>' . $q . '</b>', $rows[$i]["item_creators"]);
            }
            
            if (is_null($rows[$i]["item_cover_path"]) === FALSE) {
                // https://fatfreeframework.com/3.6/image
                $cover_path = mb_convert_encoding($rows[$i]["item_cover_path"], 'big5');
                $width = 50;
                $type = 'gif';
                
                list($width_orig, $height_orig) = getimagesize($cover_path);
                $aspectRatio = $height_orig / $width_orig;
                $height = intval($aspectRatio * $width);
                
                $image_p = imagecreatetruecolor($width, $height);
                $image = imagecreatefromstring(file_get_contents($cover_path));
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig,$height_orig);
                
                ob_start(); // Let's start output buffering.
                    imagegif($image_p); //This will normally output the image, but because of ob_start(), it won't.
                    $contents = ob_get_contents(); //Instead, output above is saved to $contents
                ob_end_clean(); //End the output buffer.
                
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($contents);
                
                $rows[$i]["item_cover_base64"] = $base64;
            }
        }
        
        return $rows;
    }
    
    function get_items_count($f3) {
        
        $item_collection_sql = $this->get_item_collection_sql($f3);
        $sql = "select count(*) as items_count
from (" . $item_collection_sql . ") as a";
        
        //echo '<!-- \n\n' . $sql . '\n\n -->';
        
        $rows = $f3->db->exec($sql);
        
        return $rows[0]["items_count"];
    }
}