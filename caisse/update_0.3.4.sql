ALTER TABLE @PREFIX_methods ADD COLUMN enabled INTEGER NOT NULL DEFAULT 1;

UPDATE @PREFIX_methods SET enabled = 0 WHERE id = 3;