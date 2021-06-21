-- Migrate News Program
SELECT CASE
         WHEN page_content.title IS NULL THEN null
         ELSE CONCAT('(SELECT id FROM campaigns WHERE campaign_title = "', page_content.title, '")')
         END                       campaign_id,
        'insanbumimandiri.org' member_id,
       donate_progress.title       news_title,
       donate_progress.title       news_title_en,
       donate_progress.description news_content,
       donate_progress.description news_content_en,
       image                       news_image,
       8                           category_id,
       donate_progress.date        news_date,
       donate_progress.date        sent_at,
       donate_progress.date        created_at,
       donate_progress.date        updated_at,
       1                           creator_id
FROM donate_progress
       LEFT JOIN page ON donate_progress.donate_id = page.page_id
       LEFT JOIN page_content ON page.page_id = page_content.page_id
WHERE page_content.donate_need <> 0
ORDER BY donate_progress.progress_id;


-- Migrate Donation
SELECT 'insanbumimandiri.org'                       member_id,
       CASE
         WHEN page_content.title IS NULL THEN null
         ELSE CONCAT('(SELECT id FROM campaigns WHERE campaign_title = "', page_content.title, '")')
         END                                        campaign_id,
       donasi                                       donation,
       donasi + unique_id                           total_donation,
       CASE
         WHEN unique_id IS NULL THEN 0
         ELSE unique_id END                         unique_value,
       CASE
         WHEN bank_id = 6 THEN 5
         WHEN bank_id = 7 THEN 6
         WHEN bank_id = 0 THEN null
         ELSE bank_id
         END                                        bank_id,
       name                                         donor_name,
       telepon                                      donor_phone,
       donations.email                                        donor_email,
       CASE WHEN donations.status = 1 THEN date ELSE null END verified_at,
       date + interval 1 DAY                        expired_at,
       date                                         date_donation,
       created_at                                   created_at,
       created_at                                   updated_at,
       CASE
         WHEN donations.created_by IS NULL THEN null
         ELSE CONCAT('(SELECT id FROM users WHERE email = "', user.email, '")')
         END                                         staff_id,
       CASE
         WHEN anonim = 0 THEN 'false'
         ELSE 'true'
         END                                        anonymous,
       tipe_donasi                                  type_donation,
       donations.description                        note
FROM transaksi donations
       LEFT JOIN page_content ON donations.page_id = page_content.page_id
LEFT JOIN user ON donations.created_by = user.user_id
ORDER BY transaksi_id ASC;


-- Migrate Campaign
SELECT ' insanbumimandiri.org'                                                                     member_id,
       1                                                                                           creator_id,
       page_content.title                                                                          campaign_title,
       page_content.title                                                                          campaign_title_en,
       page_menu.guid                                                                              campaign_slug,
       page_menu.guid                                                                              campaign_slug_en,
       page_content.donate_need                                                                    target_donation,
       page_content.end_donate                                                                     expired_at,
       case
         WHEN page.publish = 1 then 'Publish'
         ELSE 'Draft' END                                                                          campaign_status,
       page_content.description                                                                    invitation_message,
       page_content.description                                                                    invitation_message_en,
       page_content.body                                                                           descriptions,
       page_content.body                                                                           descriptions_en,
       CONCAT('(SELECT id FROM categories WHERE category_name = "', page_category.category, '") ') category_id,
       page_image.file_name                                                                        campaign_image,
       date_created                                                                                created_at
       date_created                                                                                updated_at
FROM page
       LEFT JOIN page_content ON page.page_id = page_content.page_id
       LEFT JOIN page_category_join ON page.page_id = page_category_join.page_id
       LEFT JOIN page_category ON page_category_join.page_category_id = page_category.page_category_id
       LEFT JOIN page_image ON page.page_id = page_image.page_id
       LEFT JOIN page_menu ON page.page_id = page_menu.page_id
WHERE page_content.donate_need <> 0
  AND page.is_menu = FALSE
ORDER BY created_at;


-- Migrate Blog
SELECT 'insanbumimandiri.org' member_id,
       1                      creator_id,
       page_content.title     news_title,
       page_content.title     news_title_en,
       page_menu.guid         news_slug,
       page_menu.guid         news_slug_en,
       page_content.body      news_content,
       page_content.body      news_content_en,
       11                     category_id,
       page_image.file_name   news_image,
       date_created           news_date,
       date_created           created_at,
       date_created           updated_at,
       date_created           sent_at
FROM page
       LEFT JOIN page_content ON page.page_id = page_content.page_id
       LEFT JOIN page_category_join ON page.page_id = page_category_join.page_id
       LEFT JOIN page_category ON page_category_join.page_category_id = page_category.page_category_id
       LEFT JOIN page_image ON page.page_id = page_image.page_id
       LEFT JOIN page_menu ON page.page_id = page_menu.page_id
WHERE page_content.donate_need = 0
  AND page.is_menu = false
  AND page_content.title <> 'test'
ORDER BY created_at;

-- Migrate Pages
SELECT 'insanbumimandiri.org' member_id,
       1                      creator_id,
       page_content.title     page_title,
       page_content.title     page_title_en,
       page_menu.guid         page_slug,
       page_menu.guid         page_slug_en,
       page_content.body      page_content,
       page_content.body      page_content_en,
       date_created           created_at
       date_created           updated_at
FROM page
       LEFT JOIN page_content ON page.page_id = page_content.page_id
       LEFT JOIN page_category_join ON page.page_id = page_category_join.page_id
       LEFT JOIN page_category ON page_category_join.page_category_id = page_category.page_category_id
       LEFT JOIN page_image ON page.page_id = page_image.page_id
       LEFT JOIN page_menu ON page.page_id = page_menu.page_id
WHERE page_content.donate_need = 0
  AND page.is_menu = true
  AND page_menu.guid <> 'home' AND page_content.body <> ''
ORDER BY created_at;




