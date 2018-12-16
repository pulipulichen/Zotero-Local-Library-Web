<?php

class ZoteroLocalDatabase {

    function index($f3) {
        //$this->check_sqlite_lock($f3);
        header('Location: ' . $f3->get("BASEURL") . '/item_collection');
    }

    private $q = NULL;
    private $tag = NULL;

    function check_sqlite_lock($f3) {
        if (is_object($f3->db) === FALSE) {
            $url = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            //$_SESSION["last_url"] = $url;
            if ($this->endsWith($url, "close_zotero") === FALSE) {
                setcookie("last_url", $url);
            }
            $this->locked_zotero($f3);
        }
    }
    
    function is_sqlite_locked($f3) {
        return (is_object($f3->db) === FALSE);
    }

    function locked_zotero($f3) {
        $f3->set('page_title', "Error");
        echo \Template::instance()->render('layout/header.html');
        echo \Template::instance()->render('sqlite_locked_error_menu.html');
        echo \Template::instance()->render('sqlite_locked_error.html');
        echo \Template::instance()->render('layout/footer.html');
        exit;
    }

    function close_zotero($f3) {
        // D:\xampp\htdocs\public\Zotero-Local-Library-Web\app\ZoteroLocalDatabase.php
        $script = __DIR__;
        $autoit_script = substr($script, 0, strrpos($script, "app")) . "autoit\\close-zotero.exe";
        //shell_exec('taskkill /F /IM "zotero.exe"');
        shell_exec($autoit_script);
        sleep(3);
        //echo $_COOKIE["last_url"];
        //$last_url = $_COOKIE["last_url"];
        //unset($_COOKIE["last_url"]);
        $last_url = $_SERVER['HTTP_REFERER'];
        
        if (isset($last_url) && $this->endsWith($last_url, "close_zotero") === FALSE) {
            header('Location: ' . $last_url);
            
        } else {
            header('Location: ' . $f3->get("BASEURL"));
        }
    }

    function start_zotero($f3) {
        if (is_object($f3->db) === FALSE) {
            $f3->db = null;
        }
        $url = $_SERVER['HTTP_REFERER'];
        if ($this->endsWith($url, "close_zotero") === FALSE) {
            setcookie("last_url", $url);
        }

        $zotero_path = $f3->get('ZOTERO_PATH');
        $autoit_script = substr(__DIR__, 0, strrpos(__DIR__, "app")) . "autoit\\start-zotero.exe";
        //echo $autoit_script . '"' . $zotero_path . '"';
        shell_exec($autoit_script . ' "' . $zotero_path . '"');
        //pclose(popen('start /B cmd /C "' . $zotero_path . ' >NUL 2>NUL"', 'r'));
        sleep(10);
        //$this->locked_zotero($f3);
        
        header("Location: " . $url);
    }

    function item_collection($f3) {
        //$this->check_sqlite_lock($f3);

        if (isset($_GET["q"])) {
            $this->q = $_GET["q"];
        }

        /*
          if (isset($_GET["tag"])) {
          $this->tag = $_GET["tag"];
          }
          else {
          $this->tag = "ToReadZH";
          }
         */

        $page = NULL;
        if (is_numeric($f3->get('PARAMS.tag'))) {
            $page = $f3->get('PARAMS.tag');
        } else if ($f3->get('PARAMS.tag') !== "") {
            $this->tag = $f3->get('PARAMS.tag');
        }
        if (is_numeric($f3->get('PARAMS.page'))) {
            $page = $f3->get('PARAMS.page');
        }

        if (is_null($this->tag)) {
            $config_tags = $f3->get('TAGS');
            if (count($config_tags) > 0 && strtolower($config_tags[0]) !== "null") {
                $this->tag = trim($config_tags[0]);
            }
        }

        $page_limit = $f3->get('PAGE_LIMIT');
        $items_count = $this->get_items_count($f3);
        if (is_null($page) || $page > ceil($items_count / $page_limit)) {
            $page = 1;
        }
        $offset = ($page - 1) * $page_limit;

        $f3->set('item_collection', $this->get_item_collection($f3, $offset));

        $page_title = "All Items";
        if (is_string($this->tag)) {
            $page_title = $this->tag;
        }
        if (strtolower($page_title) === "null") {
            $page_title = "All Items";
        }
        $f3->set('page_title', $page_title);
        $f3->set('page_number', $page);

        $tags_list = $f3->get('TAGS');
        foreach ($tags_list as $key => $value) {
            if (is_null($value) || strtolower($value) === "null") {
                $tags_list[$key] = "null";
            }
        }
        $f3->set('page_tags', $tags_list);

        // -----------------------

        if ($this->is_sqlite_locked($f3)) {
            $f3->set('is_sqlite_locked', "true");
        }
        else {
            $f3->set('is_sqlite_locked', "false");
        }
        
        echo \Template::instance()->render('layout/header.html');
        echo \Template::instance()->render('layout/menu.html');

        $this->pagination($f3, $page, $items_count);

        echo \Template::instance()->render('components/item_collection.html');

        $this->pagination($f3, $page, $items_count);

        echo \Template::instance()->render('layout/footer.html');
    }

