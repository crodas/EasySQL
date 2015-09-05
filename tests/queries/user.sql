-- @name byId
-- @mapAs User
SELECT * FROM `users` WHERE user_id = $id LIMIT 1;

-- @name all
-- @mapAs User
SELECT * FROM users;

-- @name create
INSERT INTO users(user_id, email, password) VALUES((select count(*)+1 from users), :user, sha1(:password));
