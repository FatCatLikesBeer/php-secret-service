<?php
include_once(__DIR__ . "/../models/database.php");
$count = $visitor_increment();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
  <link href="/css/style.css" rel="stylesheet">
  <style>
    #title-bar {
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="container">
    <div id="title-bar">
      <h1>Secret Messages</h1>
    </div>
    <div id="msg-panel">
      <textarea>
        </textarea>
      <button type="button" id="snd-button">
        Click me!
      </button>
    </div>
  </div>
</body>

<script>
  const button = document.getElementById("snd-button");
  const apiURL = "/api/v0/messages";
  const msgArea = document.getElementById("msg-area");
</script>

</html>
