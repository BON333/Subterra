/**
 * Entidade Post: apenas texto, autor anónimo, upvotes de profundidade.
 */
public class Post {
    private final int id;
    private final String autor;          // username anónimo
    private final String texto;
    private final long criadoEm;         // epoch em segundos
    private int upvotesProfundidade;

    // calculados pelos algoritmos
    private double densidade;            // 0..1  (DensidadeSemantica)
    private boolean introspectivo;       // lado do min-cut (MaxFlow)
    private double score;                // RankingFeed

    public Post(int id, String autor, String texto, long criadoEm) {
        this.id = id;
        this.autor = autor;
        this.texto = texto;
        this.criadoEm = criadoEm;
    }

    public int getId() { return id; }
    public String getAutor() { return autor; }
    public String getTexto() { return texto; }
    public long getCriadoEm() { return criadoEm; }
    public int getUpvotesProfundidade() { return upvotesProfundidade; }
    public void upvoteProfundidade() { upvotesProfundidade++; }

    public double getDensidade() { return densidade; }
    public void setDensidade(double d) { densidade = d; }
    public boolean isIntrospectivo() { return introspectivo; }
    public void setIntrospectivo(boolean b) { introspectivo = b; }
    public double getScore() { return score; }
    public void setScore(double s) { score = s; }

    @Override
    public String toString() {
        String resumo = texto.length() > 50 ? texto.substring(0, 50) + "..." : texto;
        return String.format("#%d [%s] dens=%.2f up=%d score=%.3f %s | %s",
                id, autor, densidade, upvotesProfundidade, score,
                introspectivo ? "SUBTERRANEO" : "superficie", resumo);
    }
}
