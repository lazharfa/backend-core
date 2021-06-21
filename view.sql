CREATE OR REPLACE view history_unique as
    SELECT DISTINCT (public.donations.donation + public.donations.unique_value) AS total_donation
    FROM public.donations
    WHERE (public.donations.expired_at > ((now())::date - '2 days'::interval))
    UNION
    SELECT DISTINCT public.payments.total_payment AS total_donation
    FROM public.payments
    WHERE ((public.payments.claim_at IS NULL) AND (public.payments.payment_at > ((now())::date - '2 days'::interval)))
    UNION
    SELECT DISTINCT (qurban.invoices.total) AS total_donation
    FROM qurban.invoices
    WHERE (qurban.invoices.created_at > ((now())::date - '2 days'::interval))
    ORDER BY total_donation;

create or replace view public.payment_received(id, description, total_payment, payment_at, bank_info) as
SELECT payments.id,
       CASE
           WHEN (payments.donation_id IS NOT NULL) THEN concat('Penerimaan Program ', campaigns.campaign_title, ' / ', donations.donor_name)
           WHEN (payments.invoice_id IS NOT NULL) THEN concat('Penerimaan Qurban 2019 a.n ', invoices.report_name)
           WHEN (donations.type_donation IS NOT NULL) THEN concat('Penerimaan ', donations.type_donation)
           ELSE NULL::text
           END                                                                        AS description,
       payments.total_payment,
       ((payments.payment_at + '07:00:00'::interval))::timestamp(0) without time zone AS payment_at,
       member_banks.bank_info
FROM ((((payments
    LEFT JOIN donations ON ((payments.donation_id = donations.id)))
    LEFT JOIN campaigns ON ((donations.campaign_id = campaigns.id)))
    LEFT JOIN member_banks ON ((payments.bank_id = member_banks.id)))
         LEFT JOIN qurban.invoices ON ((payments.invoice_id = invoices.id)))
ORDER BY (((payments.payment_at + '07:00:00'::interval))::timestamp(0) without time zone);




CREATE OR REPLACE VIEW public.amount_donation as
SELECT users.id,
       date_part('year' :: text, donations.date_donation) AS year_donation,
       count(donations.id)                                AS total_donation
FROM (users
       LEFT JOIN donations ON ((users.id = donations.donor_id)))
WHERE (donations.id IS NOT NULL)
GROUP BY users.id, (date_part('year' :: text, donations.date_donation))
ORDER BY (date_part('year' :: text, donations.date_donation)), (count(donations.id));

CREATE OR REPLACE VIEW public.amount_donation as
SELECT users.id,
       date_part('year' :: text, donations.date_donation) AS year_donation,
       count(donations.id)                                AS total_donation
FROM (users
       LEFT JOIN donations ON ((users.id = donor_id)))
WHERE (donations.id IS NOT NULL)
GROUP BY users.id, (date_part('year' :: text, donations.date_donation))
ORDER BY (date_part('year' :: text, donations.date_donation)), (count(donations.id));

CREATE OR REPLACE VIEW public.donor_of_work as
SELECT users.work, count(users.id) AS count
FROM users
WHERE (users.work IS NOT NULL)
GROUP BY users.work;

CREATE OR REPLACE VIEW public.donor_year_birth as
SELECT date_part('year' :: text, users.birthday) AS year_birth, count(users.id) AS count
FROM users
WHERE (users.birthday IS NOT NULL)
GROUP BY (date_part('year' :: text, users.birthday));

CREATE OR REPLACE VIEW public.total_donation_donor as
SELECT donor_id, sum(donations.total_donation) AS total_donation
FROM donations
GROUP BY donor_id
HAVING (sum(donations.total_donation) >= (100000) :: double precision);


CREATE OR REPLACE VIEW donor_by_transaction AS
SELECT donors.id, donors.full_name donor_name, donors.phone_number donor_phone, donors.email donor_email, count(donations.id) total_transaction, sum(total_donation) total_donation
FROM donations
       LEFT JOIN users donors ON donations.donor_id = donors.id
WHERE donations.verified_at is not null
GROUP BY donors.id, donors.full_name, donors.phone_number, donors.email
ORDER BY sum(total_donation) DESC;


CREATE OR REPLACE VIEW donor_by_category AS
SELECT donors.id, donors.full_name donor_name, donors.phone_number donor_phone, donors.email donor_email, count(donations.id) total_transaction, sum(total_donation) total_donation
FROM donations
       LEFT JOIN users donors ON donations.donor_id = donors.id
       LEFT JOIN campaigns ON donations.campaign_id = campaigns.id
       LEFT JOIN categories ON campaigns.category_id = categories.id
WHERE donations.verified_at is not null
GROUP BY donors.id, donors.full_name, donors.phone_number, donors.email, categories.category_name
ORDER BY sum(total_donation) DESC;

create or replace view public.donors as
SELECT donations.donor_name,
       donations.donor_phone,
       donations.donor_email
FROM donations
WHERE verified_at IS NOT NULL
   OR auto_verified_at IS NOT NULL
GROUP BY donations.donor_name, donations.donor_phone, donations.donor_email;

CREATE OR REPLACE VIEW donation_view AS
SELECT (donations.date_donation + INTERVAL '7h')::timestamp(0) date_donation,
       donations.donor_name,
       donations.donor_phone,
       donations.donor_email,
       member_banks.bank_info,
       campaigns.campaign_title,
       staffs.full_name creator_name,
       donations.note,
       CASE
         WHEN total_donation IS NULL THEN 'Pending'
         WHEN total_donation IS NOT NULL THEN 'Complete'
       END status,
       donations.donation + donations.unique_value total_donation
FROM donations
       LEFT JOIN member_banks ON donations.bank_id = member_banks.id
       LEFT JOIN campaigns ON donations.campaign_id = campaigns.id
       LEFT JOIN users staffs ON donations.staff_id = staffs.id;

