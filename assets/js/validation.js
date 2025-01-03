document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire de connexion
    if (document.getElementById('loginForm')) {
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Valider email
            const email = document.getElementById('email');
            if (!validateEmail(email.value)) {
                showError(email, 'Email invalide');
                isValid = false;
            } else {
                clearError(email);
            }
            
            // Valider mot de passe
            const password = document.getElementById('password');
            if (password.value.length < 6) {
                showError(password, 'Le mot de passe doit contenir au moins 6 caractères');
                isValid = false;
            } else {
                clearError(password);
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Validation du formulaire d'inscription
    if (document.getElementById('registerForm')) {
        const registerForm = document.getElementById('registerForm');
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Valider nom
            const nom = document.getElementById('nom');
            if (nom.value.length < 2) {
                showError(nom, 'Le nom doit contenir au moins 2 caractères');
                isValid = false;
            } else {
                clearError(nom);
            }
            
            // Valider prénom
            const prenom = document.getElementById('prenom');
            if (prenom.value.length < 2) {
                showError(prenom, 'Le prénom doit contenir au moins 2 caractères');
                isValid = false;
            } else {
                clearError(prenom);
            }
            
            // Valider email
            const email = document.getElementById('email');
            if (!validateEmail(email.value)) {
                showError(email, 'Email invalide');
                isValid = false;
            } else {
                clearError(email);
            }
            
            // Valider mot de passe
            const password = document.getElementById('password');
            if (password.value.length < 6) {
                showError(password, 'Le mot de passe doit contenir au moins 6 caractères');
                isValid = false;
            } else {
                clearError(password);
            }
            
            // Valider photo
            const photo = document.getElementById('photo');
            if (photo.files.length > 0) {
                const file = photo.files[0];
                const fileType = file.type;
                const validImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                
                if (!validImageTypes.includes(fileType)) {
                    showError(photo, 'Le fichier doit être une image (JPG, JPEG ou PNG)');
                    isValid = false;
                } else if (file.size > 5 * 1024 * 1024) { // 5MB
                    showError(photo, 'La taille de l\'image ne doit pas dépasser 5MB');
                    isValid = false;
                } else {
                    clearError(photo);
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showError(input, message) {
    const errorElement = input.nextElementSibling;
    if (errorElement && errorElement.classList.contains('error-message')) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        input.style.borderColor = 'red';
    }
}

function clearError(input) {
    const errorElement = input.nextElementSibling;
    if (errorElement && errorElement.classList.contains('error-message')) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
        input.style.borderColor = '#ddd';
    }
}
