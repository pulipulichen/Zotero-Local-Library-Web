select fields.fieldID, fieldName, value 
from items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)
where itemID = 1
and (fields.fieldID = 14 or fields.fieldID = 110)