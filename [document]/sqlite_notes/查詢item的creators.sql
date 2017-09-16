select creators.lastName, creators.firstName
from items
left join itemCreators using(itemID) 
left join creators using(creatorID)
where itemID = 1
and creatorTypeID = 1 -- 作者
order by orderIndex