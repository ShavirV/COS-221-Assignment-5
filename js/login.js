document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        //BUG FOUND
        //WHEN SUBMITTING FORM WITH ENTER KEY THE JS DOES NOT TRIGGER
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const userType = document.querySelector('input[name="userType"]:checked').value;
        const errorElement = document.getElementById('errorMessage');

        console.log(username + "login attempt");
        
        // Clear previous errors
        errorElement.textContent = '';
        
        // Simple validation because signup will handle password strength
        if (!username || !password) {
            errorElement.textContent = 'Please fill in all fields';
            return;
        }
        
        // Create form data
        // const formData = new FormData();
        // formData.append('username', username);
        // formData.append('password', password);
        // formData.append('userType', userType);


        request = {
           type: 'Login',
           email: username,
           password: password, 
        }; 
        
        // AJAX request
        fetch('../api.php', { //FOR AYUSH: CHANGE THIS TO WHEATLY API URL
            method: 'POST',
            //headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(request)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect based on user type

                console.log(data.data.user_type);
                if (data.data.user_type === 'admin') {
                    window.location.href = 'admin.php';
                } else {
                    window.location.href = 'home.php'; 
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