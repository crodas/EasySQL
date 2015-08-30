-- @name users
CREATE TABLE users (
    user_id int not null primary key,
    email varchar(250) not null,
    password varchar(250) not null
);
