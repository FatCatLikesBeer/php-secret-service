<?php

declare(strict_types=1);

include('./router.php');
include('./controllers/controller.php');

// API Routing
get('/api/v0', function () {
  route_not_used();
});

get('/api/v0/messages/$uuid', function ($uuid) {
  get_message($uuid);
});

post('/api/v0/messages', function () {
  $writer = $_GET["writer"] ?? NULL;
  $reader = $_GET["reader"] ?? NULL;
  $writer_email = $_GET["writer_email"] ?? NULL;
  $reader_email = $_GET["reader_email"] ?? NULL;
  $expires = $_GET["expires"] ?? "24";
  $message = $_GET["message"] ?? NULL;

  try {
    if (is_null($message)) {
      throw new Exception("Message required");
    }
    if (intval($expires) < 1) {
      throw new Exception("Expiration too small");
    }
    $result = create_message($writer, $writer_email, $reader, $reader_email, $expires, $message);
    echo new Response($result, true, 200)->sendJSON();
  } catch (Exception $e) {
    echo new Response($e->getMessage(), false, 400)->sendJSON();
  }
});
