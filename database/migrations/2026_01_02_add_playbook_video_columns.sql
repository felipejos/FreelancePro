ALTER TABLE company_playbooks
ADD COLUMN video_mode VARCHAR(20) NULL AFTER payment_id,
ADD COLUMN video_url VARCHAR(512) NULL AFTER video_mode,
ADD COLUMN video_original_name VARCHAR(255) NULL AFTER video_url;
