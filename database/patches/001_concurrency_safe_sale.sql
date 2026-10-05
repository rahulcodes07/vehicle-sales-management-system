USE vsms_db;

DROP PROCEDURE IF EXISTS sp_RecordSale;

DELIMITER $$

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
    DECLARE v_vehicle_status VARCHAR(20) DEFAULT NULL;
    DECLARE v_vehicle_exists INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status_code = 500;
        SET p_status_message = 'Transaction failed and was rolled back by MySQL.';
    END;

    START TRANSACTION;

    SELECT COUNT(*), MAX(status)
      INTO v_vehicle_exists, v_vehicle_status
      FROM Vehicle
     WHERE vehicle_id = p_vehicle_id
     FOR UPDATE;

    IF v_vehicle_exists = 0 THEN
        ROLLBACK;
        SET p_status_code = 404;
        SET p_status_message = 'Vehicle does not exist.';
        LEAVE proc_label;
    END IF;

    IF v_vehicle_status <> 'Available' THEN
        ROLLBACK;
        SET p_status_code = 409;
        SET p_status_message = 'Vehicle is already marked as Sold.';
        LEAVE proc_label;
    END IF;

    INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
    VALUES (p_vehicle_id, p_customer_id, p_employee_id, CURDATE(), p_sale_price, p_payment_mode);

    COMMIT;
    SET p_status_code = 200;
    SET p_status_message = 'Sale committed successfully. Inventory and audit triggers completed.';
END$$

DELIMITER ;
