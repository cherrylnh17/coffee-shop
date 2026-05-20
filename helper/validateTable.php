<?php

function validateTable($pdo, $table_code)
{
    $query = "SELECT 1 FROM `table` WHERE name = ? LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$table_code]);

    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exists) {
        header("Location: " . BASE_URL . "order/undefined-table");
        exit();
    }
}