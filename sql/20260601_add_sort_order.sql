ALTER TABLE live_schedules
ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER id,
ADD KEY idx_deleted_sort_order (deleted_at, sort_order, id);

SET @live_sort_order := 0;

UPDATE live_schedules
SET sort_order = (@live_sort_order := @live_sort_order + 1)
WHERE deleted_at IS NULL
ORDER BY id DESC;