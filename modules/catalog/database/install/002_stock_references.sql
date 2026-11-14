CREATE TABLE IF NOT EXISTS catalog_stock_references (
    id BIGINT NOT NULL AUTO_INCREMENT,
    product_sku VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT chk_catalog_stock_sku CHECK (CHAR_LENGTH(product_sku) = 10 AND BINARY product_sku REGEXP '^[A-Z0-9-]{10}$'),
    CONSTRAINT fk_catalog_stock_product FOREIGN KEY (product_sku) REFERENCES catalog_products (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin
