/**
 * events module
 * query for url_placeholder[year] 
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


SELECT MIN(y) AS min_year, MAX(y) AS max_year
FROM (
	SELECT YEAR(IFNULL(date_begin, date_end)) AS y
	FROM /*_PREFIX_*/events
) AS years;
