var loaded;

function directUser(url, ms=0) {
    setTimeout(function() {
        window.location.href = url;
    }, ms);
}

function addPageResponse(title="", message="") {

    // Create a new div element
    var newDiv = document.createElement("div");

    // Add a class to the div
    newDiv.classList.add("page-title");

    // Adds h1 element
    var titleElement = document.createElement("h1");
    titleElement.classList.add("center-text");
    titleElement.innerHTML = title;

    // Adds text to h1 element
    var textElement = document.createElement("p");
    textElement.classList.add("center-text");
    textElement.innerHTML = message;

    // Adds h1 and p elements to div
    newDiv.appendChild(titleElement);
    newDiv.appendChild(textElement);

    // Adds div to body
    document.body.appendChild(newDiv);
}

function handleConfirmPurchase(url_to_redirect, cancelled_url) {
    var productType = purchaseType
    var userConfirmation = confirm("Are you sure you want to purchase this " + productType + "?");
    var titleMessage = "";
    var message = "";
    var url = "";


    if (userConfirmation == true) {
        titleMessage = "Thank you for confirming your purchase!";
        message = "We are processing your order and you will be redirected shortly.";
        url = url_to_redirect;
    } else {
        titleMessage = "You have cancelled your purchase.";
        message = "You will be redirected to the home page shortly.";
        url = cancelled_url;
    }

    addPageResponse(titleMessage, message);
    directUser(url, 5000);
}

document.addEventListener("DOMContentLoaded", function() {
    // endUrl has been defined in the PHP file
    // cancelUrl has been defined in the PHP file
    
    try {
        handleConfirmPurchase(endUrl, cancelUrl);
    } catch (e) {
        console.log(e);
    }
});
