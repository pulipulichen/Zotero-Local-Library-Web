select group_concat(creators.lastName, ', ') as field_creators
from items
left join itemCreators using(itemID) 
left join creators using(creatorID)
where itemID = 1
and creatorTypeID = 1
group by itemID
order by orderIndex