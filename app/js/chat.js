
// ========================================
// SUBTERRA - ETAPA 5
// CHAT PUBLICO ANONIMO
// ========================================

document.addEventListener("DOMContentLoaded", function () {

    const lista = document.getElementById("chatMessages");
    const formulario = document.getElementById("chatForm");
    const campo = document.getElementById("chatConteudo");
    const feedback = document.getElementById("chatFeedback");
    const botao = document.getElementById("chatEnviar");

    const meta = document.querySelector(
        'meta[name="csrf-token"]'
    );

    const tokenCSRF = meta
        ? meta.getAttribute("content")
        : "";

    let ultimoId = 0;
    let carregando = false;
    let ciclos = 0;

    // =====================================
    // CRIAR ELEMENTO DE MENSAGEM
    // =====================================

    function criarElementoMensagem(mensagem) {

        const artigo = document.createElement("article");
        artigo.className = "chat-message";

        const cabecalho = document.createElement("div");
        cabecalho.className = "chat-message-meta";

        const autor = document.createElement("strong");
        autor.textContent = "ANONIMO";

        const data = document.createElement("time");
        data.textContent = mensagem.data || "";

        const texto = document.createElement("p");
        texto.className = "chat-message-text";

        // textContent impede a interpretacao de HTML
        texto.textContent = mensagem.conteudo;

        cabecalho.appendChild(autor);
        cabecalho.appendChild(data);

        artigo.appendChild(cabecalho);
        artigo.appendChild(texto);

        return artigo;
    }

    // =====================================
    // CONSULTAR MENSAGENS
    // =====================================

    async function carregarMensagens(completo = false) {

        if (carregando) {
            return;
        }

        carregando = true;

        const pertoDoFim =
            lista.scrollHeight -
            lista.scrollTop -
            lista.clientHeight < 100;

        try {

            const depois = completo ? 0 : ultimoId;

            const resposta = await fetch(
                "api/chat.php?depois=" + depois,
                {
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );

            if (resposta.status === 401) {
                window.location.href = "auth/login.php";
                return;
            }

            const dados = await resposta.json();

            if (!resposta.ok || !dados.ok) {
                throw new Error(
                    dados.erro || "Erro ao carregar mensagens."
                );
            }

            const mensagens = dados.mensagens || [];

            if (completo) {

                const fragmento =
                    document.createDocumentFragment();

                mensagens.forEach(function (mensagem) {

                    fragmento.appendChild(
                        criarElementoMensagem(mensagem)
                    );
                });

                lista.replaceChildren(fragmento);

                ultimoId = mensagens.length > 0
                    ? Number(
                        mensagens[mensagens.length - 1].id
                    )
                    : 0;

            } else {

                mensagens.forEach(function (mensagem) {

                    const id = Number(mensagem.id);

                    if (id > ultimoId) {

                        lista.appendChild(
                            criarElementoMensagem(mensagem)
                        );

                        ultimoId = id;
                    }
                });
            }

            if (lista.children.length === 0) {

                const vazio = document.createElement("p");

                vazio.className = "chat-placeholder";

                vazio.textContent =
                    "Ainda nao existem mensagens. " +
                    "Inicia a conversa!";

                lista.appendChild(vazio);

            } else if (
                lista.children.length > 1 &&
                lista.querySelector(".chat-placeholder")
            ) {

                lista.querySelector(
                    ".chat-placeholder"
                ).remove();
            }

            if (pertoDoFim) {
                lista.scrollTop = lista.scrollHeight;
            }

        } catch (erro) {

            feedback.textContent =
                erro.message ||
                "Nao foi possivel actualizar o chat.";

        } finally {
            carregando = false;
        }
    }

    // =====================================
    // ENVIAR MENSAGEM
    // =====================================

    formulario.addEventListener(
        "submit",
        async function (evento) {

            evento.preventDefault();

            const conteudo = campo.value.trim();

            if (!conteudo) {
                feedback.textContent =
                    "Escreve uma mensagem.";
                return;
            }

            botao.disabled = true;
            feedback.textContent = "A enviar...";

            try {

                const resposta = await fetch(
                    "api/chat.php",
                    {
                        method: "POST",

                        credentials: "same-origin",

                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-Token": tokenCSRF
                        },

                        body: JSON.stringify({
                            conteudo: conteudo
                        })
                    }
                );

                if (resposta.status === 401) {
                    window.location.href = "auth/login.php";
                    return;
                }

                const dados = await resposta.json();

                if (!resposta.ok || !dados.ok) {
                    throw new Error(
                        dados.erro ||
                        "Nao foi possivel enviar a mensagem."
                    );
                }

                campo.value = "";

                feedback.textContent =
                    "Mensagem enviada com sucesso.";

                await carregarMensagens();

                lista.scrollTop = lista.scrollHeight;

            } catch (erro) {

                feedback.textContent =
                    erro.message ||
                    "Erro ao enviar a mensagem.";

            } finally {
                botao.disabled = false;
            }
        }
    );

    // =====================================
    // ACTUALIZACAO PERIODICA
    // =====================================

    carregarMensagens(true);

    window.setInterval(function () {

        ciclos++;

        // Recarregar a lista completa de tempos
        // em tempos para reflectir eliminacoes
        // realizadas pelos moderadores.
        const completo = ciclos % 6 === 0;

        carregarMensagens(completo);

    }, 4000);

});