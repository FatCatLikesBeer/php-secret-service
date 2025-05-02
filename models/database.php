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
      passkey_hash TEXT DEFAULT NULL,
      letter TEXT
    );',
  "test" => "SELECT * FROM test WHERE id = ?",
  "check_if_envelope_exists" => "SELECT created_at, opened, expired, reader, passkey_hash, writer FROM envelopes WHERE uuid = ?",
  "return_all_values" => "SELECT * FROM envelopes WHERE uuid = ?",
  "unseal_envelope" => "UPDATE envelopes SET opened = ?, letter = null WHERE uuid = ?",
  "expire_envelopes" => "UPDATE envelopes SET expired = 1, letter = null WHERE expires < ?",
  "create_envelope" => "
    INSERT INTO envelopes
    (uuid, writer, writer_email, reader, reader_email, created_at, expires, passkey_hash, letter)
    values (?, ?, ?, ?, ?, ?, ?, ?, ?)",
];

$visitor_queries = [
  "create_table" => '
  CREATE TABLE IF NOT EXISTS visits(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    count INTEGER DEFAULT 0
    );',
  "create_first_row" => "INSERT OR IGNORE INTO visits (id) values(1);",
  "update_count" => "UPDATE visits SET count = count + 1 WHERE id = 1 RETURNING count;",
  "get_count" => "SELECT count FROM visits WHERE id = 1;",
];

// Standard DB operations
(function ($db, $visitor_queries, $queries) {
  $db->exec($visitor_queries["create_table"]);
  $db->exec($visitor_queries["create_first_row"]);
  $db->exec($queries["create_table"]);
  $db->prepare($queries["expire_envelopes"])->execute([time()]);
})($db, $visitor_queries, $queries);

/**
 * Create an envelope to database
 * @param string $uuid The UUID
 * @param string|null $writer Name of message writer
 * @param string|null $writer_email Email of message writer
 * @param string|null $reader Name of message recipient
 * @param string|null $reader_email Email of message recipient
 * @param string|null $passkey Key to be hashed into DB
 * @param string $expires Hours until message expires
 * @param string $message Message content
 */
function envelope_to_database(
  string $uuid,
  string|NULL $writer,
  string|NULL $writer_email,
  string|NULL $reader,
  string|NULL $reader_email,
  string|NULL $passkey,
  string $expires,
  string $message,
): InternalMessage {
  global $db;
  global $queries;
  try {
    [$letter, $key] = UnsafeCrypto::encrypt($message);
    $passkey_hash = !is_null($passkey) ? hash("sha256", $passkey) : $passkey;
    $created_at = time();
    $expires_at = $created_at + (intval($expires) * 60 * 60);
    $stmt = $db->prepare($queries["create_envelope"]);
    $stmt->execute([$uuid, $writer, $writer_email, $reader, $reader_email, $created_at, $expires_at, $passkey_hash, $letter]);
    $stmt->fetch();
    return new InternalMessage(true, "Message saved!", ["uuid" => "{$uuid}:{$key}"]);
  } catch (Exception $err) {
    return new InternalMessage(false, "Database Error: {$err}");
  }
}

/**
 * Checks if envelope with uuid exists
 * @param string $uuid_key UUID & key of envelope
 * @return InternalMessage
 */
function check_if_envelope_exists(
  string $uuid_key,
): InternalMessage {
  global $db;
  global $queries;
  try {
    [$uuid] = explode(":", $uuid_key);
    $stmt = $db->prepare($queries["check_if_envelope_exists"]);
    $stmt->execute([$uuid]);
    $column = $stmt->fetch();
    if (!is_array($column)) {
      throw new Exception("Envelope not found.");
    }
    $data = [
      "created_at" => $column["created_at"],
      "opened" => intval($column["opened"]),
      "expired" => $column["expired"] == "0" ? false : true,
      "locked" => !is_null($column["passkey_hash"]),
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
 * @param string $uuid_key UUID & key of envelope
 * @param string|null $passkey Key to retrieve content (letter)
 * @return InternalMessage
 */
function unseal_envelope(string $uuid_key, string|null $passkey): InternalMessage
{
  $message = new InternalMessage(false, "Unknown Server Error", code: 500);
  global $db;
  global $queries;
  try {
    // Separate uuid & key
    [$uuid, $key] = explode(":", $uuid_key);

    // Check if envelope exists
    $stmt_check = $db->prepare($queries["check_if_envelope_exists"]);
    $stmt_check->execute([$uuid]);
    $check_if_exists = $stmt_check->fetch();

    // Verify envelope exists
    if (!is_array($check_if_exists)) {
      throw new Exception("Envelope not found.", 500);
    }

    $message->data = [
      "opened" => intval($check_if_exists["opened"]),
      "locked" => !is_null($check_if_exists["passkey_hash"]),
      "expired" => $check_if_exists["expired"] == "0" ? false : true,
      "reader" => $check_if_exists["reader"],
      "writer" => $check_if_exists["writer"],
      "created_at" => $check_if_exists["created_at"],
      "letter" => null,
    ];

    // Verifiy expired
    if ($check_if_exists["expired"] != "0") {
      throw new Exception("Envelope expired.", 410);
    }

    // Verify opened
    if ($check_if_exists["opened"] != "0") {
      throw new Exception("Envelope already opened.", 410);
    }

    // Validate passkey
    if ($passkey) {
      if ($check_if_exists["passkey_hash"] != hash("sha256", $passkey)) {
        throw new Exception("Key invalid.", 401);
      }
    }

    // Retrieve Data
    $stmt = $db->prepare($queries["return_all_values"]);
    $stmt->execute([$uuid]);
    $columns = $stmt->fetch();
    $encrypted_letter = $columns["letter"];
    $letter = UnsafeCrypto::decrypt($encrypted_letter, $key);
    $message->data["letter"] = $letter;
    $message->message = "Letter content";
    $message->code = 200;
    $message->success = true;

    // Set letter as opened
    $db->prepare($queries["unseal_envelope"])->execute([time(), $uuid]);
  } catch (Exception $err) {
    $message->message = $err->getMessage();
    $message->code = $err->getCode();
  }
  return $message;
}

$visitor_increment = function () use ($db, $visitor_queries): int {
  $stmt = $db->prepare($visitor_queries["update_count"]);
  $stmt->execute();
  $columns = $stmt->fetch();
  $result = $columns["count"];
  return $result;
};

function visitor_count(): int
{
  global $db;
  global $visitor_queries;
  $stmt = $db->prepare($visitor_queries["get_count"]);
  $stmt->execute();
  $columns = $stmt->fetch();
  return $columns["count"];
}
