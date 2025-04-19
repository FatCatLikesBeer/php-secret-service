<?php

declare(strict_types=1);

$queries = [
  "get_message" => "SELECT message FROM test WHERE id = 1",
];

$db = new PDO("sqlite:" . __DIR__ . "/my_base.sqlite");
$query = $db->query($queries["get_message"]);
$result = $query->fetchAll();
header("Content-Type: application/json");
echo $result[0]["message"];
