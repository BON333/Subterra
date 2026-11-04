
<?php

require_once __DIR__ . "/bootstrap.php";

// ========================================
// CATEGORIAS DISPONIVEIS
// ========================================

function categoriasPublicacoes(): array
{
    return [
        "geral" => "Geral",
        "tecnologia" => "Tecnologia",
        "educacao" => "Educacao",
        "sociedade" => "Sociedade",
        "desabafos" => "Desabafos"
    ];
}


// ========================================
// CONTAR CARACTERES UTF-8
// ========================================

function contarCaracteres(string $texto): int
{
    if (function_exists("mb_strlen")) {
        return mb_strlen($texto, "UTF-8");
    }

    $quantidade = preg_match_all(
        "/./us",
        $texto
    );

    return $quantidade === false
        ? -1
        : $quantidade;
}


// ========================================
// VALIDAR PUBLICACAO
// ========================================

function validarPublicacao(
    string $titulo,
    string $conteudo,
    string $categoria
): string {

    $tamanhoTitulo = contarCaracteres($titulo);
    $tamanhoConteudo = contarCaracteres($conteudo);

    if (
        $tamanhoTitulo < 5 ||
        $tamanhoTitulo > 160
    ) {
        return "O titulo deve ter entre "
             . "5 e 160 caracteres.";
    }

    if (
        $tamanhoConteudo < 10 ||
        $tamanhoConteudo > 5000
    ) {
        return "O conteudo deve ter entre "
             . "10 e 5000 caracteres.";
    }

    if (
        !array_key_exists(
            $categoria,
            categoriasPublicacoes()
        )
    ) {
        return "Selecciona uma categoria valida.";
    }

    return "";
}


function densidadeSemantica(string $texto): float
{
    if (function_exists("mb_strtolower")) {
        $texto = mb_strtolower($texto, "UTF-8");
    } else {
        $texto = strtr(strtolower($texto), [
            "Á" => "á", "À" => "à", "Â" => "â", "Ã" => "ã",
            "Ç" => "ç", "É" => "é", "Ê" => "ê", "Í" => "í",
            "Ó" => "ó", "Ô" => "ô", "Õ" => "õ", "Ú" => "ú"
        ]);
    }

    preg_match_all("/[\\p{L}\\p{N}#]+/u", $texto, $correspondencias);
    $tokens = $correspondencias[0] ?? [];
    $total = count($tokens);

    if ($total === 0) {
        return 0.0;
    }

    $stopwords = [
        "a", "o", "as", "os", "um", "uma", "de", "do", "da", "dos",
        "das", "e", "é", "em", "no", "na", "nos", "nas", "que", "se",
        "por", "para", "pra", "com", "não", "mas", "ou", "eu", "tu",
        "ele", "ela", "isso", "isto", "só", "já", "muito", "mais", "me",
        "te", "lhe", "ao", "à", "seu", "sua", "meu", "minha", "kkk",
        "kkkk", "lol", "tipo", "né"
    ];

    $distintos = count(array_unique($tokens));
    $stopwordsEncontradas = count(array_intersect($tokens, $stopwords));
    $frases = preg_split("/[.!?]+\\s*/u", trim($texto), -1, PREG_SPLIT_NO_EMPTY);
    $numeroFrases = max(1, count($frases));

    $comprimento = min($total / 120, 1);
    $diversidade = $distintos / $total;
    $conteudo = 1 - ($stopwordsEncontradas / $total);
    $estrutura = min(($total / $numeroFrases) / 20, 1);

    $penalizacao = 0.0;
    $penalizacao += min(substr_count($texto, "!") * 0.03, 0.15);

    preg_match_all("/\\p{L}/u", $texto, $letras);
    preg_match_all("/\\p{Lu}/u", $texto, $maiusculas);
    if (
        count($letras[0]) > 0 &&
        count($maiusculas[0]) / count($letras[0]) > 0.5
    ) {
        $penalizacao += 0.10;
    }

    $penalizacao += min(substr_count($texto, "#") * 0.03, 0.10);
    preg_match_all("/[\\x{1F300}-\\x{10FFFF}]/u", $texto, $emojis);
    $penalizacao += min(count($emojis[0]) * 0.02, 0.05);

    $sinalizacoesVirtude = [
        "gratidão", "good vibes", "abençoado", "abençoada",
        "só positividade", "foco força fé", "#blessed",
        "segue para mais", "partilha se concordas"
    ];
    foreach ($sinalizacoesVirtude as $sinalizacao) {
        if (str_contains($texto, $sinalizacao)) {
            $penalizacao += 0.10;
            break;
        }
    }

    $penalizacao = min($penalizacao, 0.5);
    $densidade = 0.30 * $comprimento
        + 0.30 * $diversidade
        + 0.25 * $conteudo
        + 0.15 * $estrutura
        - $penalizacao;

    return max(0.0, min(1.0, $densidade));
}


function pontuacaoFeed(array $publicacao, ?int $agora = null): float
{
    $densidade = (float) $publicacao["densidade"];
    $upvotes = max(0, (int) $publicacao["upvotes_profundidade"]);
    $criadoEm = strtotime((string) $publicacao["criado_em"]);

    if ($criadoEm === false) {
        throw new RuntimeException("Data de publicacao invalida.");
    }

    $agora = $agora ?? time();
    $upvotesNormalizados = log(1 + $upvotes) / log(1 + 50);
    $base = 0.55 * $densidade
        + 0.30 * min($upvotesNormalizados, 1.0);
    $horas = max(0, ($agora - $criadoEm) / 3600);
    $decaimento = 1 / pow(1 + $horas / 48, 0.8);
    $pontuacao = $base * $decaimento;

    if ($densidade < 0.35) {
        $pontuacao *= 0.1;
    }

    return $pontuacao;
}


// ========================================
// MENSAGENS TEMPORARIAS
// ========================================

function definirMensagem(
    string $tipo,
    string $texto
): void {

    $_SESSION["flash"] = [
        "tipo" => $tipo,
        "texto" => $texto
    ];
}


function obterMensagem(): ?array
{
    $mensagem = $_SESSION["flash"] ?? null;

    unset($_SESSION["flash"]);

    return is_array($mensagem)
        ? $mensagem
        : null;
}
