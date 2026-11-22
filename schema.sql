CREATE DATABASE bookstore;
USE bookstore;

-- USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    address VARCHAR(255),
    role ENUM('ADMIN','CUSTOMER')
);

-- PUBLISHERS
CREATE TABLE publishers (
    publisher_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    address VARCHAR(255),
    phone VARCHAR(20)
);

-- BOOKS
CREATE TABLE books (
    isbn VARCHAR(20) PRIMARY KEY,
    title VARCHAR(200),
    publisher_id INT,
    publication_year INT,
    price DECIMAL(8,2),
    category ENUM('Science','Art','Religion','History','Geography'),
    stock INT,
    threshold INT,
    FOREIGN KEY (publisher_id) REFERENCES publishers(publisher_id)
);

-- PUBLISHER ORDERS (REPLENISHMENT)
CREATE TABLE publisher_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20),
    quantity INT,
    status ENUM('PENDING','CONFIRMED'),
    order_date DATE,
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);

-- CUSTOMER ORDERS
CREATE TABLE customer_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date DATE,
    total_price DECIMAL(10,2),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ORDER ITEMS
CREATE TABLE order_items (
    order_id INT,
    isbn VARCHAR(20),
    quantity INT,
    price DECIMAL(8,2),
    PRIMARY KEY (order_id, isbn),
    FOREIGN KEY (order_id) REFERENCES customer_orders(order_id),
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);
-- AUTHORS
CREATE TABLE authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

-- BOOK–AUTHOR (many-to-many)
CREATE TABLE book_authors (
    isbn VARCHAR(20),
    author_id INT,
    PRIMARY KEY (isbn, author_id),
    FOREIGN KEY (isbn) REFERENCES books(isbn),
    FOREIGN KEY (author_id) REFERENCES authors(author_id)
);

------------------------------------------------
-- TRIGGERS
------------------------------------------------

-- 1. Prevent negative stock
DELIMITER //

CREATE TRIGGER prevent_negative_stock
BEFORE UPDATE ON books
FOR EACH ROW
BEGIN
    IF NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock cannot be negative';
    END IF;
END//

DELIMITER ;

-- 2. Auto reorder when stock drops below threshold
DELIMITER //
CREATE TRIGGER auto_reorder_books
AFTER UPDATE ON books
FOR EACH ROW
BEGIN
    IF OLD.stock >= OLD.threshold AND NEW.stock < NEW.threshold THEN
        INSERT INTO publisher_orders (isbn, quantity, status, order_date)
        VALUES (NEW.isbn, 10, 'PENDING', CURDATE());
    END IF;
END//
DELIMITER ;

-- 3. Confirm publisher order add stock
DELIMITER //
CREATE TRIGGER confirm_order_add_stock
AFTER UPDATE ON publisher_orders
FOR EACH ROW
BEGIN
    IF OLD.status = 'PENDING' AND NEW.status = 'CONFIRMED' THEN
        UPDATE books
        SET stock = stock + NEW.quantity
        WHERE isbn = NEW.isbn;
    END IF;
END//
DELIMITER ;

-- 4. Prevent overselling
DELIMITER //

CREATE TRIGGER prevent_overselling
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE current_stock INT;

    SELECT stock INTO current_stock
    FROM books
    WHERE isbn = NEW.isbn;

    IF current_stock < NEW.quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Not enough stock available';
    END IF;
END//

DELIMITER ;

-- 5. Deduct stock after purchase
DELIMITER //

CREATE TRIGGER deduct_stock_after_purchase
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE books
    SET stock = stock - NEW.quantity
    WHERE isbn = NEW.isbn;
END//

DELIMITER ;
