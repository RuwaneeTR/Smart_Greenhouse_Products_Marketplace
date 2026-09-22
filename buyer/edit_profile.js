/**
 * CropS – Smart Greenhouse Products Marketplace (VerdantHub)
 * Edit Profile Script (edit_profile.js)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Change Photo button → opens file picker
    const changeBtn = document.getElementById('changePhotoBtn');
    const fileInput = document.getElementById('avatarFileInput');
    const preview   = document.getElementById('avatarPreview');
    const form      = document.getElementById('editProfileForm');

    if (changeBtn && fileInput) {
        changeBtn.addEventListener('click', () => fileInput.click());
    }

    // 2. When a file is selected → preview + auto-submit
    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const url = URL.createObjectURL(this.files[0]);
                preview.innerHTML = '<img src="' + url + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
                if (form) form.submit();
            }
        });
    }
});

