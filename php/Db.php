<?php
namespace Game;

use \PDO;

class Db
{
    public $db;
    public $lastId;

    /**
     * Db constructor.
     * @param $credentials
     */
    public function __construct($credentials)
    {
        $this->db = new PDO(
            "mysql:host={$credentials['host']};dbname={$credentials['db_name']};charset=utf8",
            $credentials['username'], $credentials['password']
        );
    }

    /**
     * execute the query with params and returns the result
     * @param $query
     * @param $params
     * @return array|null
     */
    public function exec($query, $params = [])
    {
        try
        {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            $result       = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->lastId = $this->db->lastInsertId();

            return $result;

        } catch (\PDOException $ex)
        {
            echo $ex->getMessage();
        }

        return null;
    }
}