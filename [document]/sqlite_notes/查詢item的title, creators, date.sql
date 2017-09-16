SELECT
itemTitle.value as field_title, 
group_concat(itemCreators.lastName, ', ') as field_creators,
substr(itemDate.value, instr(itemDate.value, ' ') + 1) AS field_date,
itemTitle.dateModified as field_modefied_date
FROM
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemTitle,
(items
left join itemData using(itemID) 
left join itemDataValues using(valueID)
left join fields using(fieldID)) as itemDate,
(items
left join itemCreators using(itemID) 
left join creators using(creatorID)) as itemCreators
WHERE itemTitle.itemID = 689
and itemTitle.itemID = itemDate.itemID
and itemTitle.itemID = itemCreators.itemID
and itemTitle.fieldID = 110
and itemDate.fieldID = 14
and itemCreators.creatorTypeID = 1
ORDER BY
itemTitle.dateModified DESC