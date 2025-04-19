<?php

declare(strict_types=1);

include "./lib/response.php";

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
  include(__DIR__ . "/database.php");
}
