function validateRegister(form) {
    var fail = validateEmail(form.email.value);
    fail += validateUsername(form.username.value);
    fail += validatePassword(form.password.value);
    return alertFail(fail);
}

function validateEmail(field) {
    if (field == "") {
        return "No email entered.\n";
    } else if (!(/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(field))) {
        return "Invalid email.\n";
    }
    return "";
}

function validateUsername(field) {
    var minLength = 6;

    if (field == "") {
        return "No username entered.\n";
    } else if (field.length < minLength) {
        return "Username must be at least " + minLength + " characters.\n";
    } else if (/[^a-zA-Z0-9_-]/.test(field)) {
        return "Only a-z, A-Z, 0-9, _ and - allowed in username.\n";
    }
    return "";
}

function validatePassword(field) {
    var minLength = 8;

    if (field == "") {
        return "No password entered.\n";
    } else if (field.length < minLength) {
        return "Password must be at least " + minLength + " characters.\n";
    }
    return "";
}

function alertFail(fail) {
    if (fail == "") {
        return true;
    }

    alert(fail);
    return false;
}

function validateCrypto(form) {
    var fail = validateKey(form.key.value, form.cipher.value);
    return alertFail(fail);
}

function validateKey(field, cipher) {
    if (field == "") {
        return "No key entered.\n";
    }
    
    if (cipher == "Simple Substitution") {
        var length = 26;
        
        if (field.length != length) {
            return "Key must be 26 characters for simple substitution cipher.\n";
        } else if (hasDuplicate(field)) {
            return "Key must have unique characters for simple substitution cipher.\n";
        }
    } else if (cipher == "Double Transposition") {
        if (hasDuplicate(field)) {
            return "Key must have unique characters for double transposition cipher.\n";
        }
    }
    return "";
}

function hasDuplicate(field) {
    var dict = {};
        
    for (var i = 0; i < field.length; i++) {
        var char = field.charAt(i);
        
        if (char in dict) {
            return true;
        } else {
            dict[char] = true;
        }
    }
    
    return false;
}


