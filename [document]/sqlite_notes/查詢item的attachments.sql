select items.itemID, fields.fieldID, fieldName, value, itemAttachments.* 
from items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)
left join itemAttachments using(itemID)
where itemTypeID = 2
and (fields.fieldID = 110)
and items.itemID = 689  -- 操作介面設計模式