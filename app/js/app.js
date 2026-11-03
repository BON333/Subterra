
// ========================================
// SUBTERRA - JAVASCRIPT
// ETAPA 2: AUTENTICACAO
// ========================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const botoes = document.querySelectorAll(
            "[data-toggle-password]"
        );

        botoes.forEach(function (botao) {

            botao.addEventListener(
                "click",
                function () {

                    const id = botao.getAttribute(
                        "data-toggle-password"
                    );

                    const campo = document.getElementById(
                        id
                    );

                    if (!campo) {
                        return;
                    }

                    if (campo.type === "password") {

                        campo.type = "text";

                        botao.textContent = "Ocultar";

                        botao.setAttribute(
                            "aria-label",
                            "Ocultar password"
                        );

                    } else {

                        campo.type = "password";

                        botao.textContent = "Mostrar";

                        botao.setAttribute(
                            "aria-label",
                            "Mostrar password"
                        );

                    }

                }
            );

        });

    }
);