<?php

function digit_selector(): string
{
  $digit = rand(0, 9);
  return strval($digit);
}

function uuid_generator(): string
{
  include(__DIR__ . "/../lib/words.php");
  $length = count($words);
  $head_index = rand(0, $length - 1);
  $body_index = rand(0, $length - 1);

  $head = rand(0, 1) === 1 ? $words[$head_index] : strtolower($words[$head_index]);
  $body = rand(0, 1) === 1 ? $words[$body_index] : strtolower($words[$body_index]);
  $tail = "";

  for ($i = 0; $i < 4; $i++) {
    $tail .= digit_selector();
  }
  return $head . $body . $tail;
}
