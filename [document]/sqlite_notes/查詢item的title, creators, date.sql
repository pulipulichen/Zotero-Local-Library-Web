SELECT
itemTitle.value AS item_title, 
itemCreators.item_creators AS item_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS item_date,
itemTitle.dateModified AS item_modified_date,
itemCover.item_cover_path AS item_cover_path

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

left join 
(select 
    replace(itemAttachments.path, 'storage:', 'D:/OUTTY_DOCUMENT/Zotero' || '/storage/' || items.key || '/') as item_cover_path, 
    items.key, 
    items.dateModified,
    itemAttachments.parentItemID
    from itemAttachments
    left join items using (itemID)
    where
    (contentType = 'image/png' or contentType = 'image/gif' or contentType = 'image/jpeg')
    group by itemAttachments.parentItemID
    order by dateModified DESC) as itemCover on itemCreators.itemID = itemCover.parentItemID

WHERE 
itemTitle.itemID = itemDate.itemID
and itemTitle.itemID = itemCreators.itemID
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
and itemTitle.itemID = 689
ORDER BY
itemTitle.dateModified DESC