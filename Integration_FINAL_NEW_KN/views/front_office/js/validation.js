/**
 * NutriNova Form Validation
 * Custom JavaScript validation for all forms
 * Meets teacher requirements: HTML5 validation alone is NOT acceptable
 */

// ====================
// VALIDATION UTILITIES
// ====================

/**
 * Show error message
 */
function showValidationError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#ba1a1a';
        field.setAttribute('aria-invalid', 'true');
        
        // Remove existing error message if present
        const existingError = field.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#ba1a1a';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '4px';
        errorDiv.textContent = '❌ ' + message;
        field.parentElement.appendChild(errorDiv);
    }
}

/**
 * Clear error message
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '';
        field.setAttribute('aria-invalid', 'false');
        
        const errorDiv = field.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

// ====================
// VALIDATION FUNCTIONS
// ====================

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email) && email.length <= 255;
}

/**
 * Validate password strength
 */
function isValidPassword(password) {
    return password && password.length >= 6;
}

/**
 * Validate name (min 2 chars, no special characters)
 */
function isValidName(name) {
    const nameRegex = /^[a-zA-ZÀ-ÿ\s'-]{2,}$/;
    return nameRegex.test(name.trim());
}

/**
 * Validate number in range
 */
function isValidNumber(value, min, max) {
    const num = parseInt(value);
    return !isNaN(num) && num >= min && num <= max;
}

/**
 * Calculate BMI
 */
function calculateBMI(height, weight) {
    if (height <= 0 || weight <= 0) return null;
    return (weight / ((height / 100) ** 2)).toFixed(1);
}

// ====================
// LOGIN FORM VALIDATION
// ====================

function validateLoginForm() {
    let isValid = true;
    
    // Get form fields
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    
    // Validate email
    if (!email) {
        showValidationError('login-email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showValidationError('login-email', 'Please enter a valid email address');
        isValid = false;
    } else {
        clearValidationError('login-email');
    }
    
    // Validate password
    if (!password) {
        showValidationError('login-password', 'Password is required');
        isValid = false;
    } else if (password.length < 6) {
        showValidationError('login-password', 'Password must be at least 6 characters');
        isValid = false;
    } else {
        clearValidationError('login-password');
    }
    
    return isValid;
}

// ====================
// REGISTER FORM VALIDATION
// ====================

function validateRegisterForm() {
    let isValid = true;
    
    // Get form fields
    const lastname = document.getElementById('register-lastname').value.trim();
    const firstname = document.getElementById('register-firstname').value.trim();
    const email = document.getElementById('register-email').value.trim();
    const password = document.getElementById('register-password').value;
    const taille = document.getElementById('taille').value;
    const poids = document.getElementById('poids').value;
    
    // Validate last name
    if (!lastname) {
        showValidationError('register-lastname', 'Last name is required');
        isValid = false;
    } else if (!isValidName(lastname)) {
        showValidationError('register-lastname', 'Last name must be at least 2 characters and contain only letters');
        isValid = false;
    } else {
        clearValidationError('register-lastname');
    }
    
    // Validate first name
    if (!firstname) {
        showValidationError('register-firstname', 'First name is required');
        isValid = false;
    } else if (!isValidName(firstname)) {
        showValidationError('register-firstname', 'First name must be at least 2 characters and contain only letters');
        isValid = false;
    } else {
        clearValidationError('register-firstname');
    }
    
    // Validate email
    if (!email) {
        showValidationError('register-email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showValidationError('register-email', 'Please enter a valid email address');
        isValid = false;
    } else {
        clearValidationError('register-email');
    }
    
    // Validate password
    if (!password) {
        showValidationError('register-password', 'Password is required');
        isValid = false;
    } else if (!isValidPassword(password)) {
        showValidationError('register-password', 'Password must be at least 6 characters');
        isValid = false;
    } else {
        clearValidationError('register-password');
    }
    
    // Validate height (if provided)
    if (taille && !isValidNumber(taille, 100, 250)) {
        showValidationError('taille', 'Height must be between 100 and 250 cm');
        isValid = false;
    } else {
        clearValidationError('taille');
    }
    
    // Validate weight (if provided)
    if (poids && !isValidNumber(poids, 30, 200)) {
        showValidationError('poids', 'Weight must be between 30 and 200 kg');
        isValid = false;
    } else {
        clearValidationError('poids');
    }
    
    // Calculate and display BMI if both height and weight are provided
    if (taille && poids) {
        const bmi = calculateBMI(taille, poids);
        if (bmi) {
            const bmiValue = document.getElementById('bmi-value');
            if (bmiValue) {
                bmiValue.textContent = `IMC: ${bmi} (${getBMICategory(bmi)})`;
                bmiValue.style.color = getBMIColor(bmi);
            }
        }
    }
    
    return isValid;
}

/**
 * Get BMI category
 */
function getBMICategory(bmi) {
    if (bmi < 18.5) return '🔵 Insuffisant';
    if (bmi < 25) return '🟢 Normal';
    if (bmi < 30) return '🟡 Surpoids';
    return '🔴 Obésité';
}

/**
 * Get BMI color
 */
function getBMIColor(bmi) {
    if (bmi < 18.5) return '#0060a8';
    if (bmi < 25) return '#006e1c';
    if (bmi < 30) return '#f9a825';
    return '#ba1a1a';
}

// ====================
// POST FORM VALIDATION (for creating/editing posts)
// ====================

function validatePostForm() {
    let isValid = true;
    
    // Get form fields
    const titre = document.getElementById('post-title-input')?.value.trim() || '';
    const contenu = document.getElementById('post-content-input')?.value.trim() || '';
    
    // Validate title
    if (!titre) {
        showValidationError('post-title-input', 'Post title is required');
        isValid = false;
    } else if (titre.length < 3) {
        showValidationError('post-title-input', 'Title must be at least 3 characters');
        isValid = false;
    } else if (titre.length > 50) {
        showValidationError('post-title-input', 'Title must not exceed 50 characters');
        isValid = false;
    } else {
        clearValidationError('post-title-input');
    }
    
    // Validate content
    if (!contenu) {
        showValidationError('post-content-input', 'Post content is required');
        isValid = false;
    } else if (contenu.length < 10) {
        showValidationError('post-content-input', 'Content must be at least 10 characters');
        isValid = false;
    } else if (contenu.length > 500) {
        showValidationError('post-content-input', 'Content must not exceed 500 characters');
        isValid = false;
    } else {
        clearValidationError('post-content-input');
    }
    
    return isValid;
}
    
function validateEditPostForm() {
    let isValid = true;
    
    // Get form fields
    const titre = document.getElementById('edit-post-title-input')?.value.trim() || '';
    const contenu = document.getElementById('edit-post-content-input')?.value.trim() || '';
    
    // Validate title
    if (!titre) {
        showValidationError('edit-post-title-input', 'Post title is required');
        isValid = false;
    } else if (titre.length < 3) {
        showValidationError('edit-post-title-input', 'Title must be at least 3 characters');
        isValid = false;
    } else if (titre.length > 50) {
        showValidationError('edit-post-title-input', 'Title must not exceed 50 characters');
        isValid = false;
    } else {
        clearValidationError('edit-post-title-input');
    }
    
    // Validate content
    if (!contenu) {
        showValidationError('edit-post-content-input', 'Post content is required');
        isValid = false;
    } else if (contenu.length < 10) {
        showValidationError('edit-post-content-input', 'Content must be at least 10 characters');
        isValid = false;
    } else if (contenu.length > 500) {
        showValidationError('edit-post-content-input', 'Content must not exceed 500 characters');
        isValid = false;
    } else {
        clearValidationError('edit-post-content-input');
    }
    
    return isValid;
}

// ====================
// CONTACT FORM VALIDATION
// ====================

function validateContactForm() {
    let isValid = true;
    
    // Get form fields
    const firstname = document.querySelector('input[placeholder="Marie"]')?.value.trim() || '';
    const lastname = document.querySelector('input[placeholder="Dupont"]')?.value.trim() || '';
    const email = document.querySelector('input[placeholder="marie@exemple.fr"]')?.value.trim() || '';
    const subject = document.querySelector('input[placeholder="Demande d\'information"]')?.value.trim() || '';
    
    // Validate first name
    if (!firstname) {
        showToast('❌ First name is required');
        isValid = false;
    } else if (firstname.length < 2) {
        showToast('❌ First name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate last name
    if (!lastname) {
        showToast('❌ Last name is required');
        isValid = false;
    } else if (lastname.length < 2) {
        showToast('❌ Last name must be at least 2 characters');
        isValid = false;
    }
    
    // Validate email
    if (!email) {
        showToast('❌ Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showToast('❌ Please enter a valid email address');
        isValid = false;
    }
    
    // Validate subject
    if (!subject) {
        showToast('❌ Subject is required');
        isValid = false;
    } else if (subject.length < 5) {
        showToast('❌ Subject must be at least 5 characters');
        isValid = false;
    }
    
    return isValid;
}

// ====================
// COMMENT FORM VALIDATION
// ====================

function validateCommentForm() {
    let isValid = true;
    
    // Get form field
    const contenu = document.getElementById('comment-content-input')?.value.trim() || '';
    
    // Clear previous errors
    const errorMsg = document.getElementById('comment-error-message');
    if (errorMsg) {
        errorMsg.style.display = 'none';
        errorMsg.textContent = '';
    }
    
    // Validate content
    if (!contenu) {
        const msg = 'Comment content is required';
        if (errorMsg) {
            errorMsg.textContent = msg;
            errorMsg.style.display = 'block';
        }
        showValidationError('comment-content-input', msg);
        isValid = false;
    } else if (contenu.length < 2) {
        const msg = 'Comment must be at least 2 characters';
        if (errorMsg) {
            errorMsg.textContent = msg;
            errorMsg.style.display = 'block';
        }
        showValidationError('comment-content-input', msg);
        isValid = false;
    } else if (contenu.length > 2000) {
        const msg = 'Comment must not exceed 2000 characters';
        if (errorMsg) {
            errorMsg.textContent = msg;
            errorMsg.style.display = 'block';
        }
        showValidationError('comment-content-input', msg);
        isValid = false;
    } else {
        clearValidationError('comment-content-input');
    }
    
    return isValid;
}

// ====================
// REAL-TIME VALIDATION
// ====================

/**
 * Setup real-time email validation
 */
function setupEmailValidation(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('blur', function() {
            if (this.value.trim() && !isValidEmail(this.value.trim())) {
                showValidationError(inputId, 'Invalid email format');
            } else {
                clearValidationError(inputId);
            }
        });
    }
}

/**
 * Setup real-time password validation
 */
function setupPasswordValidation(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('blur', function() {
            if (this.value && !isValidPassword(this.value)) {
                showValidationError(inputId, 'Password must be at least 6 characters');
            } else {
                clearValidationError(inputId);
            }
        });
    }
}

/**
 * Setup real-time BMI calculation
 */
function setupBMICalculation() {
    const tailleField = document.getElementById('taille');
    const poidsField = document.getElementById('poids');
    
    if (tailleField && poidsField) {
        [tailleField, poidsField].forEach(field => {
            field.addEventListener('input', function() {
                const taille = tailleField.value;
                const poids = poidsField.value;
                const bmiValue = document.getElementById('bmi-value');
                
                if (taille && poids && bmiValue) {
                    const bmi = calculateBMI(taille, poids);
                    if (bmi) {
                        bmiValue.textContent = `IMC: ${bmi} (${getBMICategory(bmi)})`;
                        bmiValue.style.color = getBMIColor(bmi);
                    }
                }
            });
        });
    }
}

// ====================
// INITIALIZE VALIDATION
// ====================

/**
 * Initialize all form validations on page load
 */
function initializeFormValidation() {
    // Setup real-time validations
    setupEmailValidation('login-email');
    setupPasswordValidation('login-password');
    setupEmailValidation('register-email');
    setupPasswordValidation('register-password');
    setupBMICalculation();
    setupCommentCounter();
}

/**
 * Setup comment character counter
 */
function setupCommentCounter() {
    const commentInput = document.getElementById('comment-content-input');
    const commentCounter = document.getElementById('comment-counter');
    
    if (commentInput && commentCounter) {
        commentInput.addEventListener('input', function() {
            commentCounter.textContent = this.value.length + '/2000';
        });
    }
}

// Run on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFormValidation);
} else {
    initializeFormValidation();
}
