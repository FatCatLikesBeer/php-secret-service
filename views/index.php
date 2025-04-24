<?php
include_once(__DIR__ . "/../models/database.php");
$count = $visitor_increment();
// TODO: Text area character counter
// TODO: passkey
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
  <link href="/css/style.css" rel="stylesheet">
  <link href="/css/color.style.css" rel="stylesheet">
  <style>
    #control-panel {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
    }

    textarea {
      padding: 4px 8px;
    }

    #toast {
      padding: 0.8rem;
      border: var(--pico-color-green-300) var(--pico-border-width) solid;
      border-radius: var(--pico-border-radius);
    }

    #app {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 98lvh;
      padding-top: 0.8rem;
      padding-bottom: 0.8rem;
    }
  </style>
</head>

<body>
  <div class="container" id="app">
    <div>
      <div id="title-bar">
        <h1>Secret Messages</h1>
      </div>
      <div id="msg-panel">
        <textarea id="msg-area" maxlength="400" rows="10"></textarea>
        <div id="control-panel">
          <span id="full-count"><span id="char-count">0</span> / 400</span>
          <button type="button" id="snd-button">
            Save Message
          </button>
        </div>
      </div>
    </div>
    <div id="toast" class="container" hidden>
      <span id="toast-emoji">✅</span> <span id="toast-message">stuff</span>
    </div>
  </div>
</body>

<script>
  const apiURL = "/api/v0/messages";
  const msgArea = document.getElementById("msg-area");
  const button = document.getElementById("snd-button");
  const charCount = document.getElementById("char-count");
  const toast = {
    container: document.getElementById("toast"),
    banner: document.getElementById("toast-message"),
    emoji: document.getElementById("toast-emoji"),
    closeToast: function() {
      this.container.setAttribute("hidden", "true");
      console.log("Closing toast");
    },
    alert: function(message = "Generic Message", success = true, timeout = 3000) {
      this.container.removeAttribute("hidden");
      this.banner.innerText = message;
      if (!success) {
        this.emoji.innerText = "❌";
        this.container.style.borderColor = "var(--pico-color-red-500)";
      } else {
        this.emoji.innerText = "✅";
        this.container.style.borderColor = "var(--pico-color-green-500)";
      }
      setTimeout(() => {
        this.closeToast();
      }, timeout);
    }
  }

  toast.alert();

  msgArea.addEventListener("input", () => {
    charCount.innerText = msgArea.value.length;
  });

  button.addEventListener("click", () => {
    const message = msgArea.value;
    toast.alert(message, false);
  });

  toast.emoji.addEventListener("click", () => {
    toast.closeToast();
  });
</script>

</html>
