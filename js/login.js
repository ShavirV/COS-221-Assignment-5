document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const errorElement = document.getElementById('errorMessage');

        // clear all previous errors (i think everything is working can add again tho)
        errorElement.textContent = '';
        
        if (!username || !password) 
        {
            errorElement.textContent = 'Please fill in all fields';
            return;
        }

        const request = {
           type: 'Login',
           email: username,
           password: password, 
        }; 

        const jrequest = JSON.stringify(request);
        
        fetch('../api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: jrequest
        })
        .then(response => response.json())
        .then(data => {
            console.log('Login response:', data);

            if (data.status === 'success')
            {
                const userEmail = data.data?.email;
                // set cookie by login once success
                setCookie("api_key", data.data.api_key, 2);
                // cookie for displaying email after login
                setCookie("user_email", userEmail, 2); 
                // timeout to allow cookie to load, not needed but this ensures email will show
                setTimeout(() => {
                    if (data.data.user_type === 'admin') {
                        setCookie("isAdmin", 'true', 2);
                        window.location.href = '../php/admin.php';
                    } else {
                        setCookie("isAdmin", 'false', 2);
                        window.location.href = '../php/home.php';
                    }
                }, 100);
                console.log('Cookie set:', {
                    name: 'api_key',
                    value: data.data.api_key,
                    allCookies: document.cookie
                });

                if (data.data.user_type === 'admin') 
                {
                    setCookie("isAdmin", 'true', 2);
                    window.location.href = '../php/admin.php';
                } 
                
                else
                {
                    setCookie("isAdmin", 'false', 2);
                    window.location.href = '../php/home.php'; 
                }
            } 
            
            else 
            {
                errorElement.textContent = data.message;
            }
        })
        .catch(error => {
            errorElement.textContent = 'An error occurred. Please try again.';
            console.error('Error:', error);
        });
    });
});

// helper functions
function setCookie(name, value, hours) {
    let expires = "";
    if (hours) {
        const date = new Date();
        date.setTime(date.getTime() + hours * 60 * 60 * 1000);
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
    console.log('Cookie set:', name, value);
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
    }
    return null;
}
