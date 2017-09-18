select *,
REGEXP_REPLACE(note, '<.*?>', "")
from itemNotes
where parentItemID = 17392