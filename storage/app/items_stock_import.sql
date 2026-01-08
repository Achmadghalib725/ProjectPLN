BEGIN;
CREATE TEMP TABLE items_stock_import (
    kategori text,
    nama text,
    satuan text,
    jumlah integer
);
\copy items_stock_import (kategori, nama, satuan, jumlah) FROM 'C:\\laragon\\www\\ProjectPLN\\storage\\app\\items_stock_import.csv' WITH (FORMAT csv, HEADER true);
DO $$
DECLARE
    gid bigint;
    missing_count int;
    inserted_count int;
BEGIN
    SELECT id INTO gid FROM gudangs WHERE lower(nama) LIKE lower('%teluk betung%') LIMIT 1;
    IF gid IS NULL THEN
        RAISE EXCEPTION 'Gudang Teluk Betung not found';
    END IF;

    DELETE FROM item_stocks WHERE gudang_id = gid;

    INSERT INTO item_stocks (item_id, gudang_id, jumlah, stok_minimum, created_at, updated_at)
    SELECT i.id, gid, s.jumlah, 0, NOW(), NOW()
    FROM items_stock_import s
    JOIN items i
        ON lower(i.nama) = lower(s.nama)
       AND lower(i.kategori) = lower(s.kategori)
       AND lower(i.satuan) = lower(s.satuan);

    GET DIAGNOSTICS inserted_count = ROW_COUNT;

    SELECT COUNT(*) INTO missing_count
    FROM items_stock_import s
    LEFT JOIN items i
        ON lower(i.nama) = lower(s.nama)
       AND lower(i.kategori) = lower(s.kategori)
       AND lower(i.satuan) = lower(s.satuan)
    WHERE i.id IS NULL;

    RAISE NOTICE 'Inserted % item_stocks for Teluk Betung.', inserted_count;
    IF missing_count > 0 THEN
        RAISE NOTICE 'Missing items during match: %', missing_count;
    END IF;
END $$;
COMMIT;
