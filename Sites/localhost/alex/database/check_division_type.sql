-- Verificar estrutura da tabela products
DESCRIBE products;

-- Verificar valores atuais da coluna division_type
SELECT division_type, COUNT(*) as total
FROM products
WHERE deleted = 0
GROUP BY division_type;

-- Mostrar alguns produtos com seus tipos de divisão
SELECT id, name, ncm, unit, division_type
FROM products
WHERE deleted = 0
ORDER BY id
LIMIT 10;