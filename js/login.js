document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const userType = document.querySelector('input[name="userType"]:checked').value;
        const errorElement = document.getElementById('errorMessage');
        
        // Clear previous errors
        errorElement.textContent = '';
        
        // Simple validation because signup will handle password strength
        if (!username || !password) {
            errorElement.textContent = 'Please fill in all fields';
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('userType', userType);
        
        // AJAX request
        fetch('#', { //FOR AYUSH: CHANGE THIS TO WHEATLY API URL
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect based on user type
                if (userType === 'admin') {
                    window.location.href = 'admin.php';
                } else {
                    window.location.href = 'home.php'; //STILL NEED TO IMPLEMENT THIS BASED ON LOGGED IN CUSTOMER
                }
            } else {
                errorElement.textContent = data.message;
            }
        })
        .catch(error => {
            errorElement.textContent = 'An error occurred. Please try again.';
            console.error('Error:', error);
        });
    });
});