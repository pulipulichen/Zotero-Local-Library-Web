select items.itemID, fields.fieldID, fieldName, value 
from items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)
where itemTypeID = 2
and (fields.fieldID = 110)
and items.itemID = 689
limit 100