-- @name createTable
BEGIN;
CREATE TABLE usertbl (id integer not null primary key auto_increment, email varchar(250) not null);
SELECT * FROM usertbl where foo=$foo;
COMMIT;

-- @name Foobar
-- @mapWith UserObject
-- This is a query for something, and `@single` makes sure
-- we only return a single value
SELECT 
    * 
FROM 
    $usertbl -- we can store it
WHERE id = $id LIMIT 1;

-- @name YetAnotherQuery
-- @default limit 1
SELECT password FROM $usertbl WHERE email = $email LIMIT $limitx;

-- @name InsertData
INSERT INTO $usertbl(email) VALUES($email);
