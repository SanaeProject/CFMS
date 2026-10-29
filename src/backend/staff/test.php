<?php

header("Content-Type: application/json; charset=UTF-8");

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

try {

    $db = Lib\DB::connect();

    $stmt = $db->exec(
        "
        SELECT
            id
        FROM
            staff
        WHERE
            name = :name
        LIMIT 1
        ",
        [
            ':name' => 'test'
        ]
    );

    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {

        echo json_encode([
            "success" => false,
            "message" => "既に存在します。",
            "data" => []
        ]);

        exit;
    }

    $hashedPassword = password_hash(
        'abcd123',
        PASSWORD_DEFAULT
    );

    $db->exec(
        "
        INSERT INTO staff (
            name,
            password,
            role
        )
        VALUES (
            :name,
            :password,
            :role
        )
        ",
        [
            ':name' => 'test',
            ':password' => $hashedPassword,
            ':role' => 'admin'
        ]
    );

    echo json_encode([
        "success" => true,
        "message" => "テストスタッフを作成しました。",
        "data" => [
            "name" => "test",
            "password" => "abcd123",
            "role" => "admin"
        ]
    ]);

} catch (\Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
        "data" => []
    ]);
}