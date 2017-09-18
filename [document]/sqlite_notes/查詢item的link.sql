select 
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
and parentItemID = 17392
group by parentItemID
order by dateModified DESC