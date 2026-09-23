<?php

// gap.php - Admin: GAP Certificate Review

session_start();

// authentication

/*if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}*/

require_once '../includes/dbConnection.php';

// fetch owners with stores and GAP certificates

$stmt = $pdo->query("
    SELECT 
        u.id AS owner_id,
        u.full_name AS owner_name,
        u.email AS owner_email,
        u.city,
        u.gap_certificate,
        s.id AS store_id,
        s.store_name,
        s.description AS store_description,
        s.image AS store_image
    FROM users u
    LEFT JOIN stores s ON s.owner_id = u.id
    WHERE u.role = 'owner'
    ORDER BY u.full_name ASC
");
$owners = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['full_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAP Certificates - CropS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7f5;
            color: #1b1c1c;
            min-height: 100vh;
        }

        .admin-header {
            background: #154212;
            color: #fff;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .admin-header h1 {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-header .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 14px;
        }

        .admin-header .admin-info a {
            color: #fff;
            text-decoration: none;
            padding: 6px 14px;
            background: rgba(255,255,255,0.15);
            border-radius: 9999px;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.2s;
        }

        .admin-header .admin-info a:hover { background: rgba(255,255,255,0.25); }

        .main-wrapper {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #154212;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title .count-badge {
            background: #e8f5e9;
            color: #154212;
            font-size: 13px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 9999px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: #f0f2f0;
            color: #1b1c1c;
            border: 1px solid #dce3dc;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover { background: #e5e9e5; transform: translateY(-2px); }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 1px solid #dce3dc;
            border-radius: 9999px;
            padding: 12px 20px;
            margin-bottom: 26px;
            max-width: 420px;
        }

        .search-bar i { color: #6b7280; }

        .search-bar input {
            border: none;
            outline: none;
            font-size: 14px;
            width: 100%;
            font-family: inherit;
            background: transparent;
            color: #1b1c1c;
        }

        .cert-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .cert-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #eef3ee;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cert-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        /* ---- Store Header ---- */
        .cert-header {
            padding: 18px 20px 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid #f1f3f1;
        }

        .store-thumb {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: #e8f5e9;
            color: #154212;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .store-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .store-info { flex: 1; min-width: 0; }

        .store-name {
            font-size: 15px;
            font-weight: 700;
            color: #1b1c1c;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .owner-name {
            font-size: 12px;
            color: #6b7280;
        }

        .cert-body {
            padding: 18px 20px 20px;
        }

        .cert-label {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .cert-preview {
            width: 100%;
            height: 180px;
            border-radius: 12px;
            background: #f8faf8;
            border: 2px dashed #dce3dc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.2s;
            position: relative;
        }

        .cert-preview:hover { border-color: #154212; }

        .cert-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cert-preview .placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: #9ca3af;
            font-size: 13px;
        }

        .cert-preview .placeholder i {
            font-size: 32px;
            color: #c2c9bb;
        }

        .cert-preview .zoom-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(21, 66, 18, 0.85);
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .no-cert {
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #eef3ee;
        }

        .empty-state i {
            font-size: 48px;
            color: #c2c9bb;
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h3 {
            font-size: 18px;
            color: #1b1c1c;
            margin-bottom: 6px;
        }

        .empty-state p { font-size: 14px; color: #6b7280; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .modal-overlay.active { display: flex; }

        .modal-overlay img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        }

        .modal-close {
            position: absolute;
            top: 24px;
            right: 30px;
            font-size: 32px;
            color: #fff;
            cursor: pointer;
            background: rgba(255,255,255,0.15);
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover { background: rgba(255,255,255,0.3); }

        @media (max-width: 900px) {
            .main-wrapper { padding: 24px 18px; }
            .admin-header { padding: 14px 20px; }
            .page-title { font-size: 20px; }
            .cert-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>🌿 CropS Admin</h1>
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($adminName); ?></span>
            <a href="../logout.php">Logout</a>
        </div>
    </header>

    <div class="main-wrapper">

        <!-- Page topbar -->
        <div class="page-topbar">
            <h2 class="page-title">
                GAP Certificate Review
                <span class="count-badge"><?php echo count($owners); ?> owners</span>
            </h2>
            <a href="admin_dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Search bar -->
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search by store name or owner..." autocomplete="off">
        </div>

        <!-- Certificate grid -->
        <?php if (count($owners) > 0): ?>
            <div class="cert-grid" id="certGrid">
                <?php foreach ($owners as $owner): ?>
                    <?php
                        $storeName = $owner['store_name'] ?: $owner['owner_name'];
                        $hasCert = !empty($owner['gap_certificate']);
                        $certPath = $hasCert ? '../' . htmlspecialchars($owner['gap_certificate']) : '';
                    ?>
                    <div class="cert-card" data-searchable="<?php echo strtolower(htmlspecialchars($storeName . ' ' . $owner['owner_name'])); ?>">

                        <!-- Store Header -->
                        <div class="cert-header">
                            <div class="store-thumb">
                                <?php if (!empty($owner['store_image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($owner['store_image']); ?>" alt="<?php echo htmlspecialchars($storeName); ?>">
                                <?php else: ?>
                                    <i class="fa-solid fa-store"></i>
                                <?php endif; ?>
                            </div>
                            <div class="store-info">
                                <div class="store-name"><?php echo htmlspecialchars($storeName); ?></div>
                                <div class="owner-name">Owner: <?php echo htmlspecialchars($owner['owner_name']); ?></div>
                            </div>
                        </div>

                        <!-- Certificate Body -->
                        <div class="cert-body">
                            <div class="cert-label">GAP Certificate</div>

                            <?php if ($hasCert): ?>
                                <div class="cert-preview" onclick="openCertModal('<?php echo $certPath; ?>')">
                                    <img src="<?php echo $certPath; ?>" 
                                         alt="GAP Certificate" 
                                         onerror="this.parentElement.innerHTML='<div class=\'placeholder\'><i class=\'fa-solid fa-file-circle-xmark\'></i><span>File not found</span></div>'">
                                    <div class="zoom-icon"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                                </div>
                            <?php else: ?>
                                <div class="cert-preview">
                                    <div class="no-cert">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        No GAP certificate uploaded
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <h3>No owners found</h3>
                <p>There are no greenhouse owner accounts in the system yet.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Full-screen image modal -->
    <div class="modal-overlay" id="certModal">
        <button class="modal-close" onclick="closeCertModal()">&times;</button>
        <img id="certModalImg" src="" alt="Certificate Preview">
    </div>

    <script>
        // Search filter
        const searchInput = document.getElementById('searchInput');
        const cards = document.querySelectorAll('.cert-card');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                cards.forEach(card => {
                    const searchable = card.getAttribute('data-searchable') || '';
                    card.style.display = searchable.includes(query) ? '' : 'none';
                });
            });
        }

        // Image modal
        function openCertModal(src) {
            const modal = document.getElementById('certModal');
            const img = document.getElementById('certModalImg');
            img.src = src;
            modal.classList.add('active');
        }

        function closeCertModal() {
            document.getElementById('certModal').classList.remove('active');
        }

        // Close modal on ESC or clicking overlay
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeCertModal();
        });

        document.getElementById('certModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeCertModal();
        });
    </script>

</body>
</html>