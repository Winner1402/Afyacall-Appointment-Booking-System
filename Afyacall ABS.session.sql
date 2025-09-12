SELECT * 
FROM doctor_slots 
WHERE doctor_id = 14
ORDER BY slot_datetime ASC;
SELECT * 
FROM doctor_slots 
WHERE doctor_id = 14
  AND status = 0
  AND slot_datetime >= NOW()
ORDER BY slot_datetime ASC;
SELECT * 
FROM doctors 
WHERE id = 14;
SELECT u.id, u.name, u.email 
FROM doctors d
JOIN users u ON d.user_id = u.id
WHERE d.id = 14;
SELECT d.id as doctor_id, s.name as specialty
FROM doctors d
JOIN specialties s ON d.specialty_id = s.id
WHERE d.id = 14;
