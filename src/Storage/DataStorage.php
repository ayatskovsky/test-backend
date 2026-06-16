<?php

namespace App\Storage;

use App\Model;

// TODO: Replace raw SQL string concatenation with prepared statements.
// TODO: Move queries to repositories TaskRepository and ProjectRepository
class DataStorage
{
    // TODO: Avoid public DB connection property; hide PDO behind a private dependency to preserve encapsulation.
    // TODO: Do not create PDO directly inside this class; inject the connection to improve testability and follow dependency inversion.
    // TODO: Move DB credentials/config out of source code; hardcoded connection details are not maintainable or secure.
    // TODO: Configure PDO error mode explicitly, otherwise DB failures may be harder to diagnose consistently.
    /**
     * @var \PDO 
     */
    public $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('mysql:dbname=task_tracker;host=127.0.0.1', 'user');
    }


    // TODO: Add scalar type hint for $projectId to match the PHPDoc contract.
    // TODO: Use a prepared statement in getProjectById() for consistency with safe query practices, even if the value is cast to int.
    // TODO: Replace SELECT * with an explicit column list to reduce coupling to schema changes.
    // TODO: Consider separating data access from model hydration; this class currently handles both persistence and object creation.
    /**
     * @param int $projectId
     * @throws Model\NotFoundException
     */
    public function getProjectById($projectId)
    {
        $stmt = $this->pdo->query('SELECT * FROM project WHERE id = ' . (int) $projectId);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new Model\Project($row);
        }

        throw new Model\NotFoundException();
    }


    // TODO: Keep parameter naming consistent; use one convention instead of mixing $projectId and $project_id.
    // TODO: Add scalar type hints for $limit and $offset to match the PHPDoc and clarify the method contract.
    // TODO: Do not use query() with placeholders in getTasksByProjectId(); this is an incorrect PDO usage pattern.
    // TODO: Use a prepared statement for pagination inputs instead of mixing interpolated SQL and execute().
    // TODO: Validate/sanitize limit and offset before querying to avoid invalid paging behavior.
    // TODO: Define an explicit ordering for paginated results; pagination without ORDER BY is unstable.
    // TODO: Specify fetch mode explicitly in fetchAll() for consistency and readability.
    // TODO: Consider returning a dedicated collection or consistent serializer strategy; task hydration is coupled directly to DB rows.
    /**
     * @param int $project_id
     * @param int $limit
     * @param int $offset
     */
    public function getTasksByProjectId(int $project_id, $limit, $offset)
    {
        $stmt = $this->pdo->query("SELECT * FROM task WHERE project_id = $project_id LIMIT ?, ?");
        $stmt->execute([$limit, $offset]);

        $tasks = [];
        foreach ($stmt->fetchAll() as $row) {
            $tasks[] = new Model\Task($row);
        }

        return $tasks;
    }

    // TODO: Do not build SQL with raw $data; use a prepared statement to prevent SQL injection.
    // TODO: Whitelist allowed task fields instead of using array_keys($data) directly.
    // TODO: Validate and normalize input before persisting it.
    // TODO: Do not quote strings manually with double quotes; let the database driver bind parameters.
    // TODO: Replace SELECT MAX(id) with a safe last-insert-id mechanism.
    // TODO: Type-hint $projectId in the method signature for consistency with the PHPDoc.
    // TODO: Consider returning the persisted task from the database instead of trusting the input array.
    // TODO: Reduce method responsibility; split mapping, persistence, and hydration into separate steps.
    // TODO: Ensure database errors are handled or allowed to bubble up consistently.
    /**
     * @param array $data
     * @param int $projectId
     * @return Model\Task
     */
    public function createTask(array $data, $projectId)
    {
        $data['project_id'] = $projectId;

        $fields = implode(',', array_keys($data));
        $values = implode(',', array_map(function ($v) {
            return is_string($v) ? '"' . $v . '"' : $v;
        }, $data));

        $this->pdo->query("INSERT INTO task ($fields) VALUES ($values)");
        $data['id'] = $this->pdo->query('SELECT MAX(id) FROM task')->fetchColumn();

        return new Model\Task($data);
    }
}
