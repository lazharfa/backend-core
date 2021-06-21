CREATE OR REPLACE VIEW amount_donation AS
SELECT users.id, date_part('year', donations.date_donation) year_donation, count(donations.id) total_donation
FROM users
LEFT JOIN donations ON users.id = donations.user_id
WHERE donations.id IS NOT NULL
GROUP BY users.id, date_part('year', donations.date_donation)
ORDER BY year_donation, total_donation;

CREATE OR REPLACE VIEW donor_year_birth AS
SELECT date_part('year', birthday) year_birth, count(id)
FROM users
WHERE birthday IS NOT NULL
GROUP BY year_birth;

CREATE OR REPLACE VIEW donor_of_work AS
SELECT work, count(id)
FROM users
WHERE work IS NOT NULL
GROUP BY work;


CREATE OR REPLACE VIEW donation_by_category AS
SELECT categories.id, category_name, COUNT(DISTINCT(donations.user_id))
FROM donations
LEFT JOIN campaigns ON donations.campaign_id = campaigns.id
LEFT JOIN categories ON campaigns.category_id = categories.id
GROUP BY categories.id, category_name;

CREATE OR REPLACE VIEW total_donation_donor AS
SELECT user_id, SUM(total_donation) total_donation
FROM donations
GROUP BY user_id
HAVING SUM(total_donation) >= 100000;

SELECT
