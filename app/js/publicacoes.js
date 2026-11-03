
// ========================================
// SUBTERRA - ETAPA 4
// COMENTARIOS E VOTOS
// ========================================

document.addEventListener("DOMContentLoaded", function () {

    // =====================================
    // CONFIRMAR ELIMINACAO DE PUBLICACOES
    // =====================================

    document.querySelectorAll(
        "[data-confirm-delete]"
    ).forEach(function (formulario) {

        formulario.addEventListener("submit", function (evento) {

            const confirmado = window.confirm(
                "Tens a certeza de que queres eliminar esta publicacao?"
            );

            if (!confirmado) {
                evento.preventDefault();
            }
        });
    });


    // =====================================
    // CONFIRMAR ELIMINACAO DE COMENTARIOS
    // =====================================

    document.querySelectorAll(
        "[data-confirm-delete-comment]"
    ).forEach(function (formulario) {

        formulario.addEventListener("submit", function (evento) {

            const confirmado = window.confirm(
                "Tens a certeza de que queres eliminar este comentario?"
            );

            if (!confirmado) {
                evento.preventDefault();
            }
        });
    });


    // =====================================
    // TOKEN CSRF
    // =====================================

    const metaCSRF = document.querySelector(
        'meta[name="csrf-token"]'
    );

    const tokenCSRF = metaCSRF
        ? metaCSRF.getAttribute("content")
        : "";


    // =====================================
    // SISTEMA DE VOTOS
    // =====================================

    document.querySelectorAll(
        "[data-vote-box]"
    ).forEach(function (caixa) {

        const botoes = caixa.querySelectorAll(
            "[data-vote-value]"
        );

        const pontuacao = caixa.querySelector(
            "[data-score]"
        );

        const totalVotos = caixa.querySelector(
            "[data-total-votes]"
        );

        const feedback = caixa.querySelector(
            "[data-vote-feedback]"
        );

        botoes.forEach(function (botao) {

            botao.addEventListener(
                "click",
                async function () {

                    const publicacaoId = Number(
                        caixa.dataset.postId
                    );

                    const votoActual = Number(
                        caixa.dataset.myVote || "0"
                    );

                    const votoEscolhido = Number(
                        botao.dataset.voteValue
                    );

                    // Clicar novamente retira o voto
                    const novoVoto =
                        votoActual === votoEscolhido
                            ? 0
                            : votoEscolhido;

                    botoes.forEach(function (b) {
                        b.disabled = true;
                    });

                    feedback.textContent = "A guardar...";

                    try {

                        const resposta = await fetch(
                            "publicacoes/votar.php",
                            {
                                method: "POST",

                                credentials: "same-origin",

                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-Token": tokenCSRF
                                },

                                body: JSON.stringify({
                                    publicacao_id: publicacaoId,
                                    valor: novoVoto
                                })
                            }
                        );

                        const dados = await resposta.json();

                        if (resposta.status === 401) {
                            window.location.href =
                                "auth/login.php";
                            return;
                        }

                        if (!resposta.ok || !dados.ok) {
                            throw new Error(
                                dados.erro ||
                                "Nao foi possivel registar o voto."
                            );
                        }

                        // Actualizar estado
                        caixa.dataset.myVote = String(
                            dados.meu_voto
                        );

                        pontuacao.textContent =
                            dados.pontuacao;

                        totalVotos.textContent =
                            dados.total_votos + " votos";

                        // Actualizar botoes activos
                        botoes.forEach(function (b) {

                            const valor = Number(
                                b.dataset.voteValue
                            );

                            const activo =
                                valor === dados.meu_voto;

                            b.classList.toggle(
                                "is-active",
                                activo
                            );

                            b.setAttribute(
                                "aria-pressed",
                                activo ? "true" : "false"
                            );
                        });

                        feedback.textContent =
                            dados.meu_voto === 0
                                ? "Voto retirado."
                                : "Voto registado.";

                    } catch (erro) {

                        feedback.textContent =
                            erro.message ||
                            "Erro ao comunicar com o servidor.";

                    } finally {

                        botoes.forEach(function (b) {
                            b.disabled = false;
                        });
                    }
                }
            );
        });
    });

});