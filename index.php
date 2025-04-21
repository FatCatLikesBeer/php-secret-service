<?php

declare(strict_types=1);

include('./router.php');
include('./controllers/controller.php');
include('./lib/classes.php');
include('./lib/uuid_generator.php');
include('./lib/database.php');

$writer = $_GET["writer"] ?? NULL;
$reader = $_GET["reader"] ?? NULL;
$writer_email = $_GET["writer_email"] ?? NULL;
$reader_email = $_GET["reader_email"] ?? NULL;
$expires = $_GET["expires"] ?? "24";
$message = $_GET["message"] ?? NULL;

// API Routing
post('/api/v0/messages', function () use (
  $writer,
  $reader,
  $writer_email,
  $reader_email,
  $expires,
  $message
) {
  try {
    // Following four are validation
    if (is_null($message)) {
      throw new Exception("Message required.", 400);
    }

    if (intval($expires) < 1) {
      throw new Exception("Expiration length too small.", 400);
    }

    if (!is_null($writer_email)) {
      if (!filter_var($writer_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Sender email is invalid.", 400);
      }
    }

    if (!is_null($reader_email)) {
      if (!filter_var($reader_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Recipient email is invalid.", 400);
      }
    }

    // Write to DB
    $result = create_message($writer, $writer_email, $reader, $reader_email, $expires, $message);

    if (!$result->success) {
      throw new Exception("Database Error, potential ID conflict, please try again.", 500);
    }

    $data = [
      "writer" => $writer,
      "reader" => $reader,
      'writer_email' => $writer_email,
      'reader_email' => $reader_email,
      'expires' => $expires,
      "uuid" => $result->message
    ];

    new Response("Message Saved!", true, 200, $data)->sendJSON();
  } catch (Exception $e) {
    new Response($e->getMessage(), false, $e->getCode())->sendJSON();
  }
});

get('/api/v0/messages/$uuid', function ($uuid) {
  get_message($uuid);
});

get('/api/v0', function () {
  route_not_used();
});
