# PHP-Facebook_likes

Full article: http://prophp.pl/article/27/system_reakcji_a_la_facebook_z_wykorzystaniem_jquery%2C_mysql_i_php

Article describes how to create simple Facebook likes system.

Mysql CREATE script:

```sql
CREATE TABLE `likes` (
    `page_id` INT(11) NOT NULL,
    `user` INT(10) UNSIGNED NOT NULL,
    `like_status` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`page_id`, `user`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
```
