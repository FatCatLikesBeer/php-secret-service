<?php

declare(strict_types=1);

?>

<head>
  <link rel="stylesheet" href="css/pico.min.css" />
</head>

<?php

include("./router.php");

$myVar = "It works!";

get("/", function () {
  echo "<h1> You are here! </h1>";
});

get("/test", function () {
  $name = $_GET["name"] ?? null;
  $age = $_GET["age"] ?? null;
  $myVar = "Generic variable";
?>
  <p>
    <?php
    echo $myVar;
    ?>
  <p>
    <?php
    echo "Name {$name}";
    ?>
  <p>
  <?php
  echo "Age {$age}";
});
