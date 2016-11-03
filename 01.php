<?php
// Sample page to test user login and password verification to the AD, via ADAuth Web Service
//
// 01.php - Simple example that to call and verify user's login and password
//
// ############################################################################################
// BE WARNED - Furnishing a wrong password thrice locks your account in the AD GAL.
// ############################################################################################
//
// Functions:
// None
// 
// Cookies:
// None
//
// Session Variables:
// None
//
// Revisions:
//    1. Sundar Krishnamurthy - sundar_k@hotmail.com               11/03/2016      Coding and commenting started

// Start output buffering on
ob_start();

// Start the initial session
session_start();

$name = "";
$password = "";

$firstName = "";
$lastName = "";
$samAccountName = "";
$email = "";
$authenticated = 0;

$nameStyle = "inputLabel";
$passwordStyle = "inputLabel";

$formPosted = false;

$errorMessage = "";
$errorHeaderMessage = "";	

// Next, if this form has been posted back
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $formPosted = true;

    // Read name and password
    $name = trim($_POST["txtName"]);
    $password = $_POST["txtPassword"];

    $errorMessage = "<ul>";

    if ($name == "") {
        $errorMessage = $errorMessage . "<li>Please enter your login ID or email address.</li>";
        $nameStyle = "redLabelBold";
    }

    if ($password == "") {
        $errorMessage = $errorMessage . "<li>Please enter your AD password.</li>";
        $passwordStyle = "redLabelBold";
    }

    // Only if user has entered a proper value
    if ($errorMessage == "<ul>") {

        $errorMessage = "";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://secauth01.yourcompany.com/Default.aspx");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "name=" . urlencode($name) . "&password=" . urlencode($password));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if ($response === FALSE) {
            $errorMessage = "<ul><li>" . curl_errorno($ch) . ": " . curl_error($ch) . "</li></ul>";
        } else if ($response != "") {

            $responseData = json_decode($response, true);
            $user = $responseData["user"];

            $firstName = $user["firstName"];
            $lastName = $user["lastName"];
            $samAccountName = $user["samAccountName"];
            $email = $user["email"];

            // This should drive all your logic - authenticated = 1 or 0 means your user is authentic or not
            $authenticated = $user["authenticated"];
        } else {
            $errorMessage = "<ul><li>No data found for provided input.</li><ul>";
        }

        curl_close($ch);
    } else {
        $errorMessage = $errorMessage . "</ul>";
    }
}   //  End if (($_SERVER["REQUEST_METHOD"] === "POST") && (postedFromSame($_SERVER["HTTP_REFERER"]) === true))

