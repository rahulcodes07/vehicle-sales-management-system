USE vsms_db;

-- =========================================================================
-- Phase 3 Proposal Objects (Matching Proposal PDF Page 4)
-- 1. View: Sales_Summary
-- 2. Function: GetTotalSalesByEmployee(employee_id)
-- 3. Procedure: AddSale(vehicle_id, customer_id, employee_id, sale_price, payment_mode)
-- =========================================================================

-- 1. View: Sales_Summary (Proposal Phase 3 Item 3)
CREATE OR REPLACE VIEW Sales_Summary AS
SELECT 
    s.sale_id,
    c.name AS customer_name,
    v.model AS vehicle_model,
    v.color AS vehicle_color,
    s.sale_date,
    s.sale_price,
    s.payment_mode,
    e.name AS employee_name,
    b.location AS branch_location
FROM Sales s
INNER JOIN Customer c ON s.customer_id = c.customer_id
INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
INNER JOIN Employee e ON s.employee_id = e.employee_id
INNER JOIN Branch b ON e.branch_id = b.branch_id;

-- 2. Stored Function: GetTotalSalesByEmployee (Proposal Phase 3 Item 5)
DROP FUNCTION IF EXISTS GetTotalSalesByEmployee;

DELIMITER $$

CREATE FUNCTION GetTotalSalesByEmployee(p_employee_id INT)
RETURNS INT
READS SQL DATA
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO v_count
    FROM Sales
    WHERE employee_id = p_employee_id;
    
    RETURN v_count;
END$$

-- 3. Stored Procedure: AddSale (Proposal Phase 3 Item 6)
DROP PROCEDURE IF EXISTS AddSale$$

CREATE PROCEDURE AddSale(
    IN p_vehicle_id INT,
    IN p_customer_id INT,
    IN p_employee_id INT,
    IN p_sale_price DECIMAL(10,2),
    IN p_payment_mode VARCHAR(50)
)
BEGIN
    DECLARE v_code INT;
    DECLARE v_msg VARCHAR(255);
    
    CALL sp_RecordSale(p_vehicle_id, p_customer_id, p_employee_id, p_sale_price, p_payment_mode, v_code, v_msg);
    
    SELECT v_code AS status_code, v_msg AS status_message;
END$$

DELIMITER ;
