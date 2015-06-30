CREATE TABLE IF NOT EXISTS /*_*/wiretap_counter (
	page_id            INT(8) UNSIGNED NOT NULL,
	counter_hour       INT(8) UNSIGNED NOT NULL DEFAULT 0,
	counter_prev_hour  INT(8) UNSIGNED NOT NULL DEFAULT 0,
	counter_day        INT(8) UNSIGNED NOT NULL DEFAULT 0,
	counter_prev_day   INT(8) UNSIGNED NOT NULL DEFAULT 0,
	counter_week       INT(8) UNSIGNED NOT NULL DEFAULT 0,
	counter_month      INT(8) UNSIGNED NOT NULL DEFAULT 0,
	counter_all_time   INT(8) UNSIGNED NOT NULL DEFAULT 0
);