if ($errorMessage != "") {
    $errorHeaderMessage = "Please correct these errors below";
}   //  End if ($errorMessage != "")
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>ADAuthentication client demo</title>
    <link rel="stylesheet" type="text/css" href="main.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script language="Javascript">
    function fasttrim(str) {
        var str = str.replace(/^\s\s*/, ''),
                  ws = /\s/,
                  i = str.length;

        while (ws.test(str.charAt(--i)))
            ;

        return str.slice(0, i + 1);
    }

    function loadExampleForm() {
        var loginField = document.getElementById("txtName");

        if (loginField != null) {
            loginField.focus();
        }
    }

    function validateExampleForm() {

        // Return value, default error message is <ul>
        var errorsFound = false;
        var errorMessage = "<ul>";
        var errorFlag = 0;

        // Read data on the login text field, trim it to remove whitespace on either side
        var loginElement = document.getElementById("txtName");
        var emailValue = fasttrim(loginElement.value);

        // Locate nameSpan element
        var nameSpanElement = document.getElementById("nameSpan");

        // Locate errorSection block, update display to block (from none)
        var errorSectionElement = document.getElementById("errorSection");

        // If the values don't match, replace it with the trimmed version
        if (emailValue != loginElement.value) {
            loginElement.value = emailValue;
        }

        var passwordSpanElement = document.getElementById("passwordSpan");
        var passwordValue = document.getElementById("txtPassword").value;

        // If the data field was blank
        if (emailValue == "") {
            // We found an error
            errorsFound = true;

            // Update error message to add this warning
            errorMessage += "<li>Please enter your login ID or email address.</li>";
   
            // Set className as redLabel
            nameSpanElement.className = "redLabelBold";

            errorFlag = errorFlag | 1;

            loadExampleForm();
        } else {
           // Set className back as inputLabel
            nameSpanElement.className = " inputLabel";
        }

        // If the data field was blank
        if (passwordValue == "") {
            // We found an error
            errorsFound = true;

            // Update error message to add this warning
            errorMessage += "<li>Please enter your AD password.</li>";

            // Set className as redLabel
            passwordSpanElement.className = "redLabelBold";

            if (errorFlag == 0) {
                document.getElementById("txtPassword").focus();
            }

            errorFlag = errorFlag | 2;
        } else {
           // Set className back as inputLabel
            passwordSpanElement.className = " inputLabel";
        }

        // In case you found errors, display error message block
        if (errorsFound) {
            // Add closing tag to make errorMessage displayable
            errorMessage += "</ul>";

            // Display error section
            errorSectionElement.style.display = "block";

            // Get section for errorHeaderSpan, set boiler-plate text for header
            document.getElementById("errorHeaderSpan").innerHTML = "Please correct these errors below:";

            // Locate errorText element, set innerHTML to message we constructed above
            var errorTextElement = document.getElementById("errorText");
            errorTextElement.innerHTML = errorMessage;
        
        } else {
            // Reset error message
            errorMessage = "";

            // Hide error section
            errorSectionElement.style.display = "none";
        }

        return !errorsFound;
    }
    </script>
  </head>
  <body class="bodyContent" onload="loadExampleForm();">
    <form name="example01Form" method="POST" action="01.php">
      <div id="errorSection" <?php if ($errorMessage == "") { print(" style=\"display: none;\""); }?>>
        <div class="errorPanel" style="width: 450px">
          <span class="boldLabel" id="errorHeaderSpan"><?php print($errorHeaderMessage); ?>:</span><br/>
          <span class="inputLabel" id="errorText"><?php if ($errorMessage != "") { print($errorMessage); }?></span>
        </div>
        <div class="fillerPanel20px">&nbsp;</div>
      </div>
      <div class="loginPanel">
        <p>
          <span class="columnHeader">ADAuth Demo</span><br/>
          <span class="boldLabel">Typing a wrong password thrice can lock your account!</span>
        </p>
        <table class="loginTable">
          <tbody>
            <tr>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonLabel">
                <span class="<?php print($nameStyle); ?>" id="nameSpan">Name (Email/login)</span>
                <span class="requiredField">*</span>
                <span class="inputLabel">:</span>
              </td>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonInput">
                <input type="text" class="inputText" name="txtName" id="txtName" placeholder="Email or login credential" maxlength="100" style="width:240px" value="<?php print($name); ?>" required/>
              </td>
            </tr>
            <tr>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonLabel">
                <span class="<?php print($passwordStyle); ?>" id="passwordSpan">Password</span>
                <span class="requiredField">*</span>
                <span class="inputLabel">:</span>
              </td>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonInput">
                <input type="password" class="inputText" name="txtPassword" id="txtPassword" maxlength="48" style="width:240px" value="" required/>
              </td>
            </tr>
            <tr<?php if (($errorMessage !== "") || ($formPosted === false)) { print(" style=\"display: none;\""); }?>>
              <td colspan="4">
                <ul>
                  <li><span class="inputLabel">First Name: <?php print(($firstName == "") ? "No information found" : "<b>" . htmlspecialchars($firstName) . "</b>"); ?></span></li>
                  <li><span class="inputLabel">Last Name: <?php print(($lastName == "") ? "No information found" : "<b>" . htmlspecialchars($lastName) . "</b>"); ?></span></li>
                  <li><span class="inputLabel">Email: <?php print(($email == "") ? "No information found" : "<b>" . htmlspecialchars($email) . "</b>"); ?></span></li>
                  <li><span class="inputLabel">SAM Account Name: <?php print(($samAccountName == "") ? "No information found" : "<b>" . htmlspecialchars($samAccountName) . "</b>"); ?></span></li>
                  <li><span class="inputLabel">Authenticated: <?php print("<b>" . (($authenticated === 1) ? "Yes" : "No") . "</b>"); ?></span></li>
                </ul>
              </td>
            </tr>
            <tr>
              <td colspan="4">
                <hr/>
              </td>
            </tr>
            <tr>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonLabel">&nbsp;&nbsp;&nbsp;</td>
              <td class="commonInput">
                <input type="submit" class="submitButton" id="btnSubmit" name="btnSubmit" value="Submit" onclick="return validateExampleForm();"/>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="fillerPanel40px">&nbsp;</div>  
    </form>
  </body>
</html>
<?php
ob_end_flush();
?>