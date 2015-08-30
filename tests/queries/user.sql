-- @name byId
SELECT * FROM `users` WHERE user_id = $id LIMIT 1;

-- @name create
INSERT INTO users(user_id, email, password) VALUES((select count(*)+1 from users), :user, :password);
