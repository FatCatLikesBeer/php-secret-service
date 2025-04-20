<?php

declare(strict_types=1);

include(__DIR__ . "/../lib/response.php");
include(__DIR__ . "/../lib/uuid_generator.php");
include(__DIR__ . "/../lib/database.php");

function echo_slug($slug)
{
  echo new Response($slug)->sendJSON();
}

function route_not_used()
{
  echo new Response("Endpoint not in use")->sendJSON();
}

function create_message(
  string|NULL $writer,
  string|NULL $writer_email,
  string|NULL $reader,
  string|NULL $reader_email,
  string $expires,
  string $message,
): string {
  $uuid = uuid_generator();
  $result = envelope_create($uuid, $writer, $writer_email, $reader, $reader_email, $expires, $message);
  return $result;
}

function get_message(): void
{
  echo uuid_generator() . "\n";
  /* include(__DIR__ . "/../lib/database.php"); */
}
