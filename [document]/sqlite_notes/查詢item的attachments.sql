select itemAttachments.*, items.key, items.dateModified 
from itemAttachments
left join items using (itemID)
where
itemAttachments.parentItemID = 689 