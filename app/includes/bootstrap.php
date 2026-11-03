
<?php

/*
========================================
SUBTERRA - CONFIGURACAO GERAL
========================================
*/

// ========================================
// 1. CONFIGURAR SESSAO
// ========================================

if (session_status() === PHP_SESSION_NONE) {

    $https = (
        !empty($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    // Rejeitar identificadores de sessao
    // que nao tenham sido criados pelo PHP.
    ini_set("session.use_strict_mode", "1");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/Subterra/",
        "secure" => $https,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}


// ========================================
// 2. IMPEDIR CACHE
// ========================================

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header("Pragma: no-cache");


// ========================================
// 3. IMPORTAR CONEXAO MYSQL
// ========================================

require_once
    "C:/xampp/subterra-private/database.php";


// ========================================
// 4. ESCAPAR TEXTO PARA HTML
// ========================================

function escapar($texto)
{
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}


// ========================================
// 5. GERAR TOKEN CSRF
// ========================================

function tokenCSRF()
{
    if (empty($_SESSION["csrf_token"])) {

        $_SESSION["csrf_token"] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}


// ========================================
// 6. VALIDAR TOKEN CSRF
// ========================================

function validarCSRF($token)
{
    return is_string($token)
        && isset($_SESSION["csrf_token"])
        && hash_equals(
            $_SESSION["csrf_token"],
            $token
        );
}


// ========================================
// 7. VERIFICAR AUTENTICACAO
// ========================================

function estaAutenticado()
{
    return isset($_SESSION["usuario_id"])
        && (int) $_SESSION["usuario_id"] > 0;
}


// ========================================
// 8. PROTEGER PAGINAS PRIVADAS
// ========================================

function exigirLogin()
{
    if (!estaAutenticado()) {

        redireccionar("auth/login.php");
    }
}


// ========================================
// 9. REDIRECCIONAR
// ========================================

function redireccionar($caminho)
{
    header(
        "Location: /Subterra/" .
        ltrim($caminho, "/"),
        true,
        303
    );

    exit;
}