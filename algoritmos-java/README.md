# Subterra: algoritmos

Implementação em Java puro (JDK 17+) dos algoritmos descritos nos slides. As estruturas usadas são as do slide 6: HashMap, LinkedList, PriorityQueue, grafo em lista de adjacência e Max-Flow.

Este módulo é uma demonstração independente e ainda não está integrado à aplicação PHP/MySQL em `app/`. O ficheiro `deploy/subterra.service` é apenas uma referência para um futuro backend Java; não corresponde ao serviço actualmente usado pelo projecto.

```
javac -encoding UTF-8 -d out src/*.java
java -Dstdout.encoding=UTF-8 -cp out Main
```

| # | Algoritmo | Ficheiro | Slide |
|---|-----------|----------|-------|
| 1 | Anonimato (username + identicon) | `Anonimato.java` | 2, 4 |
| 2 | Moderação automática | `Moderacao.java` | 7 |
| 3 | Densidade semântica + polarização | `DensidadeSemantica.java` | 2, 4, 5 |
| 4 | Grafo (lista de adjacência) | `Grafo.java` | 5, 6 |
| 5 | Max-Flow (Edmonds-Karp e Dinic) + Min-Cut | `MaxFlow.java` | 5 |
| 6 | Classificação superfície/subterrâneo por Min-Cut | `ClassificadorSubterraneo.java` | 5 |
| 7 | Community detection (Label Propagation) | `Comunidades.java` | 5 |
| 8 | Feed de profundidade (PriorityQueue, Top-K) | `RankingFeed.java` | 4, 7 |
| 9 | Rate limiting (Token Bucket) | `RateLimiter.java` | 4 |
| 10 | Chat em tempo real (fila FIFO) | `ChatTempoReal.java` | 4 |
| 11 | Limites de cgroups/namespaces | `deploy/subterra.service` | 6 |

---

## 1. Anonimato
```
gerarUsername():
    repetir
        u ← ADJ[aleatório] + "_" + SUBST[aleatório] + "_" + 4 dígitos aleatórios   (SecureRandom)
    até u ∉ usados
    usados ← usados ∪ {u};  devolver u

avatar(u):
    h ← SHA-256(u)
    para lin 0..4, col 0..2:  célula[lin][col] ← célula[lin][4-col] ← bit seguinte de h
```
O avatar é determinístico e simétrico e não usa nenhum dado real.

## 2. Moderação
```
avaliar(texto):
    se nº palavras < 25                      → CURTO_DEMAIS
    se contém termo proibido                 → PROIBIDO
    se links > 2 ou 8+ caracteres repetidos  → SPAM
    h ← SHA-256(texto normalizado);  hashes[h]++
    se hashes[h] > 1                         → DUPLICADO
    → APROVADO
```

## 3. Densidade semântica (0..1)
```
densidade = 0.30·min(palavras/120, 1)          comprimento
          + 0.30·(palavras distintas / total)  diversidade lexical
          + 0.25·(1 − proporção de stopwords)  conteúdo
          + 0.15·min(palavras por frase/20, 1) estrutura
          − penalização                        (!!!, MAIÚSCULAS, #hashtags, emojis, sinalização de virtude; máx. 0.5)
```

## 4–6. Max-Flow / Min-Cut: separar clusters
**Grafo:**
```
S = âncora superficial, T = âncora introspectiva
prof(p) = 0.8·densidade(p) + 0.2·min(upvotes/10, 1)
S → p   cap = 1 − prof(p)
p → T   cap = prof(p)
p ↔ q   cap = 0.5·Jaccard(p, q)     (se ≥ 0.05)
```
**Edmonds-Karp:**
```
fluxo ← 0
enquanto BFS encontra caminho S→T no grafo residual:
    g ← menor capacidade residual do caminho
    para cada aresta do caminho: fluxo(a) += g;  fluxo(reversa) −= g
    total += g
```
**Dinic:**
```
enquanto BFS a partir de S atinge T (construir níveis):
    enquanto DFS encontrar caminho só com nível+1: aumentar (blocking flow)
```
**Min-Cut:** a partir de S, faz BFS pelas arestas com residual > 0. Os vértices alcançados formam o lado S (superfície) e os restantes o lado T (subterrâneo). Pelo teorema Max-Flow = Min-Cut, o valor do corte é igual ao fluxo máximo.

Os dois métodos dão o mesmo valor e a mesma partição. Foram validados contra força bruta em 500 grafos aleatórios, com 0 falhas.

## 7. Community detection (alternativa ao Min-Cut)
```
rótulo(u) ← u para todo u
repetir até nenhum rótulo mudar (máx. 100):
    para u em ordem aleatória:
        rótulo(u) ← rótulo com maior soma de pesos entre os vizinhos (empate → menor)
comunidade introspectiva ⇔ densidade média dos posts dos membros ≥ 0.55
```

## 8. Feed de profundidade
```
score(p) = (0.55·densidade + 0.30·log(1+upvotes)/log(51) + 0.15·[subterrâneo]) · 1/(1 + horas/48)^0.8
se densidade < 0.35: score ← score·0.1            (posts rasos afundam)
feed:  max-heap (PriorityQueue) por score         O(n log n)
top-K: min-heap de tamanho K                      O(n log K)
```

## 9. Rate limiting (Token Bucket)
```
permitir(user, t):
    b ← baldes[user] (cria cheio)
    b.tokens ← min(capacidade, b.tokens + (t − b.último)·taxa);  b.último ← t
    se b.tokens ≥ 1: b.tokens −= 1; devolver VERDADEIRO
    devolver FALSO
```
Este limite é por utilizador. O limite de CPU e memória por processo é feito pelos cgroups em `deploy/subterra.service`.

## 10. Chat em tempo real
```
enviar(sala, autor, texto):
    rejeitar se autor não inscrito, texto vazio/>2000, ou RateLimiter nega
    filas[sala].addLast(mensagem)
despachar():
    para cada sala: enquanto fila não vazia:
        m ← fila.pollFirst();  entregar m a cada inscrito ≠ autor   (WebSocket)
```
