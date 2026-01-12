BEGIN;
TRUNCATE TABLE items RESTART IDENTITY CASCADE;
CREATE TEMP TABLE items_import (
    kode text,
    nama text,
    kategori text,
    satuan text,
    deskripsi text
);
\copy items_import (kode, nama, kategori, satuan, deskripsi) FROM 'D:\\laragon\\www\\ProjectPLN\\storage\\app\\items_import_clean.csv' WITH (FORMAT csv, HEADER true);
INSERT INTO items (kode, nama, kategori, satuan, deskripsi, created_at, updated_at)
SELECT kode, nama, kategori, satuan, NULLIF(deskripsi, ''), NOW(), NOW()
FROM items_import;
COMMIT;
