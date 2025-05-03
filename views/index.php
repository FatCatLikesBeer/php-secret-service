<?php
include_once(__DIR__ . "/../models/database.php");
$site_domain = $_SERVER["HTTP_HOST"];
$count = $visitor_increment();
$request_uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
$uri = rtrim($request_url, '/');
$uuid_key = 8 < strlen($uri) ? explode("/", $uri)[2] : false;
[$uuid] = explode(":", $uuid_key);
if ($uuid_key) {
  if (strlen($uuid_key) != 23) {
    global $site_domain;
    header("Location: {$site_domain}");
    exit;
  } else include(__DIR__ . "/message.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="description" content="Easily save private, encrypted, self-destructing messages in the cloud. Add a passkey, sender and reader names, and set messages to self-destruct after opening or a chosen time. Safe, simple, and secure!" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?php echo SITE_NAME; ?>" />
  <meta name="twitter:description" content="<?php echo SITE_DESCRIPTION; ?>" />
  <meta name="twitter:image" content="/assets/placeholder_graphic.jpg" />
  <meta name="og:type" content="website" />
  <meta name="og:title" content="<?php echo SITE_NAME; ?>" />
  <meta name="og:description" content="<?php echo SITE_DESCRIPTION; ?>" />
  <meta name="og:url" content="<?php echo $site_domain; ?>" />
  <meta name="og:image" content="/assets/placeholder_graphic.jpg" />
  <title><?php echo SITE_NAME; ?></title>
  <link href="/css/style.css" rel="stylesheet" />
  <link href="/css/color.style.css" rel="stylesheet" />
  <link rel="icon" href="/favicon.ico" type="image/x-icon" />
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
      flex-direction: column;
    }

    textarea {
      padding: 4px 8px;
      resize: none;
    }

    #toast {
      position: fixed;
      bottom: 1rem;
      padding: 0.8rem;
      border: var(--pico-color-green-300) var(--pico-border-width) solid;
      border-radius: var(--pico-border-radius);
      background-color: var(--pico-background-color);
      width: 93svw;
      left: 50%;
      transform: translateX(-50%);
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

    #options-panel-content {
      display: flex;
      flex-direction: row;
      justify-content: space-around;
    }

    .options-panel-item {
      display: flex;
      flex-direction: column;
    }

    .options-panel-item>div {
      display: flex;
      flex-direction: row;
    }

    #options-button {
      color: var(--pico-muted-color);
    }

    label {
      min-width: 4rem;
    }

    #response {
      border: var(--pico-primary-border) var(--pico-border-width) solid;
      border-radius: var(--pico-border-radius);
      padding: 0.8rem;
      margin-bottom: 1.2rem;
    }

    #chevron {
      display: flex;
      flex-direction: row;
      justify-content: center;
      margin-bottom: 0.5rem;
    }

    .logo-link {
      margin-bottom: 0.7rem !important;
      margin-right: 0.2rem !important;
      color: var(--pico-color-primary) !important;
    }

    #reference {
      margin-top: 8rem;
    }

    .summary-copy {
      padding: 0 1rem;
    }

    .should-be-link {
      cursor: pointer;
    }

    @media (max-width: 48rem) {
      #options-panel-content {
        flex-direction: column;
      }

      #reference {
        margin-top: 16rem;
      }
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
        <textarea id="msg-area" maxlength="400" rows="10" placeholder="☁️ This is a self-destructing message service.
📝 Write your message here! We'll send you a link to view it.
💥 The message will self-destruct when opened or after 24 hours, which ever comes first.
🔐 Add a passkey for extra peace of mind.
📋 Check out more options below."></textarea>
        <div id="options-panel" hidden="true">
          <div id="chevron" class="should-be-link">
            <?php include(__DIR__ . "/chevron.html"); ?>
          </div>
          <div id="options-panel-content">
            <div class="options-panel-item container-fluid">
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
            </div>
            <div class="options-panel-item container-fluid">
              <div>
                <label for="writer">From:</label>
                <input id="writer" type="text" placeholder="Author" />
              </div>
              <div>
                <label for="reader">To:</label>
                <input id="reader" type="text" placeholder="Recipiant" />
              </div>
            </div>
          </div>
        </div>
        <div id="control-panel">
          <button type="button" id="snd-button" disabled>
            Save Message
          </button>
          <div id="control-sub-panel">
            <span id="full-count"><span id="char-count">0</span> / 400</span>
            <a id="options-button" class="should-be-link">Options</a>
          </div>
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
  const apiURL = "/api/v0/messages";
  const msgArea = document.getElementById("msg-area");
  const interactButton = document.getElementById("snd-button");
  const optButton = document.getElementById("options-button");
  const charCount = document.getElementById("char-count");
  const fullCount = document.getElementById("full-count");
  const optPanel = document.getElementById("options-panel");
  const chevron = document.getElementById("chevron");
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
  optButton.addEventListener("click", toggleOptionsPanel);
  chevron.addEventListener("click", toggleOptionsPanel);
  interactButton.addEventListener("click", sendMessage);
  toast.emoji.addEventListener("click", () => {
    toast.closeToast();
  });

  //******************************
  //    Function Definitions
  //******************************
  async function sendMessage() {
    const parameterizedOptions = constructParameter();
    const fullURI = `${apiURL}?${parameterizedOptions}`;
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
      swapMessageAreaWithResponse(json.data);
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
      interactButton.setAttribute("disabled", "true");
    } else {
      interactButton.removeAttribute("disabled");
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

  function closeOptionsPanel() {
    optPanel.setAttribute("hidden", "true");
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

  function swapMessageAreaWithResponse(response = {
    uuid: string,
    expires: string | number,
    writer: string,
    reader: string,
    writer_email: string,
    reader_email: string,
  }) {
    // Validate response
    let invalid = false;
    try {
      if (typeof response.uuid != "string") {
        throw new Error("Something went really wrong 😩");
      }
    } catch (err) {
      invalid = err.message;
    }

    if (invalid) {
      toast.shout(invalid, false);
    }

    // Constants
    const linkURL = `${window.location.origin}/message/${response.uuid}`;
    const timeUnits = 24 < response.expires ? "days" : "hours";
    const timeQuantity = 24 < response.expires ? response.expires / 24 : response.expires;
    const responseDiv = document.createElement("div")

    // Create and populate response element
    responseDiv.setAttribute("id", "response")
    responseDiv.innerHTML = `
      <p>${linkURL}</p>
      <p>🔐 Your message has been encrypted and stored.</p>
      <p>⏱️ It will expire in ${timeQuantity} ${timeUnits}.</p>
      <p>🔗 Click the button below to copy the link!</p>
    `;

    // Swap element & Modify Views
    closeOptionsPanel();
    document.getElementById("control-sub-panel").remove();
    document.getElementById("msg-panel").replaceChild(responseDiv, msgArea);

    // Modify button
    interactButton.innerText = "Copy Link"
    interactButton.removeEventListener("click", sendMessage);
    interactButton.addEventListener("click", () => {
      navigator.clipboard.writeText(linkURL);
      toast.shout("Link Copied!");
    });
  }
</script>

</html>
