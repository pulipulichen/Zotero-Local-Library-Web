select 
itemDataValuesLink.value
from 
itemAttachments 
    join itemData as itemDataTitle on itemAttachments.itemID = itemDataTitle.itemID and itemDataTitle.fieldID = 110
    join itemDataValues as itemDataValuesTitle on itemDataTitle.valueID = itemDataValuesTitle.valueID and value = 'Google Drive'
    join itemData as itemDataLink on itemAttachments.itemID = itemDataLink.itemID and fieldID = 1
    join itemDataValues as itemDataValuesLink on itemDataLink.valueID = itemDataValuesLink.valueID
where 
linkMode = 3
and parentItemID = 17392

-- 25216

select *
from items join itemData using(itemID) join itemDataValues using(valueID)
where items.itemID = 25216

-- fieldID = 1