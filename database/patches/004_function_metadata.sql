USE vsms_db;

-- fn_CalculateDiscount reads the current date, so it must not be declared
-- DETERMINISTIC in MySQL metadata.
DROP FUNCTION IF EXISTS fn_CalculateDiscount;

DELIMITER $$

CREATE FUNCTION fn_CalculateDiscount(
    p_sale_price DECIMAL(10,2),
    p_vehicle_year INT
)
RETURNS DECIMAL(10,2)
NOT DETERMINISTIC
NO SQL
BEGIN
    DECLARE v_discount DECIMAL(10,2);
    DECLARE v_age INT;

    SET v_age = YEAR(CURDATE()) - p_vehicle_year;

    IF v_age >= 3 THEN
        SET v_discount = p_sale_price * 0.05;
    ELSE
        SET v_discount = p_sale_price * 0.02;
    END IF;

    RETURN v_discount;
END$$

DELIMITER ;
