<?php

declare(strict_types=1);

/**
 * @method void sendJSON() Sends the response as JSON
 */
class Response
{
  public bool $success;
  public string $message;
  public int $status;

  function __construct(string $message, bool $success = false, int $status = 500)
  {
    $this->success = $success;
    $this->message = $message;
    $this->status = $status;
  }

  public function sendJSON(): string | false
  {
    $result = json_encode($this);
    header("Content-Type: application/json");
    http_response_code($this->status);
    return $result;
  }
}
