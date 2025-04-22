<?php

declare(strict_types=1);

/**
 * Response Class which sends off API data
 * @method void sendJSON() Sends the response as JSON
 * @param bool $success Request was a success
 * @param string $message Message Content
 * @param int $status HTTP Status code
 * @param array|null $data Option Data content for response
 */
class Response
{
  public bool $success;
  public string $message;
  public int $status;
  public ?array $data;

  function __construct(
    string $message,
    bool $success = false,
    int $status = 500,
    ?array $data = null
  ) {
    $this->success = $success;
    $this->message = $message;
    $this->status = $status;
    if (!is_null($data)) {
      $this->data = $data;
    }
  }

  public function sendJSON(): void
  {
    $result = json_encode($this);
    header("Content-Type: application/json");
    http_response_code($this->status);
    echo $result;
  }
}

class InternalMessage
{
  public bool $success;
  public string $message;
  public ?int $code = null;
  public ?array $data;

  function __construct(
    bool $success,
    string $message,
    ?array $data = null,
    ?int $code = null,
  ) {
    $this->success = $success;
    $this->message = $message;
    if (!is_null($code)) {
      $this->code = $code;
    }
    if (!is_null($data)) {
      $this->data = $data;
    }
  }
}
