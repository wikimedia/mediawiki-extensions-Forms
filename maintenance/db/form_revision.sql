CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/form_revision (
	fr_rev_id INT(6) UNSIGNED NOT NULL PRIMARY KEY,
	fr_page_id INT(6) UNSIGNED NOT NULL,
	fr_applies_from VARCHAR( 15 ) NOT NULL
)/*$wgDBTableOptions*/;
