select 
replace(itemAttachments.path, 'storage:', 'D:/OUTTY_DOCUMENT/Zotero' || '/storage/' || items.key || '/') as item_cover_path, 
items.key, 
items.dateModified
from itemAttachments
left join items using (itemID)
where
(contentType = 'image/png' or contentType = 'image/gif' or contentType = 'image/jpeg')
and itemAttachments.parentItemID = 17392
group by itemAttachments.parentItemID
order by dateModified DESC