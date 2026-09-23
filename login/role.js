/**
 * VerdantHub Authentication System (Role Selection, Customer & Greenhouse Owner Registration, and Login)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Views inside index.html
    const roleSelectionView = document.getElementById('role-selection-view');
    const customerFormView = document.getElementById('customer-form-view');
    const ownerFormView = document.getElementById('owner-form-view');

    // Role Selection Cards
    const cardCustomer = document.getElementById('card-customer');
    const cardOwner = document.getElementById('card-owner');

    // Action Triggers
    const backToRolesTriggers = document.querySelectorAll('.back-to-roles-trigger');
    const togglePassBtns = document.querySelectorAll('.toggle-pass-btn');

    // Forms
    const customerRegForm = document.getElementById('customer-reg-form');
    const ownerRegForm = document.getElementById('owner-reg-form');
    const loginForm = document.getElementById('login-form');

    // GAP File Upload Elements
    const ownerGapFileInput = document.getElementById('owner-gap-file');
    const selectedFilename = document.getElementById('selected-filename');
    const fileUploadDropzone = document.getElementById('file-upload-dropzone');

    /**
     * Switch sub-view inside registration page (index.html)
     * @param {HTMLElement} targetView 
     */
    function switchRegView(targetView) {
        if (!roleSelectionView) return;
        [roleSelectionView, customerFormView, ownerFormView].forEach(v => {
            if (v) v.classList.remove('active');
        });
        if (targetView) targetView.classList.add('active');
    }

    /**
     * Return back to Role Selection View
     */
    function resetToRoleSelection() {
        if (cardCustomer) cardCustomer.classList.remove('selected');
        if (cardOwner) cardOwner.classList.remove('selected');
        switchRegView(roleSelectionView);
    }

    // Role Card Clicks
    if (cardCustomer) {
        cardCustomer.addEventListener('click', () => {
            cardCustomer.classList.add('selected');
            if (cardOwner) cardOwner.classList.remove('selected');
            setTimeout(() => {
                switchRegView(customerFormView);
            }, 200);
        });
    }

    if (cardOwner) {
        cardOwner.addEventListener('click', () => {
            cardOwner.classList.add('selected');
            if (cardCustomer) cardCustomer.classList.remove('selected');
            setTimeout(() => {
                switchRegView(ownerFormView);
            }, 200);
        });
    }

    // Back to roles triggers
    backToRolesTriggers.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            resetToRoleSelection();
        });
    });

    // GAP File Upload Display
    if (ownerGapFileInput && selectedFilename) {
        ownerGapFileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                selectedFilename.textContent = `Attached: ${e.target.files[0].name}`;
            } else {
                selectedFilename.textContent = '';
            }
        });
    }

    // Drag & Drop for File Upload Box
    if (fileUploadDropzone && ownerGapFileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                fileUploadDropzone.classList.add('hover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadDropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                fileUploadDropzone.classList.remove('hover');
            }, false);
        });

        fileUploadDropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                ownerGapFileInput.files = files;
                if (selectedFilename) {
                    selectedFilename.textContent = `Attached: ${files[0].name}`;
                }
            }
        });
    }

    // Toggle Password Visibility
    togglePassBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const inputBox = btn.closest('.input-box');
            if (!inputBox) return;
            const input = inputBox.querySelector('input');
            if (!input) return;

            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    // Form Submissions
    if (customerRegForm) {
    customerRegForm.addEventListener('submit', (e) => {
        const pass = document.getElementById('cust-password').value;
        const confirmPass = document.getElementById('cust-confirm-password').value;

        if (pass !== confirmPass) {
            e.preventDefault();
            alert('Passwords do not match. Please try again.');
        }
    });

}

    if (ownerRegForm) {
        ownerRegForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const pass = document.getElementById('owner-password').value;
            const confirmPass = document.getElementById('owner-confirm-password').value;

            if (pass !== confirmPass) {
                alert('Passwords do not match. Please try again.');
                return;
            }

            alert('Greenhouse Owner account created successfully!');
            ownerRegForm.reset();
            if (selectedFilename) selectedFilename.textContent = '';
            resetToRoleSelection();
        });
    }

    if (loginForm) {
        //loginForm.addEventListener('submit', (e) => {
            //e.preventDefault();
           // alert('Welcome back! Logged in successfully.');
            //loginForm.reset();
        //});
    }
});
