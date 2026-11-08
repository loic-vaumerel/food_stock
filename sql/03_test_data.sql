-- Food stock - TEST DATA (development only, never load in production).
--
-- All test accounts use the password:  password
-- The bcrypt hash below is upgraded automatically to Argon2id on first login
-- (when PHP supports it). Create real users with app/bin/hash_password.php.

USE lvlotoamdat;

INSERT IGNORE INTO food_stock_users (email, password_hash, is_active) VALUES
    ('a@example.com', '$2y$12$24nPH1Y07s3L.X1EvQLnJOYCthuqGtHUrsuqGfalbabIIKN.iiLSG', 1),
    ('b@example.com', '$2y$12$24nPH1Y07s3L.X1EvQLnJOYCthuqGtHUrsuqGfalbabIIKN.iiLSG', 1),
    ('c@example.com', '$2y$12$24nPH1Y07s3L.X1EvQLnJOYCthuqGtHUrsuqGfalbabIIKN.iiLSG', 0);

INSERT IGNORE INTO food_stock_locations (name) VALUES
    ('fridge'), ('freezer'), ('pantry'), ('kitchen');

-- "pâte" and "pâté" are distinct products (accent-sensitive uniqueness).
INSERT IGNORE INTO food_stock_products (name) VALUES
    ('cooked cabbage'), ('sliced pumpkin'), ('lamb chops'), ('orange ice cream'),
    ('eggs'), ('pâte'), ('pâté');

-- Dates are relative to today so the expired / expiring highlights always show.
-- Amounts are already normalised (500 g, never 0.5 kg).
INSERT IGNORE INTO food_stock_entries
    (product_id, location_id, container_id, amount, unit, quantity, storage_date, expiry_date)
SELECT p.id, l.id, c.id, t.amount, t.unit, t.quantity,
       CURDATE() - INTERVAL t.stored_days_ago DAY,
       IF(t.expires_in IS NULL, NULL, CURDATE() + INTERVAL t.expires_in DAY)
  FROM (
            SELECT 'lamb chops' AS product, 'freezer' AS location, 'bag' AS container,
                   1.000 AS amount, 'kg' AS unit, 3 AS quantity, 20 AS stored_days_ago, 160 AS expires_in
  UNION ALL SELECT 'lamb chops',       'freezer', 'bag',  500.000, 'g',      1, 10, 170
  UNION ALL SELECT 'orange ice cream', 'freezer', 'box',    1.000, 'l',      2, 30, 300
  UNION ALL SELECT 'sliced pumpkin',   'freezer', 'bag',  200.000, 'g',      4, 40, 140
  UNION ALL SELECT 'cooked cabbage',   'fridge',  'box',  400.000, 'g',      1,  2,   3
  UNION ALL SELECT 'sliced pumpkin',   'fridge',  'bag',  200.000, 'g',      1,  8,  -1
  UNION ALL SELECT 'eggs',             'fridge',  'box',   10.000, 'pieces', 2,  3,  20
  UNION ALL SELECT 'pâte',             'pantry',  'pack', 500.000, 'g',      4, 15, 365
  UNION ALL SELECT 'pâté',             'pantry',  'pot',  180.000, 'g',      2, 30, NULL
       ) AS t
  JOIN food_stock_products   p ON p.name_key = t.product
  JOIN food_stock_locations  l ON l.name_key = t.location
  JOIN food_stock_containers c ON c.name_key = t.container;
