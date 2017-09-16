select count(attachments_count) as items_count
from (
select count(items.itemID) as attachments_count
from items
join itemAttachments 
on items.itemID = itemAttachments.parentItemID 
and itemAttachments.contentType = 'application/pdf'
and items.itemTypeID = 2
group by items.itemID) as a