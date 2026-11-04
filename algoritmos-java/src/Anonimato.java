import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.SecureRandom;
import java.util.*;

/**
 * Anonimato total (slides 2 e 4).
 *  - gerarUsername(): adjetivo + substantivo (vocabulário existencialista) + 4 dígitos,
 *                     a partir de SecureRandom; reintenta se já existir.
 *  - avatar():        identicon 5x5 simétrico derivado do SHA-256 do username.
 *                     Determinístico, não usa foto nem dado real.
 *  - hashSenha():     a palavra-passe nunca é guardada em claro (SHA-256 + sal aqui;
 *                     em produção usar BCrypt/Argon2 via Spring Security).
 * Nenhum nome, email ou IP é associado ao username.
 */
public class Anonimato {

    private static final String[] ADJ = {"silencioso", "subterraneo", "absurdo", "insone",
            "lucido", "errante", "ironico", "sombrio", "estrangeiro", "inquieto"};
    private static final String[] SUBST = {"rato", "inseto", "sisifo", "escrivao",
            "andarilho", "corvo", "eco", "abismo", "porao", "vulto"};

    private final SecureRandom rnd = new SecureRandom();
    private final Set<String> usados = new HashSet<>();

    public String gerarUsername() {
        String u;
        do {
            u = ADJ[rnd.nextInt(ADJ.length)] + "_" + SUBST[rnd.nextInt(SUBST.length)]
                    + "_" + String.format("%04d", rnd.nextInt(10000));
        } while (!usados.add(u));
        return u;
    }

    /** Identicon 5x5: colunas 0-2 vêm dos bits do hash, colunas 3-4 espelham 1-0. */
    public static String avatar(String username) {
        byte[] h = sha256(username);
        boolean[][] m = new boolean[5][5];
        int bit = 0;
        for (int lin = 0; lin < 5; lin++)
            for (int col = 0; col < 3; col++, bit++) {
                boolean on = ((h[bit / 8] >> (bit % 8)) & 1) == 1;
                m[lin][col] = on;
                m[lin][4 - col] = on;
            }
        StringBuilder sb = new StringBuilder();
        for (boolean[] linha : m) {
            for (boolean c : linha) sb.append(c ? "██" : "  ");
            sb.append('\n');
        }
        return sb.toString();
    }

    public static String hashSenha(String senha, byte[] sal) {
        byte[] s = senha.getBytes(StandardCharsets.UTF_8);
        byte[] tudo = new byte[sal.length + s.length];
        System.arraycopy(sal, 0, tudo, 0, sal.length);
        System.arraycopy(s, 0, tudo, sal.length, s.length);
        return HexFormat.of().formatHex(sha256(tudo));
    }

    private static byte[] sha256(String s) { return sha256(s.getBytes(StandardCharsets.UTF_8)); }

    private static byte[] sha256(byte[] b) {
        try {
            return MessageDigest.getInstance("SHA-256").digest(b);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}
