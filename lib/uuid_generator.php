<?php

function digit_selector(): string
{
  $digit = rand(0, 9);
  return strval($digit);
}

/**
 * uuid_generator
 *
 * @return string 16 char long ID
 */
function uuid_generator(): string
{
  include(__DIR__ . "/../lib/words.php");
  $length = count($words);
  $head_index = rand(0, $length - 1);
  $body_index = rand(0, $length - 1);
  $tail_placement = rand(0, 2);
  $result = "";

  // Create parts of UUID
  $head = rand(0, 1) === 1 ? $words[$head_index] : strtolower($words[$head_index]);
  $body = rand(0, 1) === 1 ? $words[$body_index] : strtolower($words[$body_index]);
  $tail = "";

  // Construct Tail
  for ($i = 0; $i < 4; $i++) {
    $tail .= digit_selector();
  }

  // Arrange placement
  if (0 == $tail_placement) {
    $result = "{$head}{$body}{$tail}";
  } elseif (1 == $tail_placement) {
    $result = "{$head}{$tail}{$body}";
  } else {
    $result = "{$tail}{$head}{$body}";
  }

  return $result;
}
