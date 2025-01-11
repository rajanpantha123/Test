function validateForm() {
    var email = document.getElementById('email').value;
    var contact = document.getElementById('contact').value;

    if (email === "" && contact === "") {
        alert("Please enter either your email or contact number.");
        return false;
    } else if (contact !== "" && contact.length !== 10) {
        alert("Please enter a valid contact number.");
        return false;
    } else {
        alert("Form submitted successfully");
        return true;
    }
}

function data2() {
    let recover = document.getElementById("email").value;
    // You can add more functionality here if needed
}
