function validateForm() {
    let a = document.getElementById("n1").value;
    let b = document.getElementById("n2").value;
    let c = document.getElementById("n3").value;

    if (a === "" || b === "" || c === "") {
        alert("Please enter all the fields");
        return false;
    } else if (b !== c) {
        alert("The password and confirm password must match");
        return false;
    } else {
        alert("Form submitted successfully");
        return true;
    }
}
