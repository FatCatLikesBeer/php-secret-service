<?php

declare(strict_types=1);

include("./router.php");

get("/", function () {
  include("./template/top.html");
?>
  <p>I'm outside of the php block</p>
<?php
  include("./template/bottom.html");
});

get("/test", function () {
  include("./template/top.html");
?>
  <p>Test function</p>
<?php
  include("./template/bottom.html");
});

any("/404", "./views/404.php");
