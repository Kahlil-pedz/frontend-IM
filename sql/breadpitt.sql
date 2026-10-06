DROP DATABASE IF EXISTS breadpitt_db;
CREATE DATABASE breadpitt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE breadpitt_db;

-- ==================== USERS ====================
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url    VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==================== CATEGORIES ====================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ==================== PRODUCTS ====================
CREATE TABLE products (
    product_id     INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100)  NOT NULL,
    category_id    INT           NOT NULL,
    price          DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) NOT NULL,
    specs          VARCHAR(150)  DEFAULT NULL,
    image_url      VARCHAR(255)  DEFAULT NULL,
    stock_qty      INT           NOT NULL DEFAULT 0,
    is_active      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id)
        REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ==================== VOUCHERS ====================
CREATE TABLE vouchers (
    voucher_id    INT AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(30)  NOT NULL UNIQUE,
    discount_rate DECIMAL(5,4) NOT NULL,
    max_uses      INT DEFAULT 100,
    times_used    INT DEFAULT 0,
    is_active     TINYINT(1) DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==================== ORDERS ====================
CREATE TABLE orders (
    order_id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    order_code      VARCHAR(30) NOT NULL UNIQUE,
    voucher_id      INT DEFAULT NULL,
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    status          ENUM('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user    FOREIGN KEY (user_id)    REFERENCES users(user_id)    ON DELETE RESTRICT,
    CONSTRAINT fk_order_voucher FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==================== ORDER ITEMS ====================
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    product_id    INT NOT NULL,
    product_name  VARCHAR(100)  NOT NULL,
    unit_price    DECIMAL(10,2) NOT NULL,
    quantity      INT           NOT NULL,
    line_total    DECIMAL(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_item_order   FOREIGN KEY (order_id)   REFERENCES orders(order_id)   ON DELETE CASCADE,
    CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ==================== AUDIT LOG ====================
CREATE TABLE order_logs (
    log_id     INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    action     VARCHAR(50) NOT NULL,
    old_status VARCHAR(20) DEFAULT NULL,
    new_status VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==================== SEED ====================
INSERT INTO users (username, full_name, email, password_hash, avatar_url) VALUES
('breadpitt_user', 'Juan Dela Cruz', 'juan@breadpitt.test',
 '$2y$10$e0NRzQ0m6R1vYyFfXk8hIe1uXw1c1u9t2d7qFqk5nJpXWzGZ4nq2',
 'https://tr.rbxcdn.com/180DAY-99bc9c6c9cb64b72d7039a52c27c6e90/420/420/FaceAccessory/Webp/noFilter');

INSERT INTO categories (name) VALUES ('Coffee'), ('Bakery'), ('Desserts');

INSERT INTO products (name, category_id, price, original_price, specs, image_url, stock_qty) VALUES
('Spanish Latte',        1, 150.00, 165.00, '16 oz • Espresso & Sweet Milk',       'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80', 50),
('Matcha Latte',         1, 165.00, 180.00, '16oz • Creamy Matcha',                'https://i.pinimg.com/1200x/b8/15/91/b8159103ebf39a3a64160f29850974c9.jpg', 40),
('Mocha Latte',          1, 145.00, 170.00, '16oz • Creamy & Chocolatey',          'https://i.pinimg.com/1200x/f5/fb/cb/f5fbcb920fa1f955dd7650dfe803b56f.jpg', 45),
('Croissant',            2,  95.00, 110.00, 'Freshly Baked • Flaky Layered',        'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=600&q=80', 30),
('French Apple Tart',    2, 110.00, 130.00, 'Buttery Crust • Sweet & Fruity',       'https://i.pinimg.com/1200x/cd/88/d5/cd88d57687efd76aee5a574d125b0078.jpg', 25),
('Blueberry Cheesecake', 3, 120.00, 135.00, '1 Slice • Blueberry Compote',          'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=600&q=80', 20),
('Moist Chocolate Cake', 3, 135.00, 150.00, 'Rich Chocolate • Moist & Velvety',     'https://i.pinimg.com/736x/62/43/f5/6243f515eae4f41119461e537dea3d2d.jpg', 20),
('Red Velvet Cake',      3, 150.00, 170.00, 'Velvety Soft • Creamy & Rich',         'https://i.pinimg.com/736x/d2/77/6d/d2776db48c372fdd2c6f1a5a8bf5f43d.jpg', 20),
('Cinnamon Roll',        2, 140.00, 160.00, 'Freshly Baked • Soft & Cinnamon Sweet', 'https://i.pinimg.com/736x/2d/21/01/2d2101c743652bc122b7e0863ab168e2.jpg', 35);

INSERT INTO vouchers (code, discount_rate, max_uses) VALUES
('BREAD10', 0.1000, 500),
('SAVE10',  0.1000, 500),
('BREAD20', 0.2000, 200);

-- ==================== TRIGGERS ====================
DELIMITER $$

-- Auto-compute line_total on insert
CREATE TRIGGER trg_order_items_before_insert
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    IF NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be > 0.';
    END IF;
    SET NEW.line_total = NEW.unit_price * NEW.quantity;
END$$

-- Recalc parent order totals after insert
CREATE TRIGGER trg_order_items_after_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_rate     DECIMAL(5,4) DEFAULT 0;

    SELECT IFNULL(SUM(line_total),0) INTO v_subtotal
    FROM order_items WHERE order_id = NEW.order_id;

    SELECT IFNULL(v.discount_rate,0) INTO v_rate
    FROM orders o LEFT JOIN vouchers v ON v.voucher_id = o.voucher_id
    WHERE o.order_id = NEW.order_id;

    UPDATE orders
    SET subtotal        = v_subtotal,
        discount_amount = v_subtotal * v_rate,
        total_amount    = v_subtotal - (v_subtotal * v_rate)
    WHERE order_id = NEW.order_id;
END$$

-- Recalc after delete
CREATE TRIGGER trg_order_items_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_rate     DECIMAL(5,4) DEFAULT 0;

    SELECT IFNULL(SUM(line_total),0) INTO v_subtotal
    FROM order_items WHERE order_id = OLD.order_id;

    SELECT IFNULL(v.discount_rate,0) INTO v_rate
    FROM orders o LEFT JOIN vouchers v ON v.voucher_id = o.voucher_id
    WHERE o.order_id = OLD.order_id;

    UPDATE orders
    SET subtotal        = v_subtotal,
        discount_amount = v_subtotal * v_rate,
        total_amount    = v_subtotal - (v_subtotal * v_rate)
    WHERE order_id = OLD.order_id;
END$$

-- Auto-generate order_code if blank
CREATE TRIGGER trg_orders_before_insert
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    IF NEW.order_code IS NULL OR NEW.order_code = '' THEN
        SET NEW.order_code = CONCAT('BP-', DATE_FORMAT(NOW(),'%Y%m%d'), '-',
                                    LPAD(FLOOR(RAND()*99999),5,'0'));
    END IF;
END$$

-- Log status change + bump voucher counter on "paid"
CREATE TRIGGER trg_orders_after_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO order_logs (order_id, action, old_status, new_status)
        VALUES (NEW.order_id, 'STATUS_CHANGE', OLD.status, NEW.status);
    END IF;

    IF NEW.status = 'paid' AND OLD.status <> 'paid' AND NEW.voucher_id IS NOT NULL THEN
        UPDATE vouchers SET times_used = times_used + 1
        WHERE voucher_id = NEW.voucher_id;
    END IF;
END$$

DELIMITER ;