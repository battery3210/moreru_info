SET @live_sort_order := 0;

UPDATE live_schedules
SET sort_order = (@live_sort_order := @live_sort_order + 1)
WHERE deleted_at IS NULL
ORDER BY sort_order DESC, id ASC;