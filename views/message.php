<?php
include_once(__DIR__ . "/../models/database.php");
$message = "Doesn't look like there's a message for you here 😩";
$from = false;
$to = false;
$created_at = "";
$expired = false;
$opened = false;
$passkey = false;

$stmt = $db->prepare("SELECT * FROM envelopes WHERE uuid = ?;");
$stmt->execute([$uuid]);
$columns = $stmt->fetch();

if ($columns) {
  // Define values
  $from = $columns["writer"] ?? $from;
  $to = $columns["reader"] ?? $to;
  $created_at = $columns["created_at"];
  $passkey = $columns["passkey_hash"] ?? $passkey;
  $expired = boolval($columns["expired"]);
  $opened = $columns["opened"];

  // Structure Display Message
  $message = "";
  $message .= $to ? "<p>To: {$to}</p>" : $to;
  $message .= $from ? "<p>From: {$from}</p>" : $from;

  // OG & TwitterCard
  $description = null;
  if ($to) {
    $description = SITE_NAME . " | Hello {$to}, someone has a message for you.";
  }
  if ($from) {
    $description = SITE_NAME . " | Hello, {$from} has a message for you.";
  }
  if ($to && $from) {
    $description = SITE_NAME . " | " . "Hello {$to}, {$from} has a message for you.";
  }
  if (!$message) {
    $message = "Someone sent you a message.";
  }
  if ($expired) {
    $message = "<span id='msg'>Message <strong>expired</strong> and <strong>never</strong> opened.</span>";
  }
  if ($opened) {
    $message = "<span id='msg'>Message <span id='opened'></span></span>";
  }
  if ($expired && $opened) {
    $message = "<span id='msg'>Message <strong>expired</strong> and <span id='opened'></span></span>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="description" content="Easily save private, encrypted, self-destructing messages in the cloud. Add a passkey, sender and reader names, and set messages to self-destruct after opening or a chosen time. Safe, simple, and secure!" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?php echo $description ?? SITE_NAME; ?>" />
  <meta name="twitter:description" content="<?php echo SITE_DESCRIPTION; ?>" />
  <meta name="twitter:image" content="/assets/placeholder_graphic.jpg" />
  <meta name="og:type" content="website" />
  <meta name="og:title" content="<?php echo $description ?? SITE_NAME; ?>" />
  <meta name="og:description" content="<?php echo SITE_DESCRIPTION; ?>" />
  <meta name="og:url" content="<?php echo SITE_DOMAIN; ?>" />
  <meta name="og:image" content="/assets/placeholder_graphic.jpg" />
  <title><?php echo SITE_NAME; ?></title>
  <link href="/css/style.css" rel="stylesheet">
  <link href="/css/color.style.css" rel="stylesheet">
  <style>
    #title-bar {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
    }

    .logo-link {
      align-self: center;
    }

    a {
      text-decoration: none;
    }

    #control-panel {
      display: flex;
      flex-direction: row-reverse;
      justify-content: space-between;
    }

    #control-sub-panel {
      display: flex;
      flex-direction: row;
    }

    #toast {
      position: fixed;
      bottom: 1rem;
      padding: 0.8rem;
      border: var(--pico-color-green-300) var(--pico-border-width) solid;
      border-radius: var(--pico-border-radius);
      background-color: var(--pico-background-color);
    }

    #app {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 98lvh;
      padding-top: 0.8rem;
      padding-bottom: 0.8rem;
    }

    label {
      min-width: 4rem;
    }

    #content-area {
      border: var(--pico-primary-border) var(--pico-border-width) solid;
      border-radius: var(--pico-border-radius);
      padding: 0.8rem;
      margin-bottom: 1.2rem;
    }

    .logo-link {
      margin-bottom: 0.7rem !important;
      margin-right: 0.2rem !important;
      color: var(--pico-color-primary) !important;
    }

    #reference {
      margin-top: 9rem;
    }

    .summary-copy {
      padding: 0 1rem;
    }

    .should-be-link {
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="container" id="app">
    <div>
      <div id="title-bar">
        <a href="/">
          <h1><?php echo SITE_NAME; ?></h1>
        </a>
      </div>
      <div id="msg-panel">
        <div id="content-area">
          <?php if ($message) {
            echo $message;
          } ?>
        </div>
        <div id="control-panel">
          <button type="button" id="snd-button" <?php if ($passkey || $opened || $expired) echo "disabled"; ?>>
            Fetch Message
          </button>
          <?php if ($passkey) { ?>
            <div id="control-sub-panel">
              <input type="password" id="passkey" placeholder="Passkey Required" <?php if ($expired || $opened) echo "disabled"; ?> />
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div id="toast" class="container" hidden>
      <span id="toast-emoji">✅</span> <span id="toast-message">stuff</span>
    </div>
    <?php include(__DIR__ . "/reference.html"); ?>
  </div>
</body>

<script>
  //******************************
  //    Variables
  //******************************
  const apiURL = "/api/v0/messages/<?php echo $uuid; ?>/read";
  const interactButton = document.getElementById("snd-button");
  const contentArea = document.getElementById("content-area");
  <?php if ($passkey) { ?>
    const passkeyInput = document.getElementById("passkey");
  <?php } ?>
  const toast = {
    container: document.getElementById("toast"),
    banner: document.getElementById("toast-message"),
    emoji: document.getElementById("toast-emoji"),
    closeToast: function() {
      this.container.setAttribute("hidden", "true");
      console.log("Closing toast");
    },
    shout: function(message = "Generic Message", success = true, timeout = 3000) {
      this.banner.innerText = message;
      if (!success) {
        this.emoji.innerText = "❌";
        this.container.style.borderColor = "var(--pico-color-red-500)";
      } else {
        this.emoji.innerText = "✅";
        this.container.style.borderColor = "var(--pico-color-green-500)";
      }
      this.container.removeAttribute("hidden");
      setTimeout(() => {
        this.closeToast();
      }, timeout);
    }
  }

  //******************************
  //    Interaction Assignments
  //******************************
  interactButton.addEventListener("click", fetchMessage);
  toast.emoji.addEventListener("click", () => {
    toast.closeToast();
  });
  <?php if ($passkey) { ?>
    passkeyInput.addEventListener("input", passkeyInputIneraction);
  <?php } ?>

  //******************************
  //    Function Definitions
  //******************************
  async function fetchMessage() {
    <?php if ($passkey) { ?>
      const passkey = document.getElementById("passkey").value;
    <?php } ?>
    try {
      const result = await fetch(apiURL<?php if ($passkey) echo ' + `?key=${passkey}`' ?>);
      const json = await result.json();
      console.log(json);
      if (!json.success) {
        throw new Error(json.message);
      }
      contentArea.innerText = json.data.letter;
      interactButton.removeEventListener("click", fetchMessage);
      interactButton.addEventListener("click", copyResponse(json.data.letter));
      interactButton.innerText = "Copy Message";
    } catch (err) {
      toast.shout(err.message, false);
    }
  }

  function copyResponse(value) {
    const callBack = () => {
      navigator.clipboard.writeText(value);
      toast.shout("Message Copied");
    }
    return callBack;
  }

  <?php if ($passkey) { ?>

    function passkeyInputIneraction() {
      if (passkeyInput.value.length === 0) {
        interactButton.setAttribute("disabled", "true");
      } else {
        interactButton.removeAttribute("disabled");
      }
    }
  <?php } ?>

  <?php if ($opened) { ?>
      (function() {
        const msg = document.getElementById("opened");
        msg.innerHTML += "<strong>opened</strong> on " + new Date(<?php echo $opened * 1000; ?>) + ".";
      })();
  <?php } ?>
</script>

</html>
