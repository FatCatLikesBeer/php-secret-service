<?php

declare(strict_types=1);

$db = new PDO("sqlite:" . __DIR__ . "/../models/my_base.sqlite");

$queries = [
  "test" => "SELECT * FROM test WHERE id = ?",
  "retrieve_envelope" => "// CHECK IF ENVELOP EXISTS, RETURN ",
  "open_envelope" => "// RETURN LETTER, SET ENVELOPE TO OPENED, DELETE LETTER FROM ENVELOPE",
  "create_envelope" => "
  INSERT INTO envelopes
  (uuid, writer, writer_email, reader, reader_email, created_at, expires, letter)
  values (?, ?, ?, ?, ?, ?, ?, ?)",
];

/* $prep_statement = $db->prepare($queries["test"]); // Creates an object with a prepared statement */
/* $prep_statement->execute(["2"]);                  // Object executes prepared statement with arguments */
/* $result = $prep_statement->fetchAll();            // Object returns preped statement results */

/**
 * Create an envelope to database
 *
 * @param string $uuid The UUID
 * @param string|null $writer Name of message writer
 * @param string|null $writer_email Email of message writer
 * @param string|null $reader Name of message recipient
 * @param string|null $reader_email Email of message recipient
 * @param string $expires Hours until message expires
 * @param string $message Message content
 */
function envelope_create(
  string $uuid,
  string|NULL $writer,
  string|NULL $writer_email,
  string|NULL $reader,
  string|NULL $reader_email,
  string $expires,
  string $message,
): array {
  try {
    global $db;
    global $queries;
    $created_at = time();
    $expires_at = $created_at + (intval($expires) * 60 * 60);
    $stmt = $db->prepare($queries["create_envelope"]);
    $stmt->execute([$uuid, $writer, $writer_email, $reader, $reader_email, $created_at, $expires_at, $message]);
    $stmt->fetch();
    return ["success" => true, "message" => "success"];
  } catch (Exception $e) {
    return ["success" => false, "message" => $e->getMessage()];
  }
}
