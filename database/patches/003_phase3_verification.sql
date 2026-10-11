USE vsms_db;

-- Phase 3 verification queries. Run after 002_phase3_proposal_objects.sql.

SHOW FULL TABLES WHERE Table_type = 'VIEW';

SELECT ROUTINE_NAME, ROUTINE_TYPE
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE()
  AND ROUTINE_NAME IN (
      'Sales_Summary',
      'GetTotalSalesByEmployee',
      'AddSale',
      'sp_RecordSale',
      'sp_GetMonthlySalesReport',
      'fn_CalculateDiscount'
  )
ORDER BY ROUTINE_TYPE, ROUTINE_NAME;

SELECT * FROM Sales_Summary ORDER BY sale_id DESC LIMIT 5;

SELECT
    e.employee_id,
    e.name AS employee_name,
    GetTotalSalesByEmployee(e.employee_id) AS total_sales
FROM Employee e
ORDER BY total_sales DESC, e.name ASC;

EXPLAIN
SELECT s.sale_id, c.name, v.model, s.sale_price
FROM Sales s
INNER JOIN Customer c ON s.customer_id = c.customer_id
INNER JOIN Vehicle v ON s.vehicle_id = v.vehicle_id;