    // ----------------------------
    function t($f3) {
      echo 't';
    }
    
    function load_db($f3) {
      

      //$sqlite_path = $f3->get('ZOTERO_DATA_PATH') . '\zotero.sqlite';
      //$sqlite_journal_path = $f3->get('ZOTERO_DATA_PATH') . '\zotero.sqlite-journal';
      //if (file_exists($sqlite_journal_path) === FALSE) {
      $zotero_sqlite = $f3->get('ZOTERO_DATA_PATH') . '\zotero.sqlite';
      
      $dbhandle = sqlite_open($zotero_sqlite);
      sqlite_busy_timeout($dbhandle, 3000); // set timeout to 10 seconds
          
          
          $db = new \DB\SQL('sqlite:' . $zotero_sqlite);
          $f3->db = $db;
      //}
    }
    
    function get_item($f3, $item_id) {
        $cache = \Cache::instance();
        $key = "item_" . md5($_SERVER['REQUEST_URI']);
        $this->load_db($f3);
        if ($this->is_sqlite_locked($f3)) {
            if ($cache->exists($key)) {
                return $cache->get($key);
            }
            else {
                $this->locked_zotero($f3);
                return;
            }
        }
        
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
order by attachment_title";
        $rows = $f3->db->exec($sql);
        
        $cache->set($key, $rows);
        
        return $rows;
    }
    
