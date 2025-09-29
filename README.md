## Таска 1
```sql
-- users(id, first_name, last_name, birthday)
-- books (id, name, author)
-- user_books (id, user_id, book_id, get_date, return_date)


select 
    u.id as user_id,
    concat_ws(' ', trim(u.first_name), trim(u.last_name)) as fullname, -- не сказано наллабл или нет, но предположим, что нет
    min(b.author) as author, -- автор один, но убирает варнинг из-за груп бая т.к. агрегация
    group_concat(
        distinct trim(b.name) order by b.name separator ', '
    ) as books_ordered
    from users u
    inner join user_books ub on u.id = ub.user_id
    inner join books b on ub.book_id = b.id
        where u.birthday between curdate() - interval 17 year and curdate() - interval 7 year
        and (
            (ub.return_date is not null and ub.return_date <= ub.get_date + interval 14 day) -- в задаче не сказано, но на всякий, если посетитель не вернул
            or (ub.return_date is null and curdate() <= ub.get_date + interval 14 day) -- тоже по факту не просрочили
        )
    group by u.id
    having count(ub.id) = 2 and count(distinct b.author) = 1;

    -- еще индексы можно добавить на [get_date, return_date] в user_books и [user_id, book_id] из-за джоинов
```