<?php

declare(strict_types=1);

include("./template/top.html");
include("./router.php");

$myVar = "It works!";

get("/", function () {
  include("./template/test.html");
});

get("/test", function () {
  $name = $_GET["name"] ?? null;
  $age = $_GET["age"] ?? null;
  $person = new Persons($name, $age);
  $myVar = "Generic variable";
?>
  <p>
    <?php echo $myVar; ?>
  </p>
  <p>
    <?php echo "Name: {$person->getName()}"; ?>
  </p>
  <p>
    <?php echo "Age: {$person->getAge()}"; ?>
  </p>
<?php
});

/**
 * I'm learning how to work with classes here
 *
 * @param string | null $name The name of the person
 * @param string $age The age of the person
 * @return string person object
 */
class Persons
{
  private string $name;
  private float $age;
  public function __construct(string | null $name, string | null $age)
  {
    if ($name != null) {
      $this->name = $name;
    } else {
      $this->name = "J. Doe";
    }

    if ($age != null) {
      $this->age = intval($age);
    } else {
      $this->age = 99;
    }
  }

  public function getAge(): string
  {
    return "{$this->age}";
  }

  public function getName(): string
  {
    return $this->name;
  }
}

include("./template/bottom.html");
