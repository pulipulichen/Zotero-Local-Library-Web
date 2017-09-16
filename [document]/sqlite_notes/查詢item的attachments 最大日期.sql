select max(items.dateModified)
from itemAttachments
left join items using (itemID)
where
itemAttachments.parentItemID = 689 
group by itemAttachments.parentItemID