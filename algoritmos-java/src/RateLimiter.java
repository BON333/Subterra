import java.util.*;

/**
 * Rate limiting por utilizador — TOKEN BUCKET (HashMap<username, balde>).
 *   capacidade = rajada máxima permitida
 *   taxa       = tokens repostos por segundo
 * Cada mensagem gasta 1 token; sem token -> mensagem rejeitada.
 *
 * Isto limita por UTILIZADOR na aplicação. O limite por PROCESSO (CPU/memória do serviço)
 * é feito pelo Linux com cgroups via systemd (ver deploy/subterra.service).
 */
public class RateLimiter {

    private static class Balde {
        double tokens;
        long ultimoNanos;
        Balde(double t, long n) { tokens = t; ultimoNanos = n; }
    }

    private final double capacidade;
    private final double taxaPorSegundo;
    private final Map<String, Balde> baldes = new HashMap<>();

    public RateLimiter(double capacidade, double taxaPorSegundo) {
        this.capacidade = capacidade;
        this.taxaPorSegundo = taxaPorSegundo;
    }

    public synchronized boolean permitir(String user, long agoraNanos) {
        Balde b = baldes.computeIfAbsent(user, k -> new Balde(capacidade, agoraNanos));
        double seg = (agoraNanos - b.ultimoNanos) / 1e9;
        b.tokens = Math.min(capacidade, b.tokens + seg * taxaPorSegundo);
        b.ultimoNanos = agoraNanos;
        if (b.tokens >= 1) {
            b.tokens -= 1;
            return true;
        }
        return false;
    }

    public boolean permitir(String user) { return permitir(user, System.nanoTime()); }
}
