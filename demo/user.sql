-- @name createTable
BEGIN;
CREATE TABLE table_name("user") (id integer not null primary key auto_increment, email varchar(250) not null);
SELECT * FROM table_name("user") where foo=$foo;
COMMIT;

-- @name Foobar
-- @mapWith UserObject
-- This is a query for something, and `@single` makes sure
-- we only return a single value
SELECT 
    * 
FROM 
    table_name("user") -- we can store it
WHERE id = $id LIMIT 1;

-- @name YetAnotherQuery
-- @default limit 1
SELECT password FROM table_name("user") WHERE email = $email LIMIT $limitx;

-- @name InsertData
INSERT INTO table_name("user")(email) VALUES($email);
