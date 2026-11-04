import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.util.*;

/**
 * Moderação automática (slide 7). Verificações por ordem; a primeira que falha decide.
 *   1. Comprimento mínimo           -> "apenas texto longo e médio"
 *   2. Lista de termos proibidos     -> discurso de ódio genérico / conteúdo ilegal
 *   3. Spam: links em excesso, caracteres repetidos, flood de maiúsculas
 *   4. Duplicado: hash SHA-256 do texto normalizado num HashMap (copiar-colar em massa)
 * Se passar tudo, devolve APROVADO; posts aprovados mas rasos são tratados pelo RankingFeed.
 */
public class Moderacao {

    public enum Resultado { APROVADO, CURTO_DEMAIS, PROIBIDO, SPAM, DUPLICADO }

    public static final int MIN_PALAVRAS = 25;
    private static final int MAX_LINKS = 2;
    private static final int MAX_DUPLICADOS = 1;

    // Placeholder: em produção vem de uma tabela PostgreSQL mantida pelos moderadores.
    private final Set<String> proibidos = new HashSet<>(Arrays.asList(
            "termo_odio_1", "termo_odio_2", "venda_ilegal"));

    private final Map<String, Integer> hashes = new HashMap<>();

    public Resultado avaliar(String texto) {
        List<String> tokens = DensidadeSemantica.tokenizar(texto);

        if (tokens.size() < MIN_PALAVRAS) return Resultado.CURTO_DEMAIS;

        for (String t : tokens) if (proibidos.contains(t)) return Resultado.PROIBIDO;

        int links = texto.split("https?://", -1).length - 1;
        if (links > MAX_LINKS) return Resultado.SPAM;
        if (texto.matches("(?s).*(.)\\1{7,}.*")) return Resultado.SPAM;   // 8+ caracteres iguais seguidos

        String h = sha256(String.join(" ", tokens));
        int vezes = hashes.merge(h, 1, Integer::sum);
        if (vezes > MAX_DUPLICADOS) return Resultado.DUPLICADO;

        return Resultado.APROVADO;
    }

    static String sha256(String s) {
        try {
            byte[] d = MessageDigest.getInstance("SHA-256").digest(s.getBytes(StandardCharsets.UTF_8));
            StringBuilder sb = new StringBuilder();
            for (byte b : d) sb.append(String.format("%02x", b));
            return sb.toString();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}
