USE vsms_db;

-- Idempotent Indian dealership demo records. Existing rows are never removed.

INSERT INTO Branch (name, location, phone)
SELECT 'Koramangala Auto Gallery', '80 Feet Road, Bengaluru', '+91 9876543220'
WHERE NOT EXISTS (SELECT 1 FROM Branch WHERE name = 'Koramangala Auto Gallery');

INSERT INTO Branch (name, location, phone)
SELECT 'Powai Mobility Studio', 'JVLR, Mumbai', '+91 9876543221'
WHERE NOT EXISTS (SELECT 1 FROM Branch WHERE name = 'Powai Mobility Studio');

INSERT INTO Branch (name, location, phone)
SELECT 'Salt Lake Motors', 'Sector V, Kolkata', '+91 9876543222'
WHERE NOT EXISTS (SELECT 1 FROM Branch WHERE name = 'Salt Lake Motors');

INSERT INTO Employee (branch_id, name, role, phone, email, salary, hire_date)
SELECT b.branch_id, 'Kavya Reddy', 'Senior Sales Executive', '9850011223', 'kavya.r@vsms.com', 47000.00, '2024-02-12'
FROM Branch b WHERE b.name = 'Koramangala Auto Gallery'
  AND NOT EXISTS (SELECT 1 FROM Employee WHERE email = 'kavya.r@vsms.com');

INSERT INTO Employee (branch_id, name, role, phone, email, salary, hire_date)
SELECT b.branch_id, 'Arjun Mehta', 'Sales Manager', '9861122334', 'arjun.m@vsms.com', 72000.00, '2023-09-18'
FROM Branch b WHERE b.name = 'Powai Mobility Studio'
  AND NOT EXISTS (SELECT 1 FROM Employee WHERE email = 'arjun.m@vsms.com');

INSERT INTO Employee (branch_id, name, role, phone, email, salary, hire_date)
SELECT b.branch_id, 'Meera Iyer', 'Finance Specialist', '9872233445', 'meera.i@vsms.com', 52000.00, '2024-06-03'
FROM Branch b WHERE b.name = 'Salt Lake Motors'
  AND NOT EXISTS (SELECT 1 FROM Employee WHERE email = 'meera.i@vsms.com');

INSERT INTO Customer (name, phone, email, address)
SELECT 'Nikhil Bansal', '9944556677', 'nikhil.b@gmail.com', '12 Palm Court, Viman Nagar, Pune'
WHERE NOT EXISTS (SELECT 1 FROM Customer WHERE phone = '9944556677');

INSERT INTO Customer (name, phone, email, address)
SELECT 'Ishita Menon', '9955667788', 'ishita.m@gmail.com', '24 Rose Garden, Kakkanad, Kochi'
WHERE NOT EXISTS (SELECT 1 FROM Customer WHERE phone = '9955667788');

INSERT INTO Customer (name, phone, email, address)
SELECT 'Aditya Singh', '9966778899', 'aditya.singh@gmail.com', 'B-18 Gomti Nagar, Lucknow'
WHERE NOT EXISTS (SELECT 1 FROM Customer WHERE phone = '9966778899');

INSERT INTO Customer (name, phone, email, address)
SELECT 'Divya Shah', '9977889900', 'divya.shah@gmail.com', '7 Satellite Road, Ahmedabad'
WHERE NOT EXISTS (SELECT 1 FROM Customer WHERE phone = '9977889900');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Mahindra XUV700 AX7', 2024, 'SUV', 'Diesel', 'Deep Silver', 2450000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Mahindra XUV700 AX7' AND color = 'Deep Silver');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Kia Seltos HTX', 2024, 'SUV', 'Petrol', 'Aurora Black', 1765000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Kia Seltos HTX' AND color = 'Aurora Black');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'TVS Apache RTR 310', 2024, 'Bike', 'Petrol', 'Racing Red', 285000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'TVS Apache RTR 310' AND color = 'Racing Red');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Tata Punch EV Empowered', 2024, 'Car', 'Electric', 'Seaweed Green', 1450000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Tata Punch EV Empowered' AND color = 'Seaweed Green');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Maruti Suzuki Grand Vitara', 2023, 'SUV', 'Hybrid', 'Chestnut Brown', 1920000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Maruti Suzuki Grand Vitara' AND color = 'Chestnut Brown');

