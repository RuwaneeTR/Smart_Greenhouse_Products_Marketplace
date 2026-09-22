<?php
session_start();

if (file_exists('../includes/dbConnection.php')) {
    require_once '../includes/dbConnection.php';
} else {
    require_once '../database/dbConnection.php';
}

// Auth guard — owner only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ../login/login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch owner
$stmt = $pdo->prepare("SELECT full_name, city, profile_image FROM users WHERE id = ?");
$stmt->execute([$userId]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    header('Location: ../login/login.php');
    exit;
}

$fullName = $owner['full_name'] ?? 'Store Owner';

// Compute initials
$nameParts = array_values(array_filter(explode(' ', trim($fullName))));
$initials = count($nameParts) >= 2
    ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
    : strtoupper(substr($nameParts[0] ?? 'TO', 0, 2));

// Fetch owner's store
$stmt = $pdo->prepare("SELECT id, store_name FROM stores WHERE owner_id = ? LIMIT 1");
$stmt->execute([$userId]);
$store = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
$error   = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $category    = $_POST['category'] ?? '';
    $quantity    = (int)($_POST['quantity'] ?? 0);

    if (!$store) {
        $error = 'You need to create a store first before adding products.';
    } elseif (empty($name) || empty($description) || $price <= 0 || !in_array($category, ['vegetable','fruit','plant']) || $quantity < 0) {
        $error = 'Please fill in all fields with valid values.';
    } elseif (empty($_FILES['image']['name']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a product image.';
    } else {
        // Handle image upload
        $uploadDir = __DIR__ . '/../uploads/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = 'Image must be JPG, PNG, or WebP.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $error = 'Image must be under 5MB.';
        } else {
            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            $physical = $uploadDir . $filename;
            $dbPath   = 'uploads/products/' . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $physical)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO products 
                        (store_id, name, description, price, category, quantity, image, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $store['id'], $name, $description, $price, $category, $quantity, $dbPath
                    ]);
                    $success = 'Product added successfully!';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            } else {
                $error = 'Failed to save image file.';
            }
        }
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="owner_dashboard_v2.css">   <!-- sidebar + shared -->
<link rel="stylesheet" href="add_product.css">           <!-- form-specific -->

