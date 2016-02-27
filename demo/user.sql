-- @name createTable
CREATE TABLE users (id integer not null primary key auto_increment, email varchar(250) not null);

-- @name Foobar
-- @mapWith UserObject
-- This is a query for something, and `@single` makes sure
-- we only return a single value
SELECT 
    * 
FROM 
    users -- we can store it
WHERE id = $id LIMIT 1;

-- @name YetAnotherQuery
-- @default limit 1
SELECT password FROM users WHERE email = $email LIMIT $limitx;

-- @name InsertData
INSERT INTO users(email) VALUES($email);
