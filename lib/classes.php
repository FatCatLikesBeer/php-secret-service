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

class UnsafeCrypto
{
  const METHOD = 'aes-256-ctr';

  /**
   * Encryption Method
   *
   * @param string $message Message to be encrypted
   * @param string $key Encryption key
   * @return string
   */
  public static function encrypt(string $message, string $key)
  {
    $nonce_size = openssl_cipher_iv_length(self::METHOD);
    $nonce = openssl_random_pseudo_bytes($nonce_size);

    $cipher_text = openssl_encrypt(
      $message,
      self::METHOD,
      $key,
      OPENSSL_RAW_DATA,
      $nonce,
    );

    return base64_encode($nonce . $cipher_text);
  }

  /**
   * Decryption Method
   *
   * @param string $message Encrypted message to be decrypted
   * @param string $key Encryption key
   * @return string
   */
  public static function decrypt(string $message, string $key)
  {
    $message = base64_decode($message, true);
    if ($message === false) {
      throw new Exception("Encryption failure");
    }

    $nonce_size = openssl_cipher_iv_length(self::METHOD);
    $nonce = mb_substr($message, 0, $nonce_size, "8bit");
    $cypher_text = mb_substr($message, $nonce_size, null, "8bit");

    $plain_text = openssl_decrypt(
      $cypher_text,
      self::METHOD,
      $key,
      OPENSSL_RAW_DATA,
      $nonce,
    );

    return $plain_text;
  }
  // Thank you biziclop
  // https://stackoverflow.com/a/30189841
}
