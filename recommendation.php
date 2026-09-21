<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Plant Recommendations</title>
    <style>
        /* ============================================================
           RESET & BASE
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: rgba(0, 0, 0, 0.5);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ============================================================
           POPUP CARD
           ============================================================ */
        .popup {
            background: #ffffff;
            max-width: 480px;
            width: 100%;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Close (X) Button */
        .close-btn {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 24px;
            color: #bbb;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.2s;
            font-weight: 300;
        }
        .close-btn:hover {
            color: #333;
        }

        /* ============================================================
           TYPOGRAPHY
           ============================================================ */
        .popup-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a2e1f;
            margin-bottom: 8px;
        }

        .popup-subtitle {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* ============================================================
           FORM
           ============================================================ */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            color: #1f2937;
            outline: none;
            transition: border-color 0.2s;
            background: #fff;
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .form-group input:focus {
            border-color: #2e7d32;
        }

        /* ============================================================
           BUTTON
           ============================================================ */
        .btn-find {
            width: 100%;
            padding: 14px;
            background: #2e7d32;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 4px;
        }

        .btn-find:hover {
            background: #1b5e20;
        }

        .btn-find:active {
            transform: scale(0.98);
        }

        .btn-find:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        /* ============================================================
           MESSAGES
           ============================================================ */
        .error-msg {
            color: #dc2626;
            font-size: 14px;
            margin-top: 10px;
            display: none;
        }

        .error-msg.show {
            display: block;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 12px 0 6px 0;
        }

        .loading-spinner.show {
            display: block;
        }

        .spinner {
            border: 3px solid #e5e7eb;
            border-top: 3px solid #2e7d32;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 520px) {
            .popup {
                padding: 30px 20px;
            }
            .popup-title {
                font-size: 19px;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    POPUP
    ============================================================ -->
    <div class="popup">

        <!-- Close (X) Button -->
        <button class="close-btn" onclick="window.location.href='plants.php'">&times;</button>

        <!-- Title -->
        <h1 class="popup-title">Get Plant Recommendations</h1>
        <p class="popup-subtitle">
            Discover the perfect crops and ornamental plants optimized for your specific microclimate and district conditions.
        </p>

        <!-- Form -->
        <form id="recommendationForm">
            <div class="form-group">
                <label for="districtInput">District</label>
                <input 
                    type="text" 
                    id="districtInput" 
                    placeholder="e.g:- Ratnapura" 
                    autocomplete="off"
                >
            </div>

            <div id="errorMsg" class="error-msg">⚠️ Please add your district</div>

            <div id="loadingSpinner" class="loading-spinner">
                <div class="spinner"></div>
            </div>

            <button type="submit" class="btn-find" id="findBtn">
                Find Recommendations →
            </button>
        </form>

    </div>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        const form = document.getElementById('recommendationForm');
        const districtInput = document.getElementById('districtInput');
        const errorMsg = document.getElementById('errorMsg');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const findBtn = document.getElementById('findBtn');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const district = districtInput.value.trim();

            if (district === '') {
                errorMsg.classList.add('show');
                return;
            }

            errorMsg.classList.remove('show');

            // Show loading
            loadingSpinner.classList.add('show');
            findBtn.disabled = true;
            findBtn.textContent = 'Searching...';

            try {
                const response = await fetch(`api/get_recommendations.php?district=${encodeURIComponent(district)}`);
                const data = await response.json();

                if (data.success && data.total > 0) {
                    sessionStorage.setItem('recommendations', JSON.stringify(data));
                    window.location.href = 'recommendation_results.php';
                } else {
                    alert(data.error || 'No matching plants found for your district.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to fetch recommendations. Please try again.');
            } finally {
                loadingSpinner.classList.remove('show');
                findBtn.disabled = false;
                findBtn.textContent = 'Find Recommendations →';
            }
        });

        // Enter key submits form
        districtInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                form.dispatchEvent(new Event('submit'));
            }
        });
    </script>

</body>
</html>