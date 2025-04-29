<?php

/**
 * Digit Selector
 * @return string Single number as string
 */
function digit_selector(): string
{
  $digit = rand(0, 9);
  return strval($digit);
}

/**
 * Capitalizes a string
 * @param string $argument Input String, in any case
 * @return string Argument but capitalized.
 */
function capitalizer(string $argument): string
{
  $local = strtolower($argument);
  return ucfirst($local);
}

/**
 * Randomly selects the case of a word
 * @param string $word Word to be modified
 * @return string Word to a different case
 */
function case_picker(string $word): string
{
  $selector = rand(0, 2);
  $result = "";
  switch ($selector) {
    case 0:
      $result = strtoupper($word);
      break;
    case 1:
      $result = strtolower($word);
      break;
    case 2:
      $result = capitalizer($word);
      break;
    default:
      $result = $word;
      break;
  }
  return $result;
}

/**
 * UUID Generator
 * Generates a UUID consisting of 2 six character long
 * words and 4 digits. Each word selected from a pool
 * of 5100 words and can come in 3 different cases. Digits
 * have 4 different possible positions.
 * @return string 16 char long UUID
 */
function uuid_generator(): string
{
  include(__DIR__ . "/../lib/words.php");
  $result = "";
  $length = count($words);
  $head_word = $words[rand(0, $length - 1)];
  $tail_word = $words[rand(0, $length - 1)];
  $structure = rand(0, 3);

  // Select and cast it
  $head = case_picker($head_word);
  $tail = case_picker($tail_word);
  $apendege = "";

  // Construct apendege
  for ($i = 0; $i < 4; $i++) {
    $apendege .= digit_selector();
  }

  // Arrange structure
  switch ($structure) {
    case 0:
      $result = "{$head}{$tail}{$apendege}";
      break;
    case 1:
      $result = "{$head}{$apendege}{$tail}";
      break;
    case 2:
      $result = "{$apendege}{$head}{$tail}";
      break;
    case 3:
      $stub = substr($apendege, 0, 2);
      $trim = substr($apendege, 2, 4);
      $result = "{$stub}{$head}{$tail}{$trim}";
    default:
      break;
  }

  return $result;
}
