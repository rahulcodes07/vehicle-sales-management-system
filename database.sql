-- =========================================================
-- Vehicle Sales Management System (VSMS) - ADBMS Database Schema
-- Database: vsms_db
-- =========================================================

CREATE DATABASE IF NOT EXISTS vsms_db;
USE vsms_db;

-- 1. DROP EXISTING OBJECTS (FOR CLEAN RESET)
DROP TABLE IF EXISTS Insurance;
DROP TABLE IF EXISTS Sales_Audit_Log;
DROP TABLE IF EXISTS Sales;
DROP TABLE IF EXISTS Vehicle;
DROP TABLE IF EXISTS Employee;
DROP TABLE IF EXISTS Customer;
DROP TABLE IF EXISTS Branch;

DROP VIEW IF EXISTS view_available_vehicles;
DROP VIEW IF EXISTS view_sales_details;
DROP VIEW IF EXISTS view_employee_performance;
DROP VIEW IF EXISTS view_inventory_summary;

DROP PROCEDURE IF EXISTS sp_RecordSale;
DROP PROCEDURE IF EXISTS sp_GetMonthlySalesReport;
DROP FUNCTION IF EXISTS fn_CalculateDiscount;

-- =========================================================
-- 2. TABLE DEFINITIONS & CONSTRAINTS
-- =========================================================

