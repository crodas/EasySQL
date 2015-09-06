-- @name byId
-- @mapAs User
SELECT * FROM `users` WHERE user_id = $id LIMIT 1;

-- @name all
-- @mapAs User
-- @default("foo", NULL)
SELECT * FROM users WHERE user_id != $foo LIMIT get_limit();

-- @name create
INSERT INTO users(user_id, email, password) VALUES((select count(*) +1 from users as x), :user, sha1(:password));
