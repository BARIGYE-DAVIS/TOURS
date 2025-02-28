
    document.getElementById('myForm').addEventListener('submit', function(event) {
        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('email').value.trim();
        var phone = document.getElementById('phone').value.trim();
        var tourDate = document.getElementById('tourDate').value.trim();
        var groupSize = document.getElementById('groupSize').value.trim();
        var preferences = document.getElementById('preferences').value.trim();

        if (name === "") {
            alert("Please fill in your name");
            event.preventDefault();
        } else if (email === "") {
            alert("Please fill in your email");
            event.preventDefault();
        } else if (phone === "") {
            alert("Please fill in your phone number");
            event.preventDefault();
        } else if (tourDate === "") {
            alert("Please fill in your preferred tour date");
            event.preventDefault();
        } else if (groupSize === "") {
            alert("Please fill in the number of people");
            event.preventDefault();
        } else if (preferences === "") {
            alert("Please fill in your tour preferences");
            event.preventDefault();
        }
    });