SELECT
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
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
and itemTitle.itemID = 17392
ORDER BY
itemTitle.dateModified DESC