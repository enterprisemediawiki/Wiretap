CREATE TABLE IF NOT EXISTS /*_*/wiretap (
	page_id INT(8) UNSIGNED NOT NULL,
	page_name VARCHAR(255) DEFAULT NULL,
	user_name VARCHAR(255) DEFAULT NULL,
	hit_timestamp CHAR(14) DEFAULT NULL,

	hit_year CHAR(4) DEFAULT NULL,
	hit_month CHAR(2) DEFAULT NULL,
	hit_day CHAR(2) DEFAULT NULL,
	hit_hour CHAR(2) DEFAULT NULL,
	hit_weekday TINYINT DEFAULT NULL,

	page_action VARCHAR(255) DEFAULT NULL,
	oldid INT(10) UNSIGNED DEFAULT NULL,
	diff VARCHAR(10) DEFAULT NULL, /* VARCHAR required if diff=prev */

	referer_url TEXT DEFAULT NULL,   /* full url of referring page */
	referer_title VARCHAR(255) DEFAULT NULL,  /* null if not a wiki page */

	response_time INT(5) UNSIGNED NOT NULL

);

-- CREATE OR REPLACE VIEW /*_*/user_page_hits AS SELECT
	-- u.user_name AS user_name,
	-- u.user_real_name AS user_real_name,
	-- p.page_namespace AS page_namespace,
	-- p.page_title AS page_title,
	-- v.hits AS hits,
	-- v.last AS last
-- FROM (/*_*/user u JOIN /*_*/page p) JOIN /*_*/user_page_views v 
-- WHERE u.user_id = v.user_id AND p.page_id = v.page_id
-- ORDER BY u.user_id, v.hits DESC;