CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/form_data (
	fd_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	fd_title VARCHAR( 255 ) NULL DEFAULT '',
	fd_form VARCHAR( 255 ) NOT NULL,
	fd_data TEXT,
	fd_user INT(6) UNSIGNED,
	fd_timestamp VARCHAR( 15 ) NOT NULL
)/*$wgDBTableOptions*/;
