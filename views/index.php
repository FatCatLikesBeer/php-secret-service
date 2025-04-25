<?php
include_once(__DIR__ . "/../models/database.php");
$count = $visitor_increment();
// TODO: Reactive char counter indicator color
// TODO: passkey
// TOOD: Change view on succccessful response
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
      resize: none;
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
  const fullCount = document.getElementById("full-count");
  const charCountBreakpoints = [360, 390];
  const toast = {
    container: document.getElementById("toast"),
    banner: document.getElementById("toast-message"),
    emoji: document.getElementById("toast-emoji"),
    closeToast: function() {
      this.container.setAttribute("hidden", "true");
      console.log("Closing toast");
    },
    shout: function(message = "Generic Message", success = true, timeout = 3000) {
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

  async function sendMessage() {
    const messageValue = msgArea.value;
    try {
      const request = await fetch(`${apiURL}?message=${messageValue}`, {
        method: "POST"
      });
      if (!request.ok) {
        throw new Error("Server Error, please try again later");
      }
      const json = await request.json();
      if (!json.success) {
        throw new Error(json.message);
      }
      toast.shout(json.message);
      console.log(json);
    } catch (err) {
      toast.shout(err.message, false);
    }
  }

  function reactiveCharCount() {
    const length = msgArea.value.length;
    charCount.innerText = length;
    if ((charCountBreakpoints[0] <= length) && (charCountBreakpoints[1] > length)) {
      fullCount.style.setProperty("color", "var(--pico-color-yellow-200)");
    } else if (charCountBreakpoints[1] <= length) {
      fullCount.style.setProperty("color", "var(--pico-color-red-500)");
    } else {
      fullCount.style.removeProperty("var(--pico-color)");
    }
  }

  msgArea.addEventListener("input", reactiveCharCount);

  button.addEventListener("click", sendMessage);

  toast.emoji.addEventListener("click", () => {
    toast.closeToast();
  });
</script>

</html>
