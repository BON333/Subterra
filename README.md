# SUBTERRA

Comunidade de publicacoes e conversas anonimas, feita em PHP, MySQL, HTML,
CSS e JavaScript. As publicacoes, comentarios e mensagens nao mostram o
pseudonimo do autor aos outros membros; as contas continuam associadas aos
registos para permitir gestao e moderacao.

## Executar localmente

Requisitos: PHP 8.1 ou superior com `pdo_mysql`, e MySQL 8.0.16 ou superior.

1. Importa `app/subterra_app.sql` num servidor MySQL com permissao para criar a
   base de dados:

   ```sh
   mysql -u root -p < app/subterra_app.sql
   ```

2. Cria um utilizador MySQL dedicado. Substitui a palavra-passe de exemplo por
   uma palavra-passe unica; nao a guardes no repositorio:

   ```sql
   CREATE USER 'subterra_user'@'localhost' IDENTIFIED BY 'substitui-esta-senha';
   GRANT SELECT, INSERT, UPDATE, DELETE ON subterra_app.* TO 'subterra_user'@'localhost';
   ```

3. Configura a ligacao e inicia o servidor com `app/` como raiz publica:

   ```sh
   export SUBTERRA_DB_HOST=127.0.0.1
   export SUBTERRA_DB_PORT=3306
   export SUBTERRA_DB_NAME=subterra_app
   export SUBTERRA_DB_USER=subterra_user
   export SUBTERRA_DB_PASSWORD='a-tua-senha'
   php -S 127.0.0.1:8000 -t app
   ```

4. Abre <http://127.0.0.1:8000>. As variaveis de ambiente tambem podem ser
   definidas no servico PHP/Apache. `SUBTERRA_BASE_PATH` e opcional; usa-o
   apenas se a aplicacao estiver montada num subcaminho especifico.

Para verificar a ligacao, abre `/api/teste_conexao.php`. Erros detalhados
ficam no registo do servidor, nao na resposta publica.

## Funcionalidades

- Registo, login, sessao privada e logout com proteccao CSRF.
- Feed de publicacoes anonimas, categorias, votos positivos/negativos,
  comentarios, edicao, eliminacao e denuncias.
- Chat publico anonimo com actualizacao automatica.
- Painel de moderacao para analisar denuncias, ocultar/restaurar publicacoes
  e remover mensagens.
- Interface adaptavel a telemoveis, com uma apresentacao de imageboard simples
  inspirada no pedido visual.

Para promover um membro a moderador, depois de criar a conta, executa como
administrador da base de dados:

```sql
UPDATE usuarios SET papel = 'moderador' WHERE pseudonimo = 'nome_do_membro';
```

### Base de dados existente

Se a base de dados ja foi criada com uma versao anterior do esquema, aplica
`app/migrations/001-voting-and-reports.sql` uma vez. Se ja houver denuncias
duplicadas da mesma conta para a mesma publicacao, remove os duplicados antes
de criar a chave unica.

O repositorio recebido nao inclui uma apresentacao ou diapositivos; por isso,
o estilo segue a referencia textual a um imageboard classico, nao uma
correspondencia exacta a um ficheiro de design.
