-- Paygate
SELECT payments.id,
       CASE WHEN payments.donation_id IS NOT NULL THEN
              concat('Penerimaan Program ', campaigns.campaign_title, ' / ', donations.donor_name)
         ELSE null END description,
       payments.total_payment,
       (payment_at + INTERVAL '7h')::timestamp(0) payment_at , member_banks.bank_info
FROM payments
       LEFT JOIN donations ON payments.donation_id = donations.id
       LEFT JOIN campaigns ON donations.campaign_id = campaigns.id
LEFT JOIN member_banks ON payments.bank_id = member_banks.id
ORDER BY payment_at