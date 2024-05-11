DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS picture;
DROP TABLE IF EXISTS profile;

CREATE TABLE IF NOT EXISTS profile (id serial PRIMARY KEY, uuid VARCHAR(23), firstname VARCHAR, surname VARCHAR, username VARCHAR UNIQUE NOT NULL, validpass VARCHAR(60) UNIQUE NOT NULL, activated INTEGER, notify BOOLEAN, wallpaper VARCHAR, created_on TIMESTAMP NOT NULL, last_login TIMESTAMP);
CREATE TABLE IF NOT EXISTS picture (id serial PRIMARY KEY, title VARCHAR, user_id INTEGER, author VARCHAR, img VARCHAR, created_on TIMESTAMP NOT NULL, CONSTRAINT fk_user FOREIGN KEY(user_id) REFERENCES profile(id));
CREATE TABLE IF NOT EXISTS comments (id serial PRIMARY KEY, user_id INTEGER, author VARCHAR, picture_id INTEGER, content VARCHAR NOT NULL, created_on TIMESTAMP NOT NULL, CONSTRAINT fk_user FOREIGN KEY(user_id) REFERENCES profile(id), CONSTRAINT fk_picture FOREIGN KEY(picture_id) REFERENCES picture(id));
CREATE TABLE IF NOT EXISTS likes (id serial PRIMARY KEY, user_id INTEGER, author VARCHAR, picture_id INTEGER, created_on TIMESTAMP NOT NULL, CONSTRAINT fk_user FOREIGN KEY(user_id) REFERENCES profile(id), CONSTRAINT fk_picture FOREIGN KEY(picture_id) REFERENCES picture(id));

ALTER TABLE profile ALTER COLUMN validpass DROP NOT NULL;
ALTER TABLE profile ALTER COLUMN created_on DROP NOT NULL;
ALTER TABLE picture ALTER COLUMN created_on DROP NOT NULL;
ALTER TABLE comments ALTER COLUMN created_on DROP NOT NULL;
ALTER TABLE likes ALTER COLUMN created_on DROP NOT NULL;
