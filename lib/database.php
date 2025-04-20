<?php

declare(strict_types=1);

$db = new PDO("sqlite:" . __DIR__ . "/../models/my_base.sqlite");

$queries = [
  "test" => "SELECT * FROM test WHERE id = ?",
  "retrieve_envelope" => "// CHECK IF ENVELOP EXISTS, RETURN ",
  "open_envelope" => "// RETURN LETTER, SET ENVELOPE TO OPENED, DELETE LETTER FROM ENVELOPE",
];

$prep_statement = $db->prepare($queries["test"]); // Creates an object with a prepared statement
$prep_statement->execute(["2"]);                  // Object executes prepared statement with arguments
$result = $prep_statement->fetchAll();            // Object returns preped statement results
header("Content-Type: application/json");
echo $result[0]["message"];
