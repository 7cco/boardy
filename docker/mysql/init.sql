CREATE DATABASE IF NOT EXISTS boardy_main 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

CREATE DATABASE IF NOT EXISTS boardy_api 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

GRANT ALL ON boardy_main.* TO 'boardy'@'%'; 
GRANT ALL ON boardy_api.* TO 'boardy'@'%'; 
FLUSH PRIVILEGES;

USE boardy_api;
CREATE TABLE comments ( 
    id BIGINT PRIMARY KEY AUTO_INCREMENT, 
    post_id BIGINT NOT NULL, 
    author_id BIGINT NOT NULL, 
    author_name VARCHAR(255) NOT NULL, 
    body TEXT NOT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
    INDEX idx_post_id (post_id), 
    INDEX idx_author_id (author_id) 
); 