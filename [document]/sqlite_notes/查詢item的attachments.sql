select itemAttachments.*, items.key 
from itemAttachments
left join items using (itemID)
where
itemAttachments.parentItemID = 689  -- itemID 689 = 操作介面設計模式

select *
from item
where itemID = 17836