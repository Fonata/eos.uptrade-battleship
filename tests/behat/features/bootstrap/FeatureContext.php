<?php

use Behat\Behat\Context\Context;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @BeforeScenario
     */
    public function cleanUpDatabase()
    {
        // The file "api/.env" contains the same configuration for the Symfony side:
        // DATABASE_URL=mysql://doctrine:hutapo@127.0.0.1:3306/eos.uptrade-coding-challenge?serverVersion=5.7

        $host = '127.0.0.1';
        $db = 'eos.uptrade-coding-challenge';
        $port = 3306;
        $user = 'doctrine';

        // The password should typically not go into the Git repo. I only add it here because this coding challenge
        // database is not ever going to contain real information.
        $pass = 'hutapo';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $opt);

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE game; TRUNCATE user');
    }
}
