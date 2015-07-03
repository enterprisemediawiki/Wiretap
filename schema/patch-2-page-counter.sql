CREATE TABLE IF NOT EXISTS /*_*/wiretap_counter_period (
	page_id        INT(8) UNSIGNED NOT NULL,

	count          INT(8) UNSIGNED NOT NULL DEFAULT 0,
	count_unique   INT(8) UNSIGNED NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS /*_*/wiretap_counter_alltime (
	page_id        INT(8) UNSIGNED NOT NULL,

	count          INT(8) UNSIGNED NOT NULL DEFAULT 0,
	count_unique   INT(8) UNSIGNED NOT NULL DEFAULT 0
);
