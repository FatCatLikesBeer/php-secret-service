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

/**
 * Writes message to database
 * Really just creates a UUID and passes everything forward
 * @param string|null $writer Name of the writer of letter
 * @param string|null $writer_email Email of writer of letter
 * @param string|null $reader Name of reader of letter
 * @param string|null $reader_email Email of reader of letter
 * @param string $expires Number of hours message self destructs after creation
 * @param string $message Content of letter
 * @return InternalMessage - Internal messaging object
 */
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
  return new InternalMessage($result->success, $result->success ? $uuid : $result->message);
}

/**
 * Retrieves message from database
 */
function get_message(string $uuid): string
{
  return "Message {$uuid}";
}
