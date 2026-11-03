
<?php

require_once __DIR__ . "/includes/bootstrap.php";

?>
<!DOCTYPE html>
<html lang="pt">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>SUBTERRA | As ideias tem voz</title>

    <link rel="stylesheet" href="css/style.css">

    <script src="js/app.js" defer></script>

</head>

<body>

<header class="cabecalho">

    <a class="logo" href="index.php">
        SUBTERRA<span>.</span>
    </a>

    <nav class="nav-links">

        <?php if (estaAutenticado()): ?>

            <a class="nav-link" href="feed.php">
                Feed
            </a>

        <?php else: ?>

            <a class="nav-link" href="auth/login.php">
                Entrar
            </a>

            <a class="btn btn-outline"
               href="auth/registo.php">
                Criar conta
            </a>

        <?php endif; ?>

    </nav>

</header>

<main class="principal">

    <section class="hero">

        <p class="etiqueta">
            / UMA COMUNIDADE DE IDEIAS
        </p>

        <h1 class="hero-title">

            As ideias tem voz.

            <span>Os nomes nao.</span>

        </h1>

        <p class="hero-subtitle">
            Um espaco para partilhar ideias,
            explorar perspectivas e participar
            em conversas sem exposicao publica
            da identidade.
        </p>

        <div class="hero-actions">

            <?php if (estaAutenticado()): ?>

                <a
                    class="btn btn-primary"
                    href="feed.php"
                >
                    Entrar no feed
                </a>

            <?php else: ?>

                <a
                    class="btn btn-primary"
                    href="auth/registo.php"
                >
                    Comecar agora
                </a>

                <a
                    class="btn btn-outline"
                    href="auth/login.php"
                >
                    Ja tenho conta
                </a>

            <?php endif; ?>

        </div>

    </section>

    <section class="painel">

        <div class="painel-cabecalho">

            <span>SUBTERRA / SISTEMA</span>

            <span>v0.2</span>

        </div>

        <h2>Uma identidade privada.</h2>

        <p class="painel-descricao">
            A SUBTERRA permite participar
            na comunidade sem apresentar
            publicamente o pseudonimo
            associado a cada publicacao.
        </p>

        <div class="system-line">

            <span class="ponto"></span>

            Sistema de autenticacao disponivel

        </div>

    </section>

    <section class="modulos">

        <p class="etiqueta">
            / ARQUITECTURA
        </p>

        <h2>Explora a SUBTERRA</h2>

        <div class="grelha">

            <article class="modulo">

                <h3>01 / Identidade</h3>

                <p>
                    Registo, login, passwords
                    protegidas e sessoes privadas.
                </p>

                <span>ETAPA 2</span>

            </article>

            <article class="modulo">

                <h3>02 / Publicacoes</h3>

                <p>
                    Feed anonimo, comentarios
                    e interaccoes.
                </p>

                <span>PROXIMA ETAPA</span>

            </article>

            <article class="modulo">

                <h3>03 / Comunidade</h3>

                <p>
                    Chat, categorias, regras
                    e moderacao.
                </p>

                <span>EM DESENVOLVIMENTO</span>

            </article>

            <article class="modulo">

                <h3>04 / Inteligencia</h3>

                <p>
                    Algoritmos Java de ranking,
                    classificacao e grafos.
                </p>

                <span>EM DESENVOLVIMENTO</span>

            </article>

        </div>

    </section>

</main>

<footer class="rodape">

    <span>SUBTERRA © 2026</span>

    <span>
        HTML / CSS / JS / PHP / MYSQL
    </span>

</footer>

</body>
</html>
