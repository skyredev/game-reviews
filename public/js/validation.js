/**
 * Client-side validation matching backend rules
 */
class ClientValidator {
    constructor(form, rules) {
        this.form = form;
        this.rules = rules;
        this.errors = {};
    }

    validate() {
        this.errors = {};
        const formData = new FormData(this.form);
        const data = {};
        const files = {};

        // Convert FormData to objects
        for (const [key, value] of formData.entries()) {
            if (value instanceof File) {
                files[key] = value;
            } else {
                // Handle arrays (checkboxes with name="field[]")
                if (key.endsWith('[]')) {
                    const arrayKey = key.slice(0, -2);
                    if (!data[arrayKey]) {
                        data[arrayKey] = [];
                    }
                    data[arrayKey].push(value);
                } else {
                    // For radio buttons and regular fields, last value wins
                    // For checkboxes with same name, collect all values
                    if (key in data) {
                        if (!Array.isArray(data[key])) {
                            data[key] = [data[key]];
                        }
                        data[key].push(value);
                    } else {
                        data[key] = value;
                    }
                }
            }
        }
        
        // Convert single-item arrays back to values (for radio buttons)
        for (const key in data) {
            if (Array.isArray(data[key]) && data[key].length === 1) {
                data[key] = data[key][0];
            }
        }

        // Validate each field
        for (const [field, fieldRules] of Object.entries(this.rules)) {
            const value = data[field];
            const file = files[field];

            for (const rule of fieldRules) {
                if (typeof rule === 'string') {
                    this.applyRule(field, value, file, rule, []);
                } else if (Array.isArray(rule)) {
                    const ruleName = rule[0];
                    const ruleParams = rule.slice(1);
                    this.applyRule(field, value, file, ruleName, ruleParams);
                }
            }
        }

        return this.errors;
    }

    applyRule(field, value, file, rule, params) {
        switch (rule) {
            case 'required':
                if (file !== undefined && file !== null) {
                    if (!file || file.size === 0) {
                        this.addError(field, 'Toto pole je povinné.');
                    }
                } else if (value === undefined || value === null || value === '') {
                    this.addError(field, 'Toto pole je povinné.');
                }
                break;

            case 'string':
                if (value !== undefined && value !== null && value !== '' && typeof value !== 'string') {
                    this.addError(field, 'Hodnota musí být text.');
                }
                break;

            case 'email':
                if (value !== undefined && value !== null && value !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        this.addError(field, 'Neplatný formát emailu.');
                    }
                }
                break;

            case 'min':
                const min = params[0] || 0;
                if (value !== undefined && value !== null && value !== '' && String(value).length < min) {
                    this.addError(field, `Hodnota musí mít alespoň ${min} znaků.`);
                }
                break;

            case 'max':
                const max = params[0] || 0;
                if (value !== undefined && value !== null && value !== '' && String(value).length > max) {
                    this.addError(field, `Hodnota nesmí mít více než ${max} znaků.`);
                }
                break;

            case 'username':
                if (value !== undefined && value !== null && value !== '') {
                    const usernameRegex = /^(?![._-])[A-Za-z0-9._-]+(?<![._-])$/;
                    if (!usernameRegex.test(value)) {
                        this.addError(field, 'Uživatelské jméno nesmí začínat ani končit tečkou, pomlčkou nebo podtržítkem a nesmí obsahovat jiné specialní znaky.');
                    } else if (value.length < 3) {
                        this.addError(field, 'Uživatelské jméno musí mít alespoň 3 znaky.');
                    }
                }
                break;

            case 'password':
                if (value !== undefined && value !== null && value !== '') {
                    if (value.length < 8) {
                        this.addError(field, 'Heslo musí mít alespoň 8 znaků.');
                    }
                    if (!/[A-Z]/.test(value)) {
                        this.addError(field, 'Heslo musí obsahovat alespoň jedno velké písmeno.');
                    }
                    if (!/\d/.test(value)) {
                        this.addError(field, 'Heslo musí obsahovat alespoň jedno číslo.');
                    }
                    if (!/[^A-Za-z0-9]/.test(value)) {
                        this.addError(field, 'Heslo musí obsahovat alespoň jeden speciální znak.');
                    }
                }
                break;

            case 'confirmed':
                if (field === 'password_confirmation') {
                    const passwordField = this.form.querySelector('[name="password"]');
                    const originalValue = passwordField ? passwordField.value : null;
                    if (value !== undefined && value !== null && value !== '' && originalValue && value !== originalValue) {
                        this.addError(field, 'Hesla se neshodují.');
                    }
                } else {
                    const confirmField = params[0] || field + '_confirmation';
                    const confirmInput = this.form.querySelector(`[name="${confirmField}"]`);
                    const confirmValue = confirmInput ? confirmInput.value : null;
                    if (value !== undefined && value !== null && value !== '' && value !== confirmValue) {
                        this.addError(field, 'Hodnoty se neshodují.');
                    }
                }
                break;

            case 'image':
                if (file !== undefined && file !== null && file.size > 0) {
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        this.addError(field, 'Podporované formáty: JPG, PNG, WEBP, GIF.');
                    }
                }
                break;

