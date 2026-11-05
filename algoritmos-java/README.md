# Algoritmos SUBTERRA

Serviço Java 17 sem dependências externas que aplica o classificador Min-Cut e
o `PriorityQueue` do feed, moderação heurística e token bucket. A aplicação PHP
continua responsável por sessões, autorização e MySQL; o serviço só aceita
ligações em `127.0.0.1`.

## Compilar e testar

```sh
./algoritmos-java/build.sh
```

O script compila os algoritmos e o servidor, corre os testes de unidade e gera
`algoritmos-java/build/subterra-algoritmos.jar`.

## Executar o serviço

```sh
java -jar algoritmos-java/build/subterra-algoritmos.jar
```

O serviço escuta em `127.0.0.1:8787`. Define `SUBTERRA_ALGORITHM_PORT` para
escolher outra porta. Endpoints internos:

- `GET /health`
- `POST /api/rank` recebe `application/x-www-form-urlencoded` com campos
  `posts[0][id]`, `posts[0][text]`, `posts[0][created_at]` (Unix, segundos)
  e `posts[0][upvotes]`; devolve os IDs ordenados, densidade, classificação
  subterrânea e pontuação.
- `POST /api/moderate` recebe `text` e devolve o resultado heurístico.
- `POST /api/rate-limit` recebe `user_id` e devolve `allowed`.

`SUBTERRA_BLOCKED_TERMS` aceita uma lista opcional de termos separados por
vírgula; não há uma lista de insultos/termos ilegais embutida. A moderação
automática só faz verificações simples de comprimento, repetição e links, não
substitui revisão humana. O histórico de duplicados e os tokens do rate limit
residem na memória e reiniciam com o serviço.

## Instalação systemd

Instala o JAR em `/opt/subterra/subterra-algoritmos.jar` e copia
`deploy/subterra-algoritmos.service` para
`/etc/systemd/system/subterra-algoritmos.service`. Cria a conta e directoria
de sistema `subterra` antes de activar a unidade.

No PHP, define `SUBTERRA_ALGORITHM_URL=http://127.0.0.1:8787`. O feed,
publicação e endpoint de chat passam pelo serviço; se este estiver indisponível,
a aplicação mostra um erro em vez de usar uma ordenação alternativa.

## Limites

O classificador compara os textos em pares, por isso o endpoint limita cada
pedido a 50 publicações. O módulo `ChatTempoReal` é uma implementação de fila
para testes/demonstração; o chat da aplicação continua a usar polling PHP/MySQL,
não WebSocket. O serviço não implementa Spring Boot nem PostgreSQL, pois o
projeto web atual é PHP/MySQL e os algoritmos do ZIP são Java puro.
