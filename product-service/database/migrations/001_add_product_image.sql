-- Run once against an existing database that was initialized before image was added.
ALTER TABLE products
    ADD COLUMN image MEDIUMTEXT NULL;

UPDATE products
SET image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/SuoAAAAASUVORK5CYII='
WHERE image IS NULL;

ALTER TABLE products
    MODIFY COLUMN image MEDIUMTEXT NOT NULL;
