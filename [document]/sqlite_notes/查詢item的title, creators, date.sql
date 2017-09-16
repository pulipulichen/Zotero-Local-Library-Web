SELECT
itemTitle.value as field_title, 
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS field_date
FROM
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemTitle,
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemDate
WHERE itemTitle.itemID = 1
and itemTitle.itemID = itemDate.itemID
and itemTitle.fieldID = 110
and itemDate.fieldID = 14