    function item($f3) {
        //$this->check_sqlite_lock($f3);

        $item_id = intval($f3->get("PARAMS.item_id"));
        //echo $item_id;

        $item_collection = $this->get_item_collection($f3, 0, $item_id);

        $f3->set('item_collection', $item_collection);
        $rows = $this->get_item($f3, $item_id);
        
        $f3->set('attachment_collection', $rows);

        $f3->set('page_title', $item_collection[0]['item_title'] . ' - Zotero Local Database');
        $f3->set('page_header', $item_collection[0]['item_title']);

        if (isset($item_collection[0]['item_link'])) {
            $f3->set('folder_link', $item_collection[0]['item_link']);
        }

        // ----------------------

        if ($this->is_sqlite_locked($f3)) {
            $f3->set('is_sqlite_locked', "true");
        }
        else {
            $f3->set('is_sqlite_locked', "false");
        }
        
        echo \Template::instance()->render('layout/header.html');
        echo \Template::instance()->render('layout/menu.html');
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
        if ($current_page - $page_near_limit > (1 + 1)) {
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
        for ($i = 1; $i < $page_near_limit + 1; $i++) {
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

        if (is_null($this->tag) === FALSE) {
            $f3->set("tag", $this->tag . "/");
        } else {
            $f3->set("tag", "");
        }

        echo \Template::instance()->render('components/pagination.html');
    }

    function get_item_collection_sql($f3) {
        $where_search = "";
        if (is_null($this->q) === FALSE) {
            $where_search = "AND (itemTitle.value LIKE '%" . $this->q . "%' OR itemCreators.item_creators LIKE '%" . $this->q . "%' )";
        }

        $tag_join = "";
        if (is_null($this->tag) === FALSE && $this->tag !== "" && strtolower($this->tag) !== "null") {
            $tag_join = "join itemTags using(itemID)
join tags on tags.tagID = itemTags.tagID and tags.name = '" . $this->tag . "'";
        }
        //echo "<!-- \n\n" . $this->tag . "\n\n -->";

        $limit_boot_type = "";
        //$limit_boot_type = "and itemTitle.itemTypeID = 2";

        $sql = "SELECT
itemTitle.itemID AS item_id, 
itemTitle.value AS item_title, 
itemCreators.item_creators AS item_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS item_date,
itemTitle.dateModified AS item_modified_date,
itemCover.item_cover_path AS item_cover_path,
itemCoverPDF.item_cover_pdf_path AS item_cover_pdf_path,
ifnull(item_attachment_count, 0) AS item_attachment_count,
itemLink.value as item_link
FROM

(items
" . $tag_join . "
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
    replace(itemAttachments.path, 'storage:', '" . $f3->get("ZOTERO_DATA_PATH") . "' || '/storage/' || items.key || '/') as item_cover_path, 
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
    replace(min(itemAttachments.path), 'storage:', '" . $f3->get("ZOTERO_DATA_PATH") . "' || '/storage/' || items.key || '/') as item_cover_pdf_path, 
    items.key, 
    items.dateModified,
    itemAttachments.parentItemID
    from itemAttachments
    left join items using (itemID)
    where
    (contentType = 'application/pdf')
    group by itemAttachments.parentItemID
    order by itemAttachments.path ASC) as itemCoverPDF on itemCreators.itemID = itemCoverPDF.parentItemID

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
" . $limit_boot_type . "
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
" . $where_search . "
ORDER BY
itemTitle.dateModified DESC";

        return $sql;
    }
    
    function get_item_collection($f3, $offset = 0, $item_id = NULL) {
        $cache = \Cache::instance();
        $key = "item_collection_" . md5($_SERVER['REQUEST_URI']);
        if ($this->is_sqlite_locked($f3)) {
            if ($cache->exists($key)) {
                return $cache->get($key);
            }
            else {
                $this->locked_zotero($f3);
                return;
            }
        }
        
        // ---------------
        
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

        //echo "<!-- \n\n" . $sql . "\n\n -->";

        $rows = $f3->db->exec($sql);

        for ($i = 0; $i < count($rows); $i++) {
            // 取代搜尋詞彙
            if (is_null($this->q)) {
                $q = $this->q;
                $rows[$i]["item_title"] = str_replace($q, '<b>' . $q . '</b>', $rows[$i]["item_title"]);
                $rows[$i]["item_creators"] = str_replace($q, '<b>' . $q . '</b>', $rows[$i]["item_creators"]);
            }

            if (FALSE && is_null($rows[$i]["item_cover_path"]) === FALSE) {
                // 檢查cache
                $cache_key = "item_cover_path" . $this->path_to_key($rows[$i]["item_cover_path"]);
                if ($cache->exists($cache_key)) {
                    $base64 = $cache->get($cache_key);
                }
                else {
                    // https://fatfreeframework.com/3.6/image
                    $cover_path = mb_convert_encoding($rows[$i]["item_cover_path"], 'big5');
                    $base64 = $this->path_to_base64($cover_path);
                    $cache->set($cache_key, $base64);
                }

                $rows[$i]["item_cover_base64"] = $base64;
            } else if (is_null($rows[$i]["item_cover_pdf_path"]) === FALSE) {
                $cache_key = "item_cover_path" . $this->path_to_key($rows[$i]["item_cover_pdf_path"]);
                $base64 = null;
                if ($cache->exists($cache_key)) {
                    $base64 = $cache->get($cache_key);
                }
                else {
                    //$path = "D:/OUTTY_DOCUMENT/Zotero/storage/RZQ6JEY7/A5.182 文本发生学_11549249.pdf";
                    $path = $rows[$i]["item_cover_pdf_path"];
                    $path = str_replace("/", "\\", $path);
                    //$path = mb_convert_encoding($path, 'big5');
                    $filesize = 10000000;
                    //echo filesize(mb_convert_encoding($path, 'big5'));
                        if (@filesize(mb_convert_encoding($path, 'big5')) < $filesize) {
                            $base_path = __DIR__;
                            //echo __DIR__;
                            $base_path = substr($base_path, 0, strrpos($base_path, "\\"));
                            $convert_path = $base_path . "\\imagemagick\\convert.exe";

                            $cover_path = $base_path . "\\tmp\\cover.gif";
                            $cmd = '"' . $convert_path . '" "' . $path . '"[0]  -flatten -resize 50x50 "' . $cover_path . '"';
                            //echo $cmd;
                            $cmd = mb_convert_encoding($cmd, 'big5');
                            
                            //exec($cmd);

                            //$rows[$i]["item_cover_pdf_path"] = filesize($path);

                            exec($cmd);
                            //$cover_path = $base_path . "\\cover.gif";
                            if (is_file($cover_path)) {
                                //echo $cover_path;
                                $base64 = $this->path_to_base64($cover_path);
                                $cache->set($cache_key, $base64);
                                unlink($cover_path);
                            }
                        }
                }
                $rows[$i]["item_cover_base64"] = $base64;
            }
        }
        
        $cache->set($key, $rows);
        return $rows;
    }
    
    function path_to_key($path) {
        $path = str_replace("/", "\\", $path);
        $parts = explode("\\", $path);
        foreach ($parts AS $key => $part) {
            if ($part === "storage") {
                return $parts[($key+1)];
            }
        }
    }
    
    function path_to_base64($cover_path) {
        $width = 50;
        $type = 'gif';
        
        $filesize_limit = 100000;
        if (filesize($cover_path) > $filesize_limit) {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA8AAAAMcAQMAAACl5F6MAAAABlBMVEW8vsDn6OnyCdevAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEbUlEQVR4AezBMQEAAADCoPVP7WsIoAcAAAAAAAAAAAAAAAAAAAAAAIydO0iOk4eCAGyVXNEuukF0FB1NHE1H0RG0ZEHRf5A99WYMk1T+Kvolw+vVjBf+CkMLoRCFWQkuqCquB3QOOQKrCpwBKJ1i6Jxk/EzTubaArgAHQOeyjhu8aMEafUrYolLjLRMfLrpw5cMYadeBnTLcrwN7ZXi+DhyuCi8GG/xy8Gyw3SRe7n7cLzfnajah5z87keFJ//lYf0WAAq+vv+rDX+fir+zpr2Xqr95qrle/ywr9OxX+/nF19fGRmR/yrzCJCudRqNHiNDHhIlquTBgCF2aX3V2BwYT9nUYdNoNojnqjiKJ56q0xiRaok4EsWqQO2EW0RJ3wQbTMvDc6iFaYswEvkx5Hnf8E0Tx1xhcfj3gh1lhOLHVWnx8vLvBq/FAnYpGBhwEElVfjhyETjVjj5bFanATpLrHIcpDkIstpJRdZLmRykaW63CLLMZKLLGeVXGS5jvlFluZKq8k1lnGMXGMZuck1lnsVu8ZydybXWOYj5BrLDIxcY5lz8mrML/JupPIC02osf3tyjeVqY9dYikys8QlTAUIsFovFYrFYLJaIP8hq8D4GG2ywwQYbbLDBBhtssMEGG2ywwQYb3N9+G/ePwQYbbLDBBhtssMEGG5yhA3ugqcARmFXgDKwqcAGgAsu+J1zYy94UXDjIRxosH+crwUm2euHCWX43Fy7YciUYI9N1YIeRavDpsMdIM9jgs+BOh4PBavA3ras6aMFRC06TEpyrLsy/H5emBKOrwvxZpsOsA3ssOk8SQQ9edZ4WI6DzfJwU4Ym/FCE7ofFXfQoJlnUugRsHdl9GTKBzlxRF6vzVW9kngr5eLTB5hT6MUhPgLfdDRhwQCQ79TQXe756tAWcp10XgIuMYF8aWxoedLtz5sB/wzIeDLrzw4QiRqHBSgOvnwLWFCrc7eCLCbnwrXFh2ZMdHKhEOyx3ciHBch8OHE2TtCZ0IZ0wCz1S4jhGTDhc0ecN9IcLja/qEVwIswqwC+/H3zfgMDw7j95cbPBFg+YEKnIaGWyoNzpvmcEujws0rwGX7LnCnwWOgDLhlZsFuaJEP+zFCC7yw4DCAhFtWFhwHl/lwwpeowRMJzlpw+QpXEgwl2O3gxoH9Du4cOOzgmQNHLTjt4OXF4byDVw5csAsHhhLssM/EgD32qQw4aMER+zQtuDPgpAVn7DMz4IJ9FgYMJdgdwSsB9jgKAQ5acDyEJy24ng8nLTgfwu18uBzC/XwYSrA7hmfCeyCHWU6Hw18Gr4R3fY5zOpy04PwEngj/KfYw9WwYSrB7BjfCW4qH6a8Kh2fwTHgF9jDLyXDSgvMzeKW8UX6Uk2Eowe45PL0m7J/D9VQ4aMG/isH/JwYbbLDBBhtssMEGG2ywtz3K/2tvDmQAAAAABvlbn+NbCSQWi8VisVgsFovFYrFYLBYvAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEHVJjR5bJLd8AAAAASUVORK5CYII=';
        }

        list($width_orig, $height_orig) = getimagesize($cover_path);
        $aspectRatio = $height_orig / $width_orig;
        $height = intval($aspectRatio * $width);

        $image_p = imagecreatetruecolor($width, $height);
        try {
            $image = imagecreatefromstring(file_get_contents($cover_path));
        }
        catch (Exception $e) {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA8AAAAMcAQMAAACl5F6MAAAABlBMVEW8vsDn6OnyCdevAAAACXBIWXMAAAsTAAALEwEAmpwYAAAEbUlEQVR4AezBMQEAAADCoPVP7WsIoAcAAAAAAAAAAAAAAAAAAAAAAIydO0iOk4eCAGyVXNEuukF0FB1NHE1H0RG0ZEHRf5A99WYMk1T+Kvolw+vVjBf+CkMLoRCFWQkuqCquB3QOOQKrCpwBKJ1i6Jxk/EzTubaArgAHQOeyjhu8aMEafUrYolLjLRMfLrpw5cMYadeBnTLcrwN7ZXi+DhyuCi8GG/xy8Gyw3SRe7n7cLzfnajah5z87keFJ//lYf0WAAq+vv+rDX+fir+zpr2Xqr95qrle/ywr9OxX+/nF19fGRmR/yrzCJCudRqNHiNDHhIlquTBgCF2aX3V2BwYT9nUYdNoNojnqjiKJ56q0xiRaok4EsWqQO2EW0RJ3wQbTMvDc6iFaYswEvkx5Hnf8E0Tx1xhcfj3gh1lhOLHVWnx8vLvBq/FAnYpGBhwEElVfjhyETjVjj5bFanATpLrHIcpDkIstpJRdZLmRykaW63CLLMZKLLGeVXGS5jvlFluZKq8k1lnGMXGMZuck1lnsVu8ZydybXWOYj5BrLDIxcY5lz8mrML/JupPIC02osf3tyjeVqY9dYikys8QlTAUIsFovFYrFYLJaIP8hq8D4GG2ywwQYbbLDBBhtssMEGG2ywwQYb3N9+G/ePwQYbbLDBBhtssMEGG5yhA3ugqcARmFXgDKwqcAGgAsu+J1zYy94UXDjIRxosH+crwUm2euHCWX43Fy7YciUYI9N1YIeRavDpsMdIM9jgs+BOh4PBavA3ras6aMFRC06TEpyrLsy/H5emBKOrwvxZpsOsA3ssOk8SQQ9edZ4WI6DzfJwU4Ym/FCE7ofFXfQoJlnUugRsHdl9GTKBzlxRF6vzVW9kngr5eLTB5hT6MUhPgLfdDRhwQCQ79TQXe756tAWcp10XgIuMYF8aWxoedLtz5sB/wzIeDLrzw4QiRqHBSgOvnwLWFCrc7eCLCbnwrXFh2ZMdHKhEOyx3ciHBch8OHE2TtCZ0IZ0wCz1S4jhGTDhc0ecN9IcLja/qEVwIswqwC+/H3zfgMDw7j95cbPBFg+YEKnIaGWyoNzpvmcEujws0rwGX7LnCnwWOgDLhlZsFuaJEP+zFCC7yw4DCAhFtWFhwHl/lwwpeowRMJzlpw+QpXEgwl2O3gxoH9Du4cOOzgmQNHLTjt4OXF4byDVw5csAsHhhLssM/EgD32qQw4aMER+zQtuDPgpAVn7DMz4IJ9FgYMJdgdwSsB9jgKAQ5acDyEJy24ng8nLTgfwu18uBzC/XwYSrA7hmfCeyCHWU6Hw18Gr4R3fY5zOpy04PwEngj/KfYw9WwYSrB7BjfCW4qH6a8Kh2fwTHgF9jDLyXDSgvMzeKW8UX6Uk2Eowe45PL0m7J/D9VQ4aMG/isH/JwYbbLDBBhtssMEGG2ywtz3K/2tvDmQAAAAABvlbn+NbCSQWi8VisVgsFovFYrFYLBYvAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEHVJjR5bJLd8AAAAASUVORK5CYII=';
        }
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

        ob_start(); // Let's start output buffering.
        imagegif($image_p); //This will normally output the image, but because of ob_start(), it won't.
        $contents = ob_get_contents(); //Instead, output above is saved to $contents
        ob_end_clean(); //End the output buffer.

        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($contents);
        return $base64;
    }

    function get_items_count($f3) {
        $cache = \Cache::instance();
        $key = "item_count_" . md5($_SERVER['REQUEST_URI']);
        if ($this->is_sqlite_locked($f3)) {
            if ($cache->exists($key)) {
                return $cache->get($key);
            }
            else {
                $this->locked_zotero($f3);
                return;
            }
        }
        
        $item_collection_sql = $this->get_item_collection_sql($f3);
        $sql = "select count(*) as items_count
from (" . $item_collection_sql . ") as a";

        //echo "<!-- \n\n" . $sql . "\n\n -->";

        $rows = $f3->db->exec($sql);
        $rows = $rows[0]["items_count"];
        
        $cache->set($key, $rows);

        return $rows;
    }

    function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    function endsWith($haystack, $needle) {
        $length = strlen($needle);

        return $length === 0 ||
                (substr($haystack, -$length) === $needle);
    }

}
