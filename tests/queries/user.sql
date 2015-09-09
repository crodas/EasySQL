-- @name byId
-- @mapAs User
SELECT *, 'user_id' as table_pk FROM `users` WHERE user_id = $id LIMIT 1;

-- @name all
-- @mapAs User
-- @default("foo", NULL)
SELECT *, 'user_id' as table_pk FROM users WHERE user_id != $foo LIMIT get_limit();

-- @name pluck1
-- @pluck
SELECT user_id FROM users;

-- @name pluck2
-- @pluck
SELECT user_id, email FROM users;

-- @name create
INSERT INTO users(user_id, email, password) VALUES((select count(*) +1 from users as x), :user, sha1(:password));