-- Table: Branch (Stores dealership showroom/branch locations)
CREATE TABLE Branch (
    branch_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: Employee (Showroom staff, sales agents, managers)
CREATE TABLE Employee (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Sales Executive',
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE,
    salary DECIMAL(10,2) NOT NULL CHECK (salary > 0),
    hire_date DATE NOT NULL,
    FOREIGN KEY (branch_id) REFERENCES Branch(branch_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table: Customer (Buyers and leads)
CREATE TABLE Customer (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: Vehicle (Inventory stock)
CREATE TABLE Vehicle (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    manufacturing_yr INT NOT NULL CHECK (manufacturing_yr >= 1990 AND manufacturing_yr <= 2030),
    type ENUM('Car', 'Bike', 'SUV', 'Truck') NOT NULL DEFAULT 'Car',
    fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'CNG', 'Hybrid') NOT NULL DEFAULT 'Petrol',
    color VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price > 0),
    status ENUM('Available', 'Sold') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: Sales (Transaction records of vehicle purchases)
CREATE TABLE Sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL UNIQUE, -- 1-to-1: each physical vehicle is sold once per record
    customer_id INT NOT NULL,
    employee_id INT NOT NULL,
    sale_date DATE NOT NULL,
    sale_price DECIMAL(10,2) NOT NULL CHECK (sale_price > 0),
    payment_mode ENUM('Cash', 'Loan', 'Card', 'UPI', 'Net Banking') NOT NULL DEFAULT 'Cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES Vehicle(vehicle_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES Employee(employee_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table: Insurance (Insurance policies tied to sold vehicles and customers)
CREATE TABLE Insurance (
    insurance_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    customer_id INT NOT NULL,
    provider VARCHAR(100) NOT NULL,
    policy_no VARCHAR(100) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    premium DECIMAL(10,2) NOT NULL CHECK (premium > 0),
    FOREIGN KEY (vehicle_id) REFERENCES Vehicle(vehicle_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_dates CHECK (end_date > start_date)
) ENGINE=InnoDB;

-- Table: Sales_Audit_Log (ADBMS Audit table populated via Triggers)
CREATE TABLE Sales_Audit_Log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    vehicle_id INT,
    action_type ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    action_description TEXT NOT NULL,
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 3. INDEXES FOR PERFORMANCE OPTIMIZATION (ADBMS Topic)
-- =========================================================
CREATE INDEX idx_vehicle_status ON Vehicle(status);
CREATE INDEX idx_sales_date ON Sales(sale_date);
CREATE INDEX idx_customer_phone ON Customer(phone);
CREATE INDEX idx_employee_branch ON Employee(branch_id);

-- =========================================================
-- 4. DATABASE VIEWS (ADBMS Topic)
-- =========================================================

-- View 1: Available Vehicles for Customer Showroom
CREATE VIEW view_available_vehicles AS
SELECT 
    vehicle_id, 
    model, 
    manufacturing_yr, 
    type, 
    fuel_type, 
    color, 
    price 
FROM Vehicle 
WHERE status = 'Available';

-- View 2: Comprehensive Sales Details with multi-table joins
CREATE VIEW view_sales_details AS
SELECT 
    s.sale_id,
    s.sale_date,
    s.sale_price,
    s.payment_mode,
    v.vehicle_id,
    v.model AS vehicle_model,
    v.type AS vehicle_type,
    v.color AS vehicle_color,
    c.customer_id,
    c.name AS customer_name,
    c.phone AS customer_phone,
    c.email AS customer_email,
    e.employee_id,
    e.name AS employee_name,
    b.location AS branch_location
FROM Sales s
INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
INNER JOIN Customer c ON s.customer_id = c.customer_id
INNER JOIN Employee e ON s.employee_id = e.employee_id
INNER JOIN Branch b ON e.branch_id = b.branch_id;

-- View 3: Employee Performance & Total Revenue Summary
CREATE VIEW view_employee_performance AS
SELECT 
    e.employee_id,
    e.name AS employee_name,
    e.role,
    b.name AS branch_name,
    COUNT(s.sale_id) AS total_vehicles_sold,
    COALESCE(SUM(s.sale_price), 0) AS total_revenue_generated
FROM Employee e
INNER JOIN Branch b ON e.branch_id = b.branch_id
LEFT JOIN Sales s ON e.employee_id = s.employee_id
GROUP BY e.employee_id, e.name, e.role, b.name;

-- View 4: Inventory Status Summary by Vehicle Type
CREATE VIEW view_inventory_summary AS
SELECT 
    type,
    COUNT(*) AS total_count,
    SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) AS available_count,
    SUM(CASE WHEN status = 'Sold' THEN 1 ELSE 0 END) AS sold_count,
    AVG(price) AS average_price
FROM Vehicle
GROUP BY type;

-- =========================================================
-- 5. TRIGGERS (ADBMS Topic)
-- =========================================================

DELIMITER $$

-- Trigger 1: After a sale is inserted, automatically update Vehicle status and log to Audit Log
CREATE TRIGGER trg_after_sale_insert
AFTER INSERT ON Sales
FOR EACH ROW
BEGIN
    -- Automatically set vehicle status to Sold
    UPDATE Vehicle 
    SET status = 'Sold' 
    WHERE vehicle_id = NEW.vehicle_id;

    -- Insert record into audit log
    INSERT INTO Sales_Audit_Log (sale_id, vehicle_id, action_type, action_description)
    VALUES (
        NEW.sale_id, 
        NEW.vehicle_id, 
        'INSERT', 
        CONCAT('Sale ID #', NEW.sale_id, ' recorded. Sold for ₹', NEW.sale_price, ' via ', NEW.payment_mode)
    );
END$$

-- Trigger 2: After a sale is deleted/cancelled, restore Vehicle status to Available and log action
CREATE TRIGGER trg_after_sale_delete
AFTER DELETE ON Sales
FOR EACH ROW
BEGIN
    -- Restore vehicle status to Available
    UPDATE Vehicle 
    SET status = 'Available' 
    WHERE vehicle_id = OLD.vehicle_id;

    -- Insert record into audit log
    INSERT INTO Sales_Audit_Log (sale_id, vehicle_id, action_type, action_description)
    VALUES (
        OLD.sale_id, 
        OLD.vehicle_id, 
        'DELETE', 
        CONCAT('Sale ID #', OLD.sale_id, ' was cancelled/deleted. Vehicle ID #', OLD.vehicle_id, ' restored to Available.')
    );
END$$

-- =========================================================
-- 6. STORED PROCEDURES & FUNCTIONS (ADBMS Topic)
-- =========================================================

-- Stored Procedure: Encapsulates Sale Transaction with ACID verification
CREATE PROCEDURE sp_RecordSale(
    IN p_vehicle_id INT,
    IN p_customer_id INT,
    IN p_employee_id INT,
    IN p_sale_price DECIMAL(10,2),
    IN p_payment_mode VARCHAR(50),
    OUT p_status_code INT,
    OUT p_status_message VARCHAR(255)
)
proc_label: BEGIN
    DECLARE v_vehicle_status VARCHAR(20);

    -- Error handler for unexpected SQL exceptions (Rollback)
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status_code = 500;
        SET p_status_message = 'Transaction Failed: Database error occurred.';
    END;

    -- 1. Check if vehicle exists and is available
    SELECT status INTO v_vehicle_status 
    FROM Vehicle 
    WHERE vehicle_id = p_vehicle_id;

    IF v_vehicle_status IS NULL THEN
        SET p_status_code = 404;
        SET p_status_message = 'Error: Vehicle does not exist.';
        LEAVE proc_label;
    END IF;

    IF v_vehicle_status <> 'Available' THEN
        SET p_status_code = 400;
        SET p_status_message = 'Error: This vehicle is already marked as Sold.';
        LEAVE proc_label;
    END IF;

    -- 2. Begin ACID Transaction
    START TRANSACTION;

    INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
    VALUES (p_vehicle_id, p_customer_id, p_employee_id, CURDATE(), p_sale_price, p_payment_mode);

    -- The trigger `trg_after_sale_insert` will execute automatically here to update vehicle status!
    
    COMMIT;
    SET p_status_code = 200;
    SET p_status_message = 'Success: Sale recorded successfully and vehicle marked as Sold!';
END$$

-- Stored Procedure: Monthly Sales Report Analytics
CREATE PROCEDURE sp_GetMonthlySalesReport(IN p_year INT)
BEGIN
    SELECT 
        MONTHNAME(sale_date) AS month_name,
        MONTH(sale_date) AS month_number,
        COUNT(sale_id) AS total_sales_count,
        SUM(sale_price) AS total_monthly_revenue,
        AVG(sale_price) AS average_sale_price
    FROM Sales
    WHERE YEAR(sale_date) = p_year
    GROUP BY MONTH(sale_date), MONTHNAME(sale_date)
    ORDER BY month_number ASC;
END$$

-- Stored Function: Calculates eligible festive discount based on manufacturing year
CREATE FUNCTION fn_CalculateDiscount(p_sale_price DECIMAL(10,2), p_vehicle_year INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_discount DECIMAL(10,2);
    DECLARE v_age INT;

    SET v_age = YEAR(CURDATE()) - p_vehicle_year;

    -- If car is older than 3 years, give 5% discount; otherwise 2%
    IF v_age >= 3 THEN
        SET v_discount = p_sale_price * 0.05;
    ELSE
        SET v_discount = p_sale_price * 0.02;
    END IF;

    RETURN v_discount;
END$$

DELIMITER ;

-- =========================================================
-- 7. SEED DATA (FOR TESTING AND DEMO)
-- =========================================================

-- Branches
INSERT INTO Branch (name, location, phone) VALUES
('Downtown Motors', 'MG Road, Bengaluru', '+91 9876543210'),
('Suburban Wheels', 'Whitefield, Bengaluru', '+91 9876543211'),
('Metro Auto Hub', 'Andheri West, Mumbai', '+91 9876543212');

-- Employees
INSERT INTO Employee (branch_id, name, role, phone, email, salary, hire_date) VALUES
(1, 'Rahul Sharma', 'Sales Manager', '9811122233', 'rahul.s@vsms.com', 65000.00, '2023-01-15'),
(1, 'Priya Verma', 'Senior Sales Executive', '9822233344', 'priya.v@vsms.com', 45000.00, '2023-04-10'),
(2, 'Amit Patel', 'Sales Executive', '9833344455', 'amit.p@vsms.com', 38000.00, '2023-08-01'),
(3, 'Sneha Joshi', 'Finance Specialist', '9844455566', 'sneha.j@vsms.com', 50000.00, '2022-11-20');

-- Customers
INSERT INTO Customer (name, phone, email, address) VALUES
('Rohan Kapoor', '9900112233', 'rohan.k@gmail.com', 'Flat 402, Sunshine Apts, Indiranagar, Bengaluru'),
('Ananya Gupta', '9911223344', 'ananya.g@gmail.com', '12/A Green Meadows, Koramangala, Bengaluru'),
('Vikram Malhotra', '9922334455', 'vikram.m@gmail.com', 'Villa 7, Palm Groves, Powai, Mumbai'),
('Pooja Nair', '9933445566', 'pooja.n@gmail.com', '54 Brigade Road, Bengaluru');

-- Vehicles
INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status) VALUES
('Honda City ZX', 2023, 'Car', 'Petrol', 'Platinum White', 1550000.00, 'Available'),
('Hyundai Creta SX', 2024, 'SUV', 'Diesel', 'Phantom Black', 1820000.00, 'Available'),
('Tata Nexon EV', 2023, 'SUV', 'Electric', 'Teal Blue', 1680000.00, 'Available'),
('Royal Enfield Hunter 350', 2023, 'Bike', 'Petrol', 'Dapper Ash', 175000.00, 'Available'),
('Toyota Fortuner 4x4', 2022, 'SUV', 'Diesel', 'Silver Metallic', 3800000.00, 'Available'),
('Maruti Swift ZXi', 2024, 'Car', 'CNG', 'Luster Red', 850000.00, 'Available');

-- Initial Sale
INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
VALUES (1, 1, 2, '2024-02-15', 1520000.00, 'Loan');

-- Insurance Policy for the sold car
INSERT INTO Insurance (vehicle_id, customer_id, provider, policy_no, start_date, end_date, premium)
VALUES (1, 1, 'HDFC ERGO General Insurance', 'POL-HDFC-2024-8891', '2024-02-15', '2025-02-14', 32500.00);
