<?php

declare(strict_types=1);

namespace LLTool\Install;

use PDO;
use PDOException;

final class  DatabaseConnectionTester
{
    public static function test(array $data):array
    {
        try {
            $dsn = match ($data['driver']) {
                 'pgsql' => sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $data['host'],
                    $data['port'],
                    $data['database']
                 ),
                 'mysql' => sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $data['host'],
                    $data['port'],
                    $data['database']
                    ),
                default => throw new \RuntimeException('Unsupported driver'),
            }; 

            new PDO(
                $dsn,
                $data['username'],
                $data['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3,
                ]
            );


            return ['ok' => true];

        } catch (PDOException $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}