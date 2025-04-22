<?php

declare(strict_types=1);

$db = new PDO(dsn: "sqlite:" . __DIR__ . "/../models/my_base.sqlite");

$queries = [
  "create_table" => '
    CREATE TABLE IF NOT EXISTS envelopes(
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      uuid TEXT NOT NULL UNIQUE,
      writer TEXT,
      writer_email TEXT,
      reader TEXT,
      reader_email TEXT,
      created_at INTEGER NOT NULL,
      expires INTEGER NOT NULL,
      expired INTEGER NOT NULL DEFAULT 0,
      opened INTEGER NOT NULL DEFAULT 0,
      key_hash TEXT DEFAULT NULL,
      letter TEXT
    );',
  "test" => "SELECT * FROM test WHERE id = ?",
  "check_if_envelope_exists" => "SELECT opened, expired, reader, writer FROM envelopes WHERE uuid = ?",
  "return_all_values" => "SELECT * FROM envelopes WHERE uuid = ?",
  "unseal_envelope" => "UPDATE envelopes SET opened = ?, letter = null WHERE uuid = ?",
  "expire_envelopes" => "UPDATE envelopes SET expired = 1 WHERE expires < ?",
  "create_envelope" => "
    INSERT INTO envelopes
    (uuid, writer, writer_email, reader, reader_email, created_at, expires, letter)
    values (?, ?, ?, ?, ?, ?, ?, ?)",
];

// Standard DB operations
$db->exec($queries["create_table"]);
$db->prepare($queries["expire_envelopes"])->execute([time()]);

/**
 * Create an envelope to database
 * @param string $uuid The UUID
 * @param string|null $writer Name of message writer
 * @param string|null $writer_email Email of message writer
 * @param string|null $reader Name of message recipient
 * @param string|null $reader_email Email of message recipient
 * @param string $expires Hours until message expires
 * @param string $message Message content
 */
function envelope_to_database(
  string $uuid,
  string|NULL $writer,
  string|NULL $writer_email,
  string|NULL $reader,
  string|NULL $reader_email,
  string $expires,
  string $message,
): InternalMessage {
  try {
    global $db;
    global $queries;
    $created_at = time();
    $expires_at = $created_at + (intval($expires) * 60 * 60);
    $stmt = $db->prepare($queries["create_envelope"]);
    $stmt->execute([$uuid, $writer, $writer_email, $reader, $reader_email, $created_at, $expires_at, $message]);
    $stmt->fetch();
    return new InternalMessage(true, "Message saved!");
  } catch (Exception $err) {
    return new InternalMessage(false, "Database Error: {$err}");
  }
}

/**
 * Checks if envelope with uuid exists
 * @param string $uuid UUID of envelope
 * @return InternalMessage
 */
function check_if_envelope_exists(
  string $uuid,
): InternalMessage {
  global $db;
  global $queries;
  try {
    $stmt = $db->prepare($queries["check_if_envelope_exists"]);
    $stmt->execute([$uuid]);
    $column = $stmt->fetch();
    if (!is_array($column)) {
      throw new Exception("Envelope not found");
    }
    $data = [
      "opened" => intval($column["opened"]),
      "expired" => $column["expired"] == "0" ? false : true,
      "reader" => $column["reader"],
      "writer" => $column["writer"],
    ];
    return new InternalMessage(true, "Envelope Found.", $data);
  } catch (Exception $err) {
    return new InternalMessage(false, "Database Error: {$err}");
  }
}

/**
 * Returns contents of envelope (letter), sets opened to 1,
 * sets expired to 1, deletes content (letter).
 * @param stirng $uuid UUID of envelope
 * @return InternalMessage
 */
function unseal_envelope(string $uuid): InternalMessage
{
  $message = new InternalMessage(false, "Unknown Server Error", code: 500);
  global $db;
  global $queries;
  try {
    // Check if envelope exists
    $stmt_check = $db->prepare($queries["check_if_envelope_exists"]);
    $stmt_check->execute([$uuid]);
    $check_if_exists = $stmt_check->fetch();

    // Verifiy expired
    if ($check_if_exists["expired"] == "1") {
      throw new Exception("Message expired.", 410);
    }

    // Verify opened
    if ($check_if_exists["opened"] == "1") {
      throw new Exception("Message already opened.", 410);
    }

    // Retrieve Data
    $stmt = $db->prepare($queries["return_all_values"]);
    $stmt->execute([$uuid]);
    $columns = $stmt->fetch();
    $letter = $columns["letter"];
    $message->data = ["letter" => $letter];
    $message->success = true;
    $message->message = "Letter content";
    $message->code = 200;

    // Set letter as opened
    $db->prepare($queries["unseal_envelope"])->execute([time(), $uuid]);
  } catch (Exception $err) {
    $message->message = $err->getMessage();
    $message->success = false;
    $message->code = $err->getCode();
  }
  return $message;
}
