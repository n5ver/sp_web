<?php
/**
 * Created by PhpStorm.
 * User: n5ver
 * Date: 29.11.2016
 * Time: 18:56
 */

namespace Sp\Models;
use PDO;

/**
 * Class Model
 * @package Sp\Models
 * Rodičovská třída pro modely.
 */
class Model
{
    /**
     * @var PDO - spojení do databáze
     */
    protected $db;

    /**
     * Konstruktor, který nastaví spojení.
     */
    public function __construct()
    {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_DATABASE.";charset=".DB_CHARSET."";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->db = new PDO($dsn, DB_USER, DB_PASS, $opt);
    }

    /**
     * Destruktor
     */
    public function __destruct()
    {
        unset($this->db);
    }
}