-- User import untuk gudang Tarahan & Teluk Betung
-- Catatan: semua user dihapus dulu, lalu insert ulang

-- Hapus semua user beserta data yang berelasi (PostgreSQL)
-- PERHATIAN: TRUNCATE ... CASCADE akan menghapus data di tabel yang berelasi (mis. surat_jalans, peminjamans, dll.)
TRUNCATE TABLE users RESTART IDENTITY CASCADE;

-- Hash password (bcrypt):
-- 12345678  -> $2y$12$6CscduJxUgczH2jgl0ok4./3b5jpz30qH1XelYY8LYd7495v.PVwq
-- 1234567@  -> $2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq

-- Admin: Erwin Darmawan (akses semua gudang)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Erwin Darmawan',
    'admin',
    'admin@egudang.local',
    'admin',
    NULL,
    'Administrator',
    NULL,
    '$2y$12$6CscduJxUgczH2jgl0ok4./3b5jpz30qH1XelYY8LYd7495v.PVwq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

-- Manager: Mega Sukmawan (kelola semua gudang)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Mega Sukmawan',
    'manager',
    'manager@egudang.local',
    'manager',
    NULL,
    'Manager ULPLTD/G Tanjung Karang',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'manager');

-- Penerima: Azril Azis (Tarahan)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Azril Azis',
    'azrilazis',
    'azrilazis@egudang.local',
    'penerima',
    (SELECT id FROM gudangs WHERE nama LIKE '%Tarahan%' LIMIT 1),
    'TL OPHAR Tarahan',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'azrilazis');

-- Penerima: Adam Agustian (Teluk Betung)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Adam Agustian',
    'adamagustian',
    'adamagustian@egudang.local',
    'penerima',
    (SELECT id FROM gudangs WHERE nama LIKE '%Teluk Betung%' LIMIT 1),
    'TL OPHAR Teluk Betung',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'adamagustian');

-- Penerima: Erwin Darmawan (Teluk Betung)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Erwin Darmawan',
    'erwindarmawan',
    'erwindarmawan@egudang.local',
    'penerima',
    (SELECT id FROM gudangs WHERE nama LIKE '%Teluk Betung%' LIMIT 1),
    'TL K3LK',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'erwindarmawan');

-- Security: Daryanto (Tarahan)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Daryanto',
    'satpamtarahan',
    'satpamtarahan@egudang.local',
    'security',
    (SELECT id FROM gudangs WHERE nama LIKE '%Tarahan%' LIMIT 1),
    'Security PLTD/G Tarahan',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'satpamtarahan');

-- Security: Chandra Dwi Kurniawan (Teluk Betung)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Chandra Dwi Kurniawan',
    'satpamtelukbetung',
    'satpamtelukbetung@egudang.local',
    'security',
    (SELECT id FROM gudangs WHERE nama LIKE '%Teluk Betung%' LIMIT 1),
    'Security PLTD Teluk Betung',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'satpamtelukbetung');

-- Operator Gudang: Kholik (Tarahan)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Kholik',
    'toolmantrh',
    'toolmantrh@egudang.local',
    'operator_gudang',
    (SELECT id FROM gudangs WHERE nama LIKE '%Tarahan%' LIMIT 1),
    'Tool Man PLTD/G Tarahan',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'toolmantrh');

-- Operator Gudang: Tomi (Teluk Betung)
INSERT INTO users (name, username, email, role, gudang_id, jabatan, no_hp, password, is_active, created_at, updated_at)
SELECT
    'Tomi',
    'toolmantlb',
    'toolmantlb@egudang.local',
    'operator_gudang',
    (SELECT id FROM gudangs WHERE nama LIKE '%Teluk Betung%' LIMIT 1),
    'Tool Man PLTD Teluk Betung',
    NULL,
    '$2y$12$Gq4TyjXg1Ijujurr0ZHlIOU2Yp4qtkBFxVkPlFMJoNvmfMIOjTFCq',
    TRUE,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'toolmantlb');

-- Buat PIC untuk penerima & Operator Gudang
INSERT INTO pics (nama, jabatan, no_hp, gudang_id, user_id, created_at, updated_at)
SELECT
    u.name,
    u.jabatan,
    u.no_hp,
    u.gudang_id,
    u.id,
    NOW(),
    NOW()
FROM users u
WHERE u.username IN ('azrilazis', 'adamagustian', 'erwindarmawan', 'toolmantrh', 'toolmantlb')
  AND NOT EXISTS (SELECT 1 FROM pics p WHERE p.user_id = u.id);

-- Relasi gudang_user untuk admin & manager (Tarahan + Teluk Betung)
INSERT INTO gudang_user (gudang_id, user_id, created_at, updated_at)
SELECT g.id, u.id, NOW(), NOW()
FROM gudangs g
JOIN users u ON u.username = 'admin'
WHERE g.nama LIKE '%Tarahan%'
  AND NOT EXISTS (
      SELECT 1 FROM gudang_user gu WHERE gu.gudang_id = g.id AND gu.user_id = u.id
  );

INSERT INTO gudang_user (gudang_id, user_id, created_at, updated_at)
SELECT g.id, u.id, NOW(), NOW()
FROM gudangs g
JOIN users u ON u.username = 'admin'
WHERE g.nama LIKE '%Teluk Betung%'
  AND NOT EXISTS (
      SELECT 1 FROM gudang_user gu WHERE gu.gudang_id = g.id AND gu.user_id = u.id
  );

INSERT INTO gudang_user (gudang_id, user_id, created_at, updated_at)
SELECT g.id, u.id, NOW(), NOW()
FROM gudangs g
JOIN users u ON u.username = 'manager'
WHERE g.nama LIKE '%Tarahan%'
  AND NOT EXISTS (
      SELECT 1 FROM gudang_user gu WHERE gu.gudang_id = g.id AND gu.user_id = u.id
  );

INSERT INTO gudang_user (gudang_id, user_id, created_at, updated_at)
SELECT g.id, u.id, NOW(), NOW()
FROM gudangs g
JOIN users u ON u.username = 'manager'
WHERE g.nama LIKE '%Teluk Betung%'
  AND NOT EXISTS (
      SELECT 1 FROM gudang_user gu WHERE gu.gudang_id = g.id AND gu.user_id = u.id
  );
