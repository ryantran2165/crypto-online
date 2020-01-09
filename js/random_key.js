function getRandomKey() {
    var cipher = document.getElementById("cipher").value;
    
    if (cipher == "Simple Substitution") {
        document.getElementById("key").value = getSimpleSubstitutionKey();
    } else if (cipher == "Double Transposition") {
        document.getElementById("key").value = getDoubleTranspositionKey();
    } else if (cipher == "RC4") {
        document.getElementById("key").value = getRC4Key();
    }
}

function getSimpleSubstitutionKey() {
    var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    var arr = chars.split("");
    
    // Fisher-Yates Shuffle
    for (var i = arr.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = arr[i];
        arr[i] = arr[j];
        arr[j] = temp;
    }
    
    return arr.join("");
}

function getDoubleTranspositionKey() {
    var minLength = 5;
    var maxLength = 10;
	var length = minLength + Math.floor(Math.random() * (maxLength - minLength + 1));
	var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()-_=+[]{}|;:,.<>/?";
	var key = "";
	
	// Duplicate chars are not ok
	while (key.length < length) {
	    char = chars.charAt(Math.floor(Math.random() * chars.length));
	    
	    if (!key.includes(char)) {
	        key += char;
	    }
	}
	
	return key;
}

function getRC4Key() {
    var minLength = 5;
    var maxLength = 16;
	var length = minLength + Math.floor(Math.random() * (maxLength - minLength + 1));
	var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()-_=+[]{}|;:,.<>/?";
	var key = "";
	
	// Duplicate chars are ok
	for (var i = 0; i < length; i++) {
	    key += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	
	return key;
}

