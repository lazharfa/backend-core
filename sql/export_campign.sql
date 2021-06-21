SELECT 1                                            member_id,
       1                                            user_id,
       page_content.title                           campaign_title,
       page_content.title                           campaign_title_en,
       LOWER(REPLACE(page_content.title, ' ', '-')) campaign_slug,
       LOWER(REPLACE(page_content.title, ' ', '-')) campaign_slug_en,
       page_content.end_donate                      expiration_date,
       page_content.body                            descriptions,
       page_content.body                            descriptions_en,
       page_content.description                     invitation_message,
       page_content.description                     invitation_message_en,
       'insanbumimandiri'                           domain_name
    #        page_category.category
FROM page
       LEFT JOIN page_content ON page.page_id = page_content.page_id
       LEFT JOIN page_category_join ON page.page_id = page_category_join.page_id
       LEFT JOIN page_category ON page_category_join.page_category_id = page_category.page_category_id
WHERE page_category.category IS NOT NULL