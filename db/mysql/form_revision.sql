-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: db/form_revision.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/form_revision (
  fr_rev_id INT NOT NULL,
  fr_page_id INT NOT NULL,
  fr_applies_from BINARY(14) NOT NULL,
  PRIMARY KEY(fr_rev_id)
) /*$wgDBTableOptions*/;