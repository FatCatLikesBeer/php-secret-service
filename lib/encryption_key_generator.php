<?php

/**
 * Generates 6 character long alphanumeric key
 */
function encryption_key_generator(int $length = 6): string
{
  $options = [];
  for ($i = 0; $i < 26; $i++) {
    array_push($options, chr($i + 65));
    array_push($options, chr($i + 97));
  }
  for ($i = 0; $i < 10; $i++) {
    array_push($options, strval($i));
  }
  $result = "";
  for ($i = 0; $i < $length; $i++) {
    $selector = rand(0, count($options) - 1);
    $result .= $options[$selector];
  }
  return $result;
}
