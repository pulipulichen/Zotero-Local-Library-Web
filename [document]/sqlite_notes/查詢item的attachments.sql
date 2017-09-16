select replace(itemAttachments.path, 'storage:', '') as title, items.key, items.dateModified 
from itemAttachments
left join items using (itemID)
where
itemAttachments.parentItemID = 689 
order by title + 0