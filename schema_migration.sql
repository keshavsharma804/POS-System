-- Check sale_items schema
DESCRIBE sale_items;

-- Drop foreign key constraint on client_id
SET @constraint_name = (
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'pos_demo'
      AND TABLE_NAME = 'sale_items'
      AND COLUMN_NAME = 'client_id'
);
SET @drop_fk_sql = IF(@constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE sale_items DROP FOREIGN KEY ', @constraint_name),
    'SELECT "No foreign key on client_id"');
PREPARE stmt FROM @drop_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop client_id column if exists
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'pos_demo'
      AND TABLE_NAME = 'sale_items'
      AND COLUMN_NAME = 'client_id'
);
SET @drop_column_sql = IF(@column_exists,
    'ALTER TABLE sale_items DROP COLUMN client_id',
    'SELECT "client_id column already dropped"');
PREPARE stmt FROM @drop_column_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create sales_header
CREATE TABLE IF NOT EXISTS sales_header (
    id INT(11) NOT NULL AUTO_INCREMENT,
    sale_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    user_id INT(11) NOT NULL,
    customer_id INT(11),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Add columns to sale_items
ALTER TABLE sale_items
    ADD COLUMN IF NOT EXISTS sale_id INT(11),
    ADD COLUMN IF NOT EXISTS price DECIMAL(10,2);

-- Add foreign key for sale_id
SET @fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'pos_demo'
      AND TABLE_NAME = 'sale_items'
      AND CONSTRAINT_NAME = 'fk_sale_items_sale'
);
SET @add_fk_sql = IF(@fk_exists = 0,
    'ALTER TABLE sale_items ADD CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales_header(id)',
    'SELECT "Foreign key fk_sale_items_sale already exists"');
PREPARE stmt FROM @add_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Migrate data if client_id existed previously (if data exists)
SET @has_data = (
    SELECT COUNT(*)
    FROM sale_items
    WHERE sale_id IS NULL
);
SET @migrate_sql = IF(@has_data > 0,
    'INSERT INTO sales_header (sale_date, total_amount, user_id)
     SELECT si.sale_date, SUM(si.quantity * p.price), 1
     FROM sale_items si
     JOIN products p ON si.product_id = p.id
     WHERE si.sale_id IS NULL
     GROUP BY si.sale_date;
     UPDATE sale_items si
     JOIN products p ON si.product_id = p.id
     SET si.price = p.price,
         si.sale_id = (SELECT id FROM sales_header sh WHERE sh.sale_date = si.sale_date LIMIT 1)
     WHERE si.sale_id IS NULL;',
    'SELECT "No data to migrate"');
PREPARE stmt FROM @migrate_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;