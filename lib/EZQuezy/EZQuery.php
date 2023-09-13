<?php

require_once __DIR__ . "/config.php";

class EZQuery
{
    private PDO $pdo;
    private bool $debug = false;

    public function __construct()
    {
        /* Connection to the db */
        $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB_NAME, USERNAME, PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
    }

    /* Debug */
    public function debug(?bool $debug = true): void
    {
        $this->debug = $debug;
    }

    /* Select */
    public function executeSelect(string $query, ...$args): array
    {
        // Replace % ? by args
        $queryArgs = $this->convertArgs($query, $args);
        $query = $queryArgs['query'];
        $args = $queryArgs['args'];

        // Debug
        if ($this->debug) $this->displayQuery($query, $args);

        // Prepare query
        $stmt = $this->pdo->prepare($query);

        // Bind Args
        foreach ($args as $i => $arg)
            $stmt->bindValue($i + 1, $arg);

        // Execute query
        $stmt->execute();

        // Return results
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function executeEdit(string $query, ...$args): int
    {
        // Replace % ? by args
        $queryArgs = $this->convertArgs($query, $args);
        $query = $queryArgs['query'];
        $args = $queryArgs['args'];

        // Debug
        if ($this->debug) $this->displayQuery($query, $args);

        // Prepare query
        $stmt = $this->pdo->prepare($query);

        // Bind Args
        foreach ($args as $i => $arg)
            $stmt->bindValue($i + 1, $arg);

        // Execute query
        $stmt->execute();

        // Return num rows affected
        return $stmt->rowCount();
    }

    /* Display query */
    private function displayQuery(string $query, array $args): void
    {
        echo "<br><strong>$query<br><pre><i>";
        print_r($args);
        echo "</i></pre></strong><br>";
    }

    private function convertArgs(string $query, array $args): array
    {
        $argsToBind = [];

        // Split into a table the string $where
        $split = str_split($query);

        // replace % by the value
        // Stock the value of ? into $this->args
        $count = count($split);
        for ($i = $j = 0; $i < $count; $i++) {
            switch ($split[$i]) {
                case '\\': // Skip the next char
                    $i++;
                    break;
                case '%': // Replace
                    $split[$i] = $args[$j++];
                    break;
                case '?': // Bind
                    $argsToBind[] = $args[$j++];
                    break;
            }
        }

        return ['query' => join('', $split), 'args' => $argsToBind];
    }
}
