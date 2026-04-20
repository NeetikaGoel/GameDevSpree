
-- NORMALIZED SCHEMA OF DATA FOR GAME SHOP

-- HAVE TO ADD SEPARATE TABLES FOR CUSTOMERS, THEN PRODUCTS, THEN ORDERS, THEN ABOUT ORDER DESCRIPTION TOO LIKE WHAT ITEMS HAVE BEEN ADDED IN A PARTICULAR ORDER

-- ADD RANDOM DATA TOO TO FILL UP FRONTEND


CREATE DATABASE game_inventory_system;
USE game_inventory_system;

CREATE TABLE customers 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    rarity VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id)
);


-- ANOTHER TABLE WE NEED FOR WHAT ALL PRODUCTS ARE ORDERED IN 1 ORDER SO KEEPING TRACK OF THAT

CREATE TABLE order_items 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    item_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
);

-- WILL CONNECT ORDER WITH VALID CUSTOMER

INSERT INTO products (id, name, category, price, rarity) VALUES
(1, 'Flame Sword', 'weapons', 120.00, 'Legendary'),
(2, 'Shadow Armor', 'armors', 95.00, 'Epic'),
(3, 'Guardian Shield', 'shields', 70.00, 'Rare'),
(4, 'Health Potion', 'potions', 20.00, 'Consumable');