-- Additional Vehicles for Rich Catalog
INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Tata Harrier Fearless Plus', 2024, 'SUV', 'Diesel', 'Sunlit Yellow', 2550000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Tata Harrier Fearless Plus');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Mahindra Thar LX 4x4', 2023, 'SUV', 'Diesel', 'Napoli Black', 1650000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Mahindra Thar LX 4x4');

INSERT INTO Vehicle (model, manufacturing_yr, type, fuel_type, color, price, status)
SELECT 'Royal Enfield Himalayan 450', 2024, 'Bike', 'Petrol', 'Hanle Black', 320000.00, 'Available'
WHERE NOT EXISTS (SELECT 1 FROM Vehicle WHERE model = 'Royal Enfield Himalayan 450');

-- -------------------------------------------------------------
-- Additional Sales Records (Creates Repeat Buyers & Varied Revenue)
-- Customer 1 (Rohan Kapoor) buys vehicle 2 (Hyundai Creta) -> Repeat Buyer!
-- Customer 2 (Ananya Gupta) buys vehicle 3 (Tata Nexon EV)
-- Customer 3 (Vikram Malhotra) buys vehicle 5 (Toyota Fortuner)
-- -------------------------------------------------------------
INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
SELECT 2, 1, 1, '2024-03-10', 1800000.00, 'Card'
WHERE NOT EXISTS (SELECT 1 FROM Sales WHERE vehicle_id = 2);
UPDATE Vehicle SET status = 'Sold' WHERE vehicle_id = 2;

INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
SELECT 3, 2, 2, '2024-04-18', 1650000.00, 'UPI'
WHERE NOT EXISTS (SELECT 1 FROM Sales WHERE vehicle_id = 3);
UPDATE Vehicle SET status = 'Sold' WHERE vehicle_id = 3;

INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
SELECT 5, 3, 3, '2024-05-22', 3750000.00, 'Net Banking'
WHERE NOT EXISTS (SELECT 1 FROM Sales WHERE vehicle_id = 5);
UPDATE Vehicle SET status = 'Sold' WHERE vehicle_id = 5;

-- Another purchase by Customer 2 (Ananya Gupta) -> Repeat Buyer!
INSERT INTO Sales (vehicle_id, customer_id, employee_id, sale_date, sale_price, payment_mode)
SELECT 4, 2, 1, '2024-06-15', 170000.00, 'Cash'
WHERE NOT EXISTS (SELECT 1 FROM Sales WHERE vehicle_id = 4);
UPDATE Vehicle SET status = 'Sold' WHERE vehicle_id = 4;

-- Additional Insurance Policies
INSERT INTO Insurance (vehicle_id, customer_id, provider, policy_no, start_date, end_date, premium)
SELECT 2, 1, 'ICICI Lombard General Insurance', 'POL-ICICI-2024-4412', '2024-03-10', '2025-03-09', 38000.00
WHERE NOT EXISTS (SELECT 1 FROM Insurance WHERE policy_no = 'POL-ICICI-2024-4412');

INSERT INTO Insurance (vehicle_id, customer_id, provider, policy_no, start_date, end_date, premium)
SELECT 3, 2, 'Tata AIG General Insurance', 'POL-TATA-2024-7729', '2024-04-18', '2025-04-17', 35000.00
WHERE NOT EXISTS (SELECT 1 FROM Insurance WHERE policy_no = 'POL-TATA-2024-7729');

INSERT INTO Insurance (vehicle_id, customer_id, provider, policy_no, start_date, end_date, premium)
SELECT 5, 3, 'Bajaj Allianz General Insurance', 'POL-BAJAJ-2024-9911', '2024-05-22', '2025-05-21', 72000.00
WHERE NOT EXISTS (SELECT 1 FROM Insurance WHERE policy_no = 'POL-BAJAJ-2024-9911');

