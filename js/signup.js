document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const errorElement = document.getElementById('errorMessage');
    const passwordInput = document.getElementById('password');
    const passwordHint = document.querySelector('.password-hint');
    
    // Email validation pattern
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    // Password requirements
    const passwordRequirements = [
        { id: 'length', pattern: /.{8,}/, message: '8+ characters' },
        { id: 'uppercase', pattern: /[A-Z]/, message: 'An uppercase letter' },
        { id: 'lowercase', pattern: /[a-z]/, message: 'A lowercase letter' },
        { id: 'number', pattern: /\d/, message: 'A number' },
        { id: 'special', pattern: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/, message: 'A special character' }
    ];

    // Real-time password validation
    passwordInput.addEventListener('input', function() {
        updatePasswordRequirements(passwordInput.value);
    });

    // Update password requirement indicators
    function updatePasswordRequirements(password) {
        let allMet = true;
        let requirementsHTML = '';

        passwordRequirements.forEach(req => {
            const isMet = req.pattern.test(password);
            allMet = allMet && isMet;
            
            requirementsHTML += `
                <div class="requirement ${isMet ? 'met' : ''}" data-test="${req.id}">
                    ${isMet ? '✓' : '✗'} ${req.message}
                </div>
            `;
        });

        passwordHint.innerHTML = requirementsHTML;
        return allMet;
    }


    // Validate email
    function validateEmail(email) {
        return emailPattern.test(email);
    }

    // Validate name/surname
    function validateName(name) {
        return /^[a-zA-Z '-]{2,50}$/.test(name);
    }

    // Show error message
    function showError(message) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    // Clear errors
    function clearErrors() {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }

    // Get missing requirements
    function getMissingRequirements(password) {
        return passwordRequirements
            .filter(req => !req.pattern.test(password))
            .map(req => req.message);
    }


    // Form submission handler
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        // Get form values
        const formData = {
            type: 'Register',
            name: document.getElementById('name').value.trim(),
            surname: document.getElementById('surname').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: passwordInput.value,
            user_type: document.querySelector('input[name="accountType"]:checked').value
        };

        // Validate all fields
        let isValid = true;

        // Name validation
        if (!validateName(formData.name)) {
            isValid = false;
        }

        // Email validation
        if (!validateEmail(formData.email)) {
            showError('Please enter a valid email address');
            isValid = false;
        }

        // Password validation
        const passwordValid = updatePasswordRequirements(formData.password);
        if (!passwordValid) {
            const missing = getMissingRequirements(formData.password);
            showError(`Password needs: ${missing.join(', ')}`);
            isValid = false;
        }

        // Submit if valid
        if (isValid) {
            submitForm(formData);
        }
    });

    // Form submission
    function submitForm(formData) {

        console.log(formData);

        fetch('../api.php', { //FOR AYUSH: CHANGE THIS TO WHEATLY API URL
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {

            console.log(data);

            if (data.status === 'success') {
                window.location.href = '../php/login.php?signup=success';
            } else {
                showError(data.message || 'Registration failed. Please try again.');
            }
        })
        .catch(error => {
            showError('An error occurred. Please try again.');
            console.error('Error:', error);
        });
    }
});