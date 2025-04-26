<?php
include_once(__DIR__ . "/../models/database.php");
/* const SITE_NAME = "Project Flight"; */
$count = $visitor_increment();
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
      flex-direction: column;
      justify-content: space-around;
    }

    #options-panel>div {
      display: flex;
      flex-direction: row;
    }

    #options-button {
      color: var(--pico-muted-color);
    }

    #sub-panel {
      display: flex;
      flex-direction: column;
    }

    label {
      min-width: 4rem;
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
          <div>
            <label for="expires">Time to Expire</label>
            <select id="expires">
              <option value="6">6 Hours</option>
              <option value="8">8 Hours</option>
              <option value="12">12 Hours</option>
              <option value="24" selected>1 Day</option>
              <option value="36">3 Days</option>
              <option value="168">7 Days</option>
            </select>
          </div>
          <div>
            <label for="passkey">Pass Key:</label>
            <input id="passkey" type="password" placeholder="none" />
          </div>
          <div>
            <label for="writer">From:</label>
            <input id="writer" type="text" placeholder="Author" />
          </div>
          <div>
            <label for="reader">To:</label>
            <input id="reader" type="text" placeholder="Recipiant" />
          </div>
        </div>
        <div id="control-panel">
          <div id="sub-panel">
            <span id="full-count"><span id="char-count">0</span> / 400</span>
            <a id="options-button">Options</a>
          </div>
          <button type="button" id="snd-button" disabled>
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
  msgArea.addEventListener("input", respondToMessageAreaInteraction);
  sendButton.addEventListener("click", sendMessage);
  toast.emoji.addEventListener("click", () => {
    toast.closeToast();
  });
  optButton.addEventListener("click", toggleOptionsPanel);

  //******************************
  //    Function Definitions
  //******************************
  async function sendMessage() {
    const parameterizedOptions = constructParameter();
    const fullURI = `${apiURL}?${parameterizedOptions}`;
    console.log(fullURI);
    try {
      const request = await fetch(fullURI, {
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

  function respondToMessageAreaInteraction() {
    const length = msgArea.value.length;
    charCount.innerText = length;
    if ((charCountBreakpoints[0] <= length) && (charCountBreakpoints[1] > length)) {
      fullCount.style.setProperty("color", "var(--pico-color-yellow-200)");
    } else if (charCountBreakpoints[1] <= length) {
      fullCount.style.setProperty("color", "var(--pico-color-red-500)");
    } else {
      fullCount.style.removeProperty("var(--pico-color)");
    }
    if (0 === length) {
      sendButton.setAttribute("disabled", "true");
    } else {
      sendButton.removeAttribute("disabled");
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

  function constructParameter() {
    const message = msgArea.value;
    const expires = document.getElementById("expires").value;
    const writer = returnNullIfEmpty(document.getElementById("writer").value)
    const reader = returnNullIfEmpty(document.getElementById("reader").value)
    const key = returnNullIfEmpty(document.getElementById("passkey").value)
    const values = {
      message,
      expires,
      writer,
      reader,
      key
    }

    let result = "";
    const valueKeys = Object.keys(values);
    valueKeys.forEach((key, i, arr) => {
      if (null != values[key]) {
        result += `${key}=${values[key]}&`;
      }
    });
    result = result.substring(0, result.length - 1);
    return encodeURI(result);
  }

  function returnNullIfEmpty(value) {
    const result = "" === value ? null : value;
    return result;
  }

  function logSelectedOptions() {
    /* console.log(options); */
    const parameterizedOptions = constructParameter();
    console.log(parameterizedOptions);
  }
</script>

</html>
