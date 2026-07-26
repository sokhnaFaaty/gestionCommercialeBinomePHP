<?php
namespace App\Core;

/**
 * Classe mère de tous les modèles.
 *
 * Elle fournit trois raccourcis (executeSelect, executeSelectOne,
 * executeUpdate) qui évitent de réécrire à chaque requête la même suite
 * prepare() / execute() / fetch(). Les modèles n'écrivent plus que le SQL.
 */
 class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ---------------------------------------------------------------------
    // Raccourcis d'exécution des requêtes
    // ---------------------------------------------------------------------

    /**
     * Requête de lecture qui renvoie PLUSIEURS lignes (une liste).
     *
     * Les valeurs passées dans $params ne sont jamais collées dans le SQL :
     * elles sont envoyées séparément à PostgreSQL. C'est ce qui protège des
     * injections SQL.
     *
     * @param string $sql    Le SQL, avec des ? ou des :noms à la place des valeurs
     * @param array  $params Les valeurs correspondantes
     */
    protected function executeSelect(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Requête de lecture qui renvoie UNE SEULE ligne.
     * Renvoie false si aucune ligne ne correspond.
     */
    protected function executeSelectOne(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch();
    }

    /**
     * Requête d'écriture : INSERT, UPDATE ou DELETE.
     * Renvoie true si la requête s'est bien exécutée.
     */
    protected function executeUpdate(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    // ---------------------------------------------------------------------
    // Opérations de base, disponibles dans tous les modèles
    // ---------------------------------------------------------------------

    public function all() {
        return $this->executeSelect("SELECT * FROM {$this->table}");
    }

    public function find($id) {
        return $this->executeSelectOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function delete($id) {
        return $this->executeUpdate("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
