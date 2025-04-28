<?php

declare(strict_types=1);

const SITE_NAME = "Project Flight";
const SITE_DOMAIN = "http://localhost:8000";
const SITE_TAGLINE = "Self-destructing message service";
const SITE_DESCRIPTION = "Self Destructing Message Service";
$site_domain = SITE_DOMAIN;

include_once('./router.php');

// Views Go Here
get("/", "./views/index.php");
get("/message", function () use ($site_domain) {
  header("Location: {$site_domain}");
  exit;
});
get('/message/$request_uuid', "./views/index.php");

include('./controllers/controller.php');
include('./lib/classes.php');
include('./lib/uuid_generator.php');
include('./models/database.php');

$writer = $_GET["writer"] ?? NULL;
$reader = $_GET["reader"] ?? NULL;
$writer_email = $_GET["writer_email"] ?? NULL;
$reader_email = $_GET["reader_email"] ?? NULL;
$expires = $_GET["expires"] ?? "24";
$message = $_GET["message"] ?? NULL;
$key = $_GET["key"] ?? NULL;

// Pre routing validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    if (is_null($message)) {
      throw new Exception("Message required.", 400);
    }

    if ($writer_email) {
      if (!filter_var($writer_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Writer's email is invalid.", 400);
      }
    }

    if ($reader_email) {
      if (!filter_var($reader_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Reader's email is invalid.", 400);
      }
    }

    if ($writer) {
      if (2 > strlen($writer)) {
        throw new Exception("Writer's name is too short.", 400);
      }
    }

    if ($reader) {
      if (2 > strlen($reader)) {
        throw new Exception("Reader's name is too short.", 400);
      }
    }

    if (intval($expires) > 168) {
      throw new Exception("Expiration length too large.", 400);
    }
    if (intval($expires) < 1) {
      throw new Exception("Expiration length too small.", 400);
    }
  } catch (Exception $err) {
    new Response($err->getMessage(), false, $err->getCode())->sendJSON();
    return;
  }
}

// API Routing
post('/api/v0/messages', function () use (
  $writer,
  $reader,
  $writer_email,
  $reader_email,
  $key,
  $expires,
  $message
) {
  try {
    $result = address_envelope(
      writer: $writer,
      writer_email: $writer_email,
      reader: $reader,
      reader_email: $reader_email,
      passkey: $key,
      expires: $expires,
      message: $message,
    );

    if (!$result->success) {
      throw new Exception("Database Error, potential ID conflict, please try again.", 500);
    }

    $data = [
      "writer" => $writer,
      "reader" => $reader,
      'writer_email' => $writer_email,
      'reader_email' => $reader_email,
      'expires' => intval($expires),
      "uuid" => $result->message
    ];

    new Response("Message Saved!", true, 200, $data)->sendJSON();
  } catch (Exception $err) {
    new Response($err->getMessage(), false, $err->getCode())->sendJSON();
  }
});

get('/api/v0/messages/$uuid', function ($uuid) {
  try {
    if (16 != strlen($uuid)) {
      throw new Exception("Invalid message ID", 400);
    }

    $result = get_envelope($uuid);
    if (!$result->success) {
      throw new Exception("No envelope found.", 400);
    }

    new Response("Envelope Found", true, 200, $result->data)->sendJSON();
  } catch (Exception $err) {
    new Response($err->getMessage(), false, $err->getCode())->sendJSON();
  }
});

get('/api/v0/messages/$uuid/read', function ($uuid) use ($key) {
  try {
    $result = get_letter($uuid, $key);
    if (!$result->success) {
      throw new Exception($result->message, $result->code);
    }
    $data = $result->data;
    new Response("Letter opened", true, 200, $data)->sendJSON();
  } catch (Exception $err) {
    new Response($err->getMessage(), false, $err->getCode())->sendJSON();
  }
});

get('/api/v0/visitor-count', function () {
  $count = get_visitor_count();
  new Response("Visitor Count", true, 200, ["count" => $count])->sendJSON();
});

get('/api/v0', function () {
  route_not_used();
});

get('/api', function () {
  route_not_used();
});

// 404
include_once "./views/404.php";
