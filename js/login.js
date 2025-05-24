document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        //BUG FOUND
        //WHEN SUBMITTING FORM WITH ENTER KEY THE JS DOES NOT TRIGGER
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const errorElement = document.getElementById('errorMessage');

        //console.log(username + "login attempt");
        
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


        const request = {
           type: 'Login',
           email: username,
           password: password, 
        }; 

        const jrequest = JSON.stringify(request);

        //console.log(request);
        
        // AJAX request
        fetch('../api.php', { //FOR AYUSH: CHANGE THIS TO WHEATLY API URL
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: jrequest
        })
        .then(response => response.json())
        .then(data => {

            console.log(data);

            

            if (data.status === 'success') {
                // Redirect based on user type

                setCookie("apiKey", data.data.api_key,2);

                //console.log(data.data.user_type);
                if (data.data.user_type === 'admin') {
                    setCookie("isAdmin",'true',2);
                    window.location.href = '../php/admin.php';
                } else {
                    setCookie("isAdmin",'false',2);
                    //window.location.href = '../php/home.php'; 
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

function setCookie(name, value, time) {
  let expires = "";
  if (time) {
    const date = new Date();
    date.setTime(date.getTime() + time * 60 * 60 * 1000);
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
}
