/**
 * CropS – Edit Owner Profile Script
 */

document.getElementById('changePhotoBtn')?.addEventListener('click', () => {
    document.getElementById('avatarFileInput').click();
});

document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('editProfileForm');
    const toastNotification = document.getElementById('toastNotification');

    // Store initial form values for reset functionality
    const initialFormState = {};
    if (editForm) {
        const formData = new FormData(editForm);
        for (let [key, value] of formData.entries()) {
            initialFormState[key] = value;
        }
    }

    // 1. Toast Notification Helper
    function showToast(message, isError = false) {
        if (!toastNotification) return;
        toastNotification.innerHTML = isError 
            ? `<i class="fa-solid fa-circle-exclamation"></i> ${message}`
            : `<i class="fa-solid fa-circle-check"></i> ${message}`;
        
        if (isError) {
            toastNotification.style.backgroundColor = '#dc2626';
        } else {
            toastNotification.style.backgroundColor = 'var(--primary-green)';
        }

        toastNotification.classList.add('show');
        setTimeout(() => {
            toastNotification.classList.remove('show');
        }, 3500);
    }

   if (editForm) {
    editForm.addEventListener('submit', (e) => {
        // Form validation
        const emailInput = document.getElementById('emailInput');
        const fullNameInput = document.getElementById('fullNameInput');
        const phoneInput = document.getElementById('phoneInput');

        if (fullNameInput && !fullNameInput.value.trim()) {
            e.preventDefault();
            showToast('Please enter your full name.', true);
            fullNameInput.focus();
            return;
        }

        if (emailInput && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
            e.preventDefault();
            showToast('Please enter a valid email address.', true);
            emailInput.focus();
            return;
        }

        if (phoneInput && !phoneInput.value.trim()) {
            e.preventDefault();
            showToast('Please enter a valid phone number.', true);
            phoneInput.focus();
            return;
        }

        // Validation passed — let the form submit normally to the server
        // (no e.preventDefault() here, no fake toast — the page will reload
        // and the PHP $updateSuccess message will show instead)
    });
}

    // 3. Discard Changes Button
    const discardBtn = document.getElementById('discardBtn');
    if (discardBtn && editForm) {
        discardBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to discard unsaved changes?')) {
                editForm.reset();
                showToast('Changes discarded.');
            }
        });
    }

    // 4. Cancel Button (Redirect to owner dashboard)
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            window.location.href = '/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php';
        });
    }

    // 5. Change Photo & Avatar File Upload Handling
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const changePhotoLink = document.getElementById('changePhotoLink');
    const avatarFileInput = document.getElementById('avatarFileInput');
    const avatarPreviewCircle = document.getElementById('avatarPreviewCircle');
    const removePhotoLink = document.getElementById('removePhotoLink');
    const defaultInitials = avatarPreviewCircle ? avatarPreviewCircle.textContent.trim() : 'TO';

    function triggerFileUpload() {
        if (avatarFileInput) {
            avatarFileInput.click();
        }
    }

    if (changePhotoBtn) changePhotoBtn.addEventListener('click', triggerFileUpload);
    if (changePhotoLink) changePhotoLink.addEventListener('click', triggerFileUpload);

    if (avatarFileInput && avatarPreviewCircle) {
        avatarFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    showToast('File size exceeds 5MB limit.', true);
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (evt) {
                    avatarPreviewCircle.innerHTML = `<img src="${evt.target.result}" alt="Profile Preview">`;
                    showToast('Photo uploaded successfully.');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removePhotoLink && avatarPreviewCircle) {
        removePhotoLink.addEventListener('click', () => {
            if (avatarFileInput) avatarFileInput.value = '';
            avatarPreviewCircle.innerHTML = defaultInitials;
            showToast('Profile photo removed.');
        });
    }
        if (removePhotoLink && avatarPreviewCircle) {
        removePhotoLink.addEventListener('click', () => {
            if (avatarFileInput) avatarFileInput.value = '';
            avatarPreviewCircle.innerHTML = defaultInitials;
            showToast('Profile photo removed.');
        });
    }

    // 5b. Store Image Upload Handling
    document.getElementById('changeStoreImgBtn')?.addEventListener('click', () => {
        document.getElementById('storeImageInput').click();
    });

    const storeImageInput = document.getElementById('storeImageInput');
    if (storeImageInput) {
        storeImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    showToast('Store image exceeds 5MB limit.', true);
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (evt) {
                    const circle = storeImageInput.closest('.large-avatar-wrapper').querySelector('.large-avatar-circle');
                    if (circle) {
                        circle.innerHTML = `<img src="${evt.target.result}" style="width:100%;height:100%;border-radius:var(--radius-lg);object-fit:cover;" alt="Store Preview">`;
                    }
                    showToast('Store image selected — click Save Changes to upload.');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 6. GAP Re-verify Button Handler
    const reverifyBtn = document.getElementById('reverifyBtn');
const gapCertInput = document.getElementById('gapCertInput');
if (reverifyBtn && gapCertInput) {
    reverifyBtn.addEventListener('click', () => {
        gapCertInput.click();
    });
    gapCertInput.addEventListener('change', () => {
        if (gapCertInput.files.length > 0) {
            showToast('Certificate selected — click Save Changes to upload.');
        }
    });
}

    // 7. Mobile Sidebar Drawer Controls
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function toggleSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.toggle('active');
            sidebarBackdrop.classList.toggle('active');
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', toggleSidebar);
    }

    // 8. Logout Modal Controls
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

    if (logoutBtn && logoutModal) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logoutModal.classList.add('active');
        });
    }

    if (cancelLogoutBtn && logoutModal) {
        cancelLogoutBtn.addEventListener('click', () => {
            logoutModal.classList.remove('active');
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
                logoutModal.classList.remove('active');
            }
        });
    }

    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', () => {
            window.location.href = '/Smart_Greenhouse_Products_Marketplace/logout.php';
        });
    }
});
