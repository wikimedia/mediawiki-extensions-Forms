-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: db/form_autosave.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE form_autosave (
  fa_id SERIAL NOT NULL,
  fa_data TEXT DEFAULT NULL,
  fa_timestamp TIMESTAMPTZ NOT NULL,
  PRIMARY KEY(fa_id)
);
