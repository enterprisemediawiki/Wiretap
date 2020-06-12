CREATE TABLE IF NOT EXISTS /*_*/wiretap_legacy (
	legacy_id        INT(8) UNSIGNED NOT NULL,
	legacy_counter   INT(8) UNSIGNED NOT NULL DEFAULT 0
) /*$wgDBTableOptions*/;
DROP INDEX /*i*/wiretap_counter_legacy_page_id ON /*_*/wiretap_legacy;
CREATE UNIQUE INDEX /*i*/wiretap_counter_legacy_page_id ON /*_*/wiretap_legacy (legacy_id);
