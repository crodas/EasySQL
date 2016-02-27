# EasySQL

Easiest SQL abstraction ever, heavily inspired by [yesql](https://github.com/krisajenkins/yesql)

## Concepts

I've been in a quest all my professional life, create the simplest SQL-abstraction for PHP. I [gave up](https://github.com/crodas/ActiveMongo2) many times until I saw [yesql](https://github.com/krisajenkins/yesql)'s simplicity. 

It's a tiresome task to reinvent SQL, and often in vain. You need to give away functionality (what makes SQL unique) or performance (something unthinkable for most web apps). 

## How does it work?

EasySQL is heavily inspired by [yesql](https://github.com/krisajenkins/yesql), that means you have a different file with a list of SQL statements, which is compiled to a PHP class with all those operations.

```sql
-- This file is saved as queries/users.sql
-- @name byEmail
SELECT * FROM user WHERE email = $email LIMIT 1;

-- @name getContacts
SELECT 
  u.id, u.name, u.email 
FROM user 
INNER JOIN contacts c (c.friend_of = u.id)
WHERE c.user_id = $me;

-- @name create
INSERT INTO user(email) VALUES($email);
```

Then you need the following PHP bootstrap code to get it running:

```php
$repo = new EasySQL\EasySQL("queries", $pdo);
$users = $repo->getRepository("users");

// find and return a single row due to the `LIMIT 1`. To force 
// a single row we can use @One annotation.
$me = $users->byEmail("crodas@php.net");

// All `INSERT` return the new created ID
$user_id = $users->create("crodas@php.net");
```

Underneath the EasySQL's compiler will read all files and their SQL statements and generate a PHP file with all the queries and bootstrap code.