<!-- Overlay backdrop for mobile drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="dashboard-layout">
    <!-- Left Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <!-- Profile Mini-Card at Top -->
        <div class="sidebar-user-mini">
            <div class="mini-avatar-circle">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div class="mini-user-info">
                <h4 class="mini-name"><?= htmlspecialchars($fullName) ?></h4>
                <span class="mini-role">Store Owner</span>
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="sidebar-menu">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php" class="sidebar-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Home (Dashboard)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/add_product.php" class="sidebar-link active">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Add Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/order_details.php" class="sidebar-link">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Order Details</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/notifications.php" class="sidebar-link">
                        <i class="fa-solid fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/members.php" class="sidebar-link">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Membership Fee</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/edit_profile.php" class="sidebar-link">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Edit Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/reviews.php" class="sidebar-link">
                        <i class="fa-solid fa-star"></i>
                        <span>Reviews and Ratings</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-divider"></div>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- 1. Page Header -->
        <div class="page-header-wrapper">
            <div class="header-title-row">
                <h1 class="page-title">Add New Product</h1>
            </div>
            <p class="page-subtitle">List a new item in your greenhouse store. Fields marked with * are required.</p>
        </div>

        <!-- 2. Success/Error Banner -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check alert-icon"></i>
                <div class="alert-content">
                    <span class="alert-title">Success</span>
                    <p class="alert-message"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation alert-icon"></i>
                <div class="alert-content">
                    <span class="alert-title">Error</span>
                    <p class="alert-message"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3. Form Card -->
        <div class="form-card">
            <form method="POST" action="add_product.php" enctype="multipart/form-data" id="addProductForm">
                <div class="form-two-column">
                    <!-- LEFT COLUMN (60%) -->
                    <div class="form-column-left">
                        <div class="form-group">
                            <label for="productName" class="form-label">Product Name <span class="required">*</span></label>
                            <input type="text" id="productName" name="name" class="form-input" placeholder="e.g., Organic Red Tomatoes" value="<?= htmlspecialchars(!empty($success) ? '' : ($_POST['name'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="productDescription" class="form-label">Description <span class="required">*</span></label>
                            <textarea id="productDescription" name="description" class="form-textarea" rows="4" placeholder="Detailed description of the product..." required><?= htmlspecialchars(!empty($success) ? '' : ($_POST['description'] ?? '')) ?></textarea>
                        </div>

                        <div class="form-row-two">
                            <div class="form-group">
                                <label for="productPrice" class="form-label">Price (Rs.) <span class="required">*</span></label>
                                <div class="input-prefix-wrapper">
                                    <span class="input-prefix">Rs.</span>
                                    <input type="number" id="productPrice" name="price" class="form-input input-with-prefix" step="0.01" min="0" placeholder="0.00/100g" value="<?= htmlspecialchars(!empty($success) ? '' : ($_POST['price'] ?? '')) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="productCategory" class="form-label">Category <span class="required">*</span></label>
                                <div class="select-wrapper">
                                    <select id="productCategory" name="category" class="form-select" required>
                                        <option value="" disabled <?= empty($_POST['category']) || !empty($success) ? 'selected' : '' ?>>Select category</option>
                                        <option value="vegetable" <?= (!empty($_POST['category']) && $_POST['category'] === 'vegetable' && empty($success)) ? 'selected' : '' ?>>Vegetables</option>
                                        <option value="fruit" <?= (!empty($_POST['category']) && $_POST['category'] === 'fruit' && empty($success)) ? 'selected' : '' ?>>Fruits</option>
                                        <option value="plant" <?= (!empty($_POST['category']) && $_POST['category'] === 'plant' && empty($success)) ? 'selected' : '' ?>>Plants</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down select-arrow"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="productQuantity" class="form-label">Available Quantity <span class="required">*</span></label>
                            <input type="number" id="productQuantity" name="quantity" class="form-input" min="0" placeholder="e.g. 50g" value="<?= htmlspecialchars(!empty($success) ? '' : ($_POST['quantity'] ?? '')) ?>" required>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN (40%) -->
                    <div class="form-column-right">
                        <div class="form-group image-upload-group">
                            <label class="form-label">Product Image Upload <span class="required">*</span></label>
                            
                            <div class="upload-dropzone" id="dropzone">
                                <input type="file" id="imageInput" name="image" accept=".jpg,.jpeg,.png,.webp" class="file-input-hidden" required>
                                
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <div class="upload-icon-wrapper">
                                        <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                                    </div>
                                    <span class="upload-prompt">Click or drag & drop to upload</span>
                                    <span class="upload-hint">JPG, PNG or WebP. Max 5MB.</span>
                                </div>

                                <div class="upload-preview-container hidden" id="previewContainer">
                                    <img src="" id="imagePreview" alt="Product Image Preview" class="preview-img">
                                    <div class="file-details">
                                        <span class="filename-text" id="filenameDisplay"></span>
                                        <button type="button" class="btn-change-image" id="changeImgBtn">
                                            <i class="fa-solid fa-arrows-rotate"></i> Change Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Bottom Action Row -->
                <div class="form-action-row">
                    <a href="/Smart_Greenhouse_Products_Marketplace/owner/owner_dashboard.php" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                    <div class="action-buttons-group">
                        <button type="reset" class="btn-cancel" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-plus"></i>
                            <span>Add Product</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone');
    const imageInput = document.getElementById('imageInput');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const filenameDisplay = document.getElementById('filenameDisplay');
    const addProductForm = document.getElementById('addProductForm');
    const cancelBtn = document.getElementById('cancelBtn');

    let currentObjectUrl = null;

    dropzone.addEventListener('click', function(e) {
        if (e.target.closest('#changeImgBtn')) {
            imageInput.click();
            return;
        }
        if (!previewContainer.classList.contains('hidden') && e.target.closest('#previewContainer')) {
            return;
        }
        imageInput.click();
    });

    imageInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('is-dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('is-dragover');
        }, false);
    });

    dropzone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            imageInput.files = files;
            handleFiles(files);
        }
    });

    function handleFiles(files) {
        if (!files || files.length === 0) return;

        const file = files[0];
        
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
        }

        currentObjectUrl = URL.createObjectURL(file);
        imagePreview.src = currentObjectUrl;
        filenameDisplay.textContent = file.name;

        uploadPlaceholder.classList.add('hidden');
        previewContainer.classList.remove('hidden');
    }

    function resetImagePreview() {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
        imagePreview.src = '';
        filenameDisplay.textContent = '';
        previewContainer.classList.add('hidden');
        uploadPlaceholder.classList.remove('hidden');
    }

    addProductForm.addEventListener('reset', function() {
        setTimeout(resetImagePreview, 10);
    });

    cancelBtn.addEventListener('click', function() {
        addProductForm.reset();
    });

    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function() {
            document.getElementById('sidebar')?.classList.remove('active');
            sidebarBackdrop.classList.remove('active');
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
