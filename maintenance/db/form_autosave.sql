CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/form_autosave (
	fa_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	fa_data BLOB NULL,
	fa_timestamp varbinary(15) NOT NULL
)/*$wgDBTableOptions*/;
