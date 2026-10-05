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
