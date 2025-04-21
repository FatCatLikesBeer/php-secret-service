<?php

declare(strict_types=1);

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
): InternalMessage {
  $uuid = uuid_generator();
  $result = envelope_create($uuid, $writer, $writer_email, $reader, $reader_email, $expires, $message);
  if ($result->success) {
    return new InternalMessage(true, $uuid);
  } else {
    return new InternalMessage(false, $result->message);
  }
}

function get_message(): void
{
  echo (uuid_generator() . "\n");
}
