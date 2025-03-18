document.addEventListener("DOMContentLoaded", function() {
    const formHTML = `
        <div class="col-lg-6 mx-auto">
            <h1 class="text-white mb-3">Book A Tour Deals</h1>
            <p class="text-white mb-4">Get Your First Adventure with Calm Africa Safaris. Get More Deal Offers Here.</p>
            <form id="myForm" class="bg-white px-3 py-3 rounded shadow-lg" method="post">
                <div class="form-group">
                    <label for="name" class="text-warning p-2">Full Name:</label>
                    <input type="text" class="form-control custom-border p-2" id="name" name="name">
                </div>
                <div class="form-group">
                    <label for="email" class="text-warning p-2">Email:</label>
                    <input type="email" class="form-control custom-border p-2" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="phone" class="text-warning p-2">Phone Number:</label>
                    <input type="tel" class="form-control custom-border p-2" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="tourDate" class="text-warning p-2">Preferred Tour Date:</label>
                    <input type="date" class="form-control custom-border p-2" id="tourDate" name="tourDate">
                </div>
                <div class="form-group">
                    <label for="groupSize" class="text-warning p-2">Number of People:</label>
                    <input type="number" class="form-control custom-border p-2" id="groupSize" name="groupSize" min="1">
                </div>
                <div class="form-group">
                    <label for="preferences" class="text-warning p-2">Tour Preferences:</label>
                    <textarea class="form-control custom-border p-2" id="preferences" name="preferences" rows="4" placeholder="e.g., nature, history, etc."></textarea>
                </div>
                <button type="submit" class="btn btn-warning m-2 text-white">Submit Request</button>
            </form>
        </div>
    `;

    // Inject the form HTML into the element with the ID 'form-container'
    document.getElementById('form-container').innerHTML = formHTML;

    // Add event listener for form submission
    document.getElementById('myForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission
        alert('Your request has been submitted successfully!');
    });
});