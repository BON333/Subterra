-- Aplicar uma vez em instalações existentes para habilitar votos de profundidade.
-- Votos já existentes passam a valer 1, preservando a interpretação positiva.
SET @add_vote_value = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE votos ADD COLUMN valor TINYINT NOT NULL DEFAULT 1',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'votos'
      AND COLUMN_NAME = 'valor'
);

PREPARE add_vote_value_statement FROM @add_vote_value;
EXECUTE add_vote_value_statement;
DEALLOCATE PREPARE add_vote_value_statement;
