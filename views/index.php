<?php
include_once(__DIR__ . "/../models/database.php");
/* const SITE_NAME = "Project Flight"; */
$count = $visitor_increment();
// TODO: passkey
// TODO: Change view on succccessful response
// TODO: Create tests
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo SITE_NAME; ?></title>
  <link href="/css/style.css" rel="stylesheet">
  <link href="/css/color.style.css" rel="stylesheet">
  <style>
    a {
      text-decoration: none;
    }

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

    #options-panel {
      display: flex;
      flex-direction: row;
      justify-content: space-around;
    }

    #options-button {
      color: var(--pico-muted-color);
    }

    #sub-panel {
      display: flex;
      flex-direction: column;
    }
  </style>
</head>

<body>
  <div class="container" id="app">
    <div>
      <div id="title-bar">
        <h1><?php echo SITE_NAME; ?></h1>
      </div>
      <div id="msg-panel">
        <textarea id="msg-area" maxlength="400" rows="10"></textarea>
        <div id="options-panel" hidden="true">
          Placeholder
        </div>
        <div id="control-panel">
          <div id="sub-panel">
            <span id="full-count"><span id="char-count">0</span> / 400</span>
            <a id="options-button">Options</a>
          </div>
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
  //******************************
  //    Variables
  //******************************
  const apiURL = "/api/v0/messages";
  const msgArea = document.getElementById("msg-area");
  const sendButton = document.getElementById("snd-button");
  const optButton = document.getElementById("options-button");
  const charCount = document.getElementById("char-count");
  const fullCount = document.getElementById("full-count");
  const optPanel = document.getElementById("options-panel");
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
  msgArea.addEventListener("input", reactiveCharCount);
  sendButton.addEventListener("click", sendMessage);
  toast.emoji.addEventListener("click", () => {
    toast.closeToast();
  });
  optButton.addEventListener("click", toggleOptionsPanel);

  //******************************
  //    Function Definitions
  //******************************
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

  function toggleOptionsPanel() {
    const currentState = optPanel.getAttribute("hidden");
    if ("true" === currentState) {
      optPanel.removeAttribute("hidden");
    } else {
      optPanel.setAttribute("hidden", "true");
    }
  }
</script>

</html>
