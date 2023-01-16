
function ToggleNavbar(nav) {
    var navStatus = nav.getAttribute("data-nav-status");

    if (navStatus == "closed" || navStatus == null) {
        nav.setAttribute("data-nav-status", "open");
        nav.classList.add("open");
    } else if (navStatus == "open") {
        nav.setAttribute("data-nav-status", "closed");
        nav.classList.remove("open");
    } else {
        console.warn("Unknown nav status: " + navStatus)
    }
}

function AddNavbarListeners() {
    var navbar = document.getElementsByClassName("navbar");

    if (navbar.length > 0) {
        console.log("Found the navigation bar")
        var nav = navbar[0]
        var button = nav.querySelector(".toggle-button")

        if (button != null) {
            console.log("Adding listener to button")
            button.addEventListener("click", function() {
                ToggleNavbar(nav)
            });
            console.log("Added listener to button")
        }
    }
}

document.addEventListener("DOMContentLoaded", AddNavbarListeners);