
-- 1. USERS TABLE (Holds Admin, Greenhouse Owners, and Customers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner', 'customer') NOT NULL DEFAULT 'customer',
    city VARCHAR(50) NOT NULL,
    address TEXT,
    gap_certificate VARCHAR(255) DEFAULT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. STORES TABLE (Linked to Greenhouse Owners)
CREATE TABLE stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    store_name VARCHAR(100) NOT NULL,
    description TEXT,
    city VARCHAR(50) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. PRODUCTS TABLE (Vegetables, Fruits, Plants)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category ENUM('vegetable', 'fruit', 'plant') NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
);

-- 4. PLANT RECOMMENDATION DATASET (For the recommendation system)
CREATE TABLE plant_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plant_name VARCHAR(100) NOT NULL,
    min_rainfall_mm DECIMAL(5,2) NOT NULL,
    max_rainfall_mm DECIMAL(5,2) NOT NULL,
    min_humidity_pct DECIMAL(5,2) NOT NULL,
    max_humidity_pct DECIMAL(5,2) NOT NULL,
    min_temp_c DECIMAL(5,2) NOT NULL,
    max_temp_c DECIMAL(5,2) NOT NULL,
    description TEXT
);

-- 5. CART TABLE (Holds items added by logged-in customers)
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 6. ORDERS TABLE (Stores customer's checkout details)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_reference VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    store_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'processing', 'delivered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
);

-- 7. ORDER ITEMS TABLE (Specific products in each order)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL, 
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 8. REVIEWS & RATINGS TABLE (For stores)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_hidden BOOLEAN DEFAULT FALSE, -- Admin can set this to TRUE to hide bad reviews
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. NOTIFICATIONS TABLE (In-app popup/messages)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 10. ADMIN WARNINGS TABLE (Admin warns Greenhouse Owner about expiry/reviews)
CREATE TABLE admin_warnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    owner_id INT NOT NULL,
    warning_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 1. Insert an Admin
INSERT INTO users (full_name, email, password, role, city, address) 
VALUES ('Super Admin', 'admin@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'New York', 'Admin HQ');

-- Note: The password for admin@crops.com is "password" (hashed using Bcrypt).

-- 2. Insert 2 Greenhouse Owners (with dummy GAP certificates)
INSERT INTO users (full_name, email, password, role, city, address, gap_certificate) VALUES 
('Green Thumb Owner', 'owner1@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Los Angeles', '123 Farm Lane, LA', 'gap_cert_owner1.pdf'),
('Fresh Harvest Owner', 'owner2@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'San Francisco', '456 Green Street, SF', 'gap_cert_owner2.pdf');

-- 3. Insert 2 Customers
INSERT INTO users (full_name, email, password, role, city, address) VALUES 
('John Buyer', 'customer1@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Los Angeles', '789 Home Ave, LA'),
('Jane Shopper', 'customer2@crops.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'San Diego', '101 Buyer Street, SD');

-- 4. Insert 2 Stores (belonging to the 2 owners above - IDs 2 and 3)
INSERT INTO stores (owner_id, store_name, description, city) VALUES 
(2, 'Green Thumb Nursery', 'Specializing in organic vegetables and rare plants.', 'Los Angeles'),
(3, 'Fresh Harvest Greenhouse', 'Locally grown fruits and seasonal veggies.', 'San Francisco');

-- 5. Insert 4 Products (2 for store ID 1, 2 for store ID 2)
INSERT INTO products (store_id, name, description, price, category, quantity, image) VALUES 
(1, 'Organic Tomato', 'Juicy, vine-ripened organic tomatoes.', 4.50, 'vegetable', 100, 'tomato.jpg'),
(1, 'Lavender Plant', 'Beautiful purple lavender, great for gardens.', 12.00, 'plant', 50, 'lavender.jpg'),
(2, 'Strawberries', 'Sweet, fresh California strawberries.', 6.00, 'fruit', 80, 'strawberries.jpg'),
(2, 'Basil Plant', 'Fresh aromatic basil for cooking.', 5.50, 'plant', 60, 'basil.jpg');


