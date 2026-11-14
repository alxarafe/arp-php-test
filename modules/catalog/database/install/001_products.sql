CREATE TABLE IF NOT EXISTS catalog_products (
    sku VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    name VARCHAR(150) NOT NULL,
    PRIMARY KEY (sku),
    CONSTRAINT chk_catalog_products_sku CHECK (CHAR_LENGTH(sku) = 10 AND BINARY sku REGEXP '^[A-Z0-9-]{10}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin
