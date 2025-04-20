<?php

declare(strict_types=1);

include(__DIR__ . "/../lib/response.php");
include(__DIR__ . "/../lib/uuid_generator.php");

function echo_slug($slug)
{
  echo new Response($slug)->sendJSON();
}

function route_not_used()
{
  echo new Response("Endpoint not in use")->sendJSON();
}

function get_message($uuid)
{
  echo uuid_generator() . "\n";
  include(__DIR__ . "/../lib/database.php");
}
