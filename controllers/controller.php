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
 * @param string|null $passkey Key to be hashed into DB
 * @param string $expires Number of hours message self destructs after creation
 * @param string $message Content of letter
 * @return InternalMessage - Internal messaging object
 */
function address_envelope(
  string|NULL $writer,
  string|NULL $writer_email,
  string|NULL $reader,
  string|NULL $reader_email,
  string|NULL $passkey,
  string $expires,
  string $message,
): InternalMessage {
  $uuid = uuid_generator();
  $result = envelope_to_database(
    uuid: $uuid,
    writer: $writer,
    reader: $reader,
    writer_email: $writer_email,
    reader_email: $reader_email,
    passkey: $passkey,
    expires: $expires,
    message: $message,
  );
  return new InternalMessage($result->success, $result->success ? $uuid : $result->message);
}

/**
 * Retrieves envelope from database
 * @param string $uuid UUID of envelope
 * @return InternalMessage - Internal messaging object
 */
function get_envelope(string $uuid): InternalMessage
{
  $result = check_if_envelope_exists($uuid);
  return new InternalMessage($result->success, $result->message, $result->data ?? null);
}

/**
 * Unseals envelope
 * @param string $uuid UUID of envelope
 * @return InternalMessage - Internal messaging object
 */
function get_letter(string $uuid): InternalMessage
{
  $result = unseal_envelope($uuid);
  return new InternalMessage($result->success, $result->message, $result->data ?? null, $result->code);
}