            case 'image_max_size':
                const maxSize = params[0] || 5242880; // 5MB default
                if (file !== undefined && file !== null && file.size > 0) {
                    if (file.size > maxSize) {
                        const maxSizeMB = Math.round(maxSize / 1048576 * 10) / 10;
                        this.addError(field, `Obrázek nesmí být větší než ${maxSizeMB} MB.`);
                    }
                }
                break;

            case 'email_part_min':
                const emailMin = params[0] || 4;
                if (value !== undefined && value !== null && value !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailRegex.test(value)) {
                        const emailPart = value.split('@')[0];
                        if (emailPart.length < emailMin) {
                            this.addError(field, `Část před @ musí mít alespoň ${emailMin} znaky.`);
                        }
                    }
                }
                break;

            case 'identifier':
                if (value === undefined || value === null || value === '') {
                    this.addError(field, 'Toto pole je povinné.');
                }
                break;

            case 'year':
                const currentYear = new Date().getFullYear();
                const minYear = params[0] || 1980;
                const maxYear = params[1] || currentYear;
                if (value !== undefined && value !== null && value !== '') {
                    const year = parseInt(value, 10);
                    if (isNaN(year) || year < minYear || year > maxYear) {
                        this.addError(field, `Rok musí být mezi ${minYear} a ${maxYear}.`);
                    }
                }
                break;

            case 'array_not_empty':
                if (!Array.isArray(value) || value.length === 0) {
                    this.addError(field, 'Vyberte alespoň jednu možnost.');
                }
                break;

            case 'rating':
                const ratingMin = params[0] || 1;
                const ratingMax = params[1] || 10;
                if (value !== undefined && value !== null && value !== '') {
                    const rating = parseInt(value, 10);
                    if (isNaN(rating) || rating < ratingMin || rating > ratingMax) {
                        this.addError(field, `Hodnocení musí být mezi ${ratingMin} a ${ratingMax}.`);
                    }
                }
                break;
        }
    }

    addError(field, message) {
        if (!this.errors[field]) {
            this.errors[field] = [];
        }
        this.errors[field].push(message);
    }

    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }

    displayErrors() {
        // Clear visual errors first (but keep this.errors intact)
        this.clearVisualErrors();

        // Display new errors
        for (const [field, messages] of Object.entries(this.errors)) {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                
                // Find form-row container
                const formRow = input.closest('.form-row');
                if (formRow) {
                    // Add error messages
                    messages.forEach(message => {
                        const errorEl = document.createElement('small');
                        errorEl.className = 'error';
                        errorEl.textContent = message;
                        formRow.appendChild(errorEl);
                    });
                }
            }
        }
    }

    clearVisualErrors() {
        // Remove error class from all inputs
        this.form.querySelectorAll('input.error, textarea.error, select.error').forEach(el => {
            el.classList.remove('error');
        });
        
        // Remove all error messages
        this.form.querySelectorAll('small.error').forEach(el => {
            el.remove();
        });
    }

    clearErrors() {
        // Reset errors object
        this.errors = {};
        
        // Clear visual errors
        this.clearVisualErrors();
    }
}

/**
 * Initialize validation for forms with data-validation-rules attribute
 */
document.addEventListener('DOMContentLoaded', function() {
    // Forms with inline rules (from data attribute)
    document.querySelectorAll('form[data-validation-rules]').forEach(form => {
        try {
            const rules = JSON.parse(form.getAttribute('data-validation-rules'));
            const validator = new ClientValidator(form, rules);
            
            form.addEventListener('submit', function(e) {
                // Always clear errors first
                validator.clearErrors();
                
                // Validate
                validator.validate();
                
                // If there are errors, prevent submit and display them
                if (validator.hasErrors()) {
                    e.preventDefault();
                    validator.displayErrors();
                }
            });
        } catch (err) {
            console.error('Error parsing validation rules:', err);
        }
    });
});

