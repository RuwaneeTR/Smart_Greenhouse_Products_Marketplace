<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Plant Picks</title>
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
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        /* ============================================================
           POPUP CONTAINER
           ============================================================ */
        .popup {
            background: #ffffff;
            max-width: 780px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px 38px;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            position: relative;
            animation: fadeSlide 0.4s ease;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .popup::-webkit-scrollbar {
            width: 6px;
        }
        .popup::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .popup::-webkit-scrollbar-thumb {
            background: #c8e6c9;
            border-radius: 10px;
        }
        .popup::-webkit-scrollbar-thumb:hover {
            background: #a5d6a7;
        }

        /* ============================================================
           CLOSE BUTTON
           ============================================================ */
        .close-btn {
            position: sticky;
            float: right;
            top: 0;
            font-size: 26px;
            color: #ccc;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.25s, transform 0.2s;
            z-index: 5;
            padding: 4px 8px;
            line-height: 1;
        }
        .close-btn:hover {
            color: #1a2e1f;
            transform: rotate(90deg);
        }

        /* ============================================================
           HEADER
           ============================================================ */
        .header-icon {
            font-size: 40px;
            display: block;
            margin-bottom: 6px;
        }

        .popup-title {
            font-size: 26px;
            font-weight: 800;
            color: #1a2e1f;
            letter-spacing: -0.5px;
        }

        .popup-subtitle {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-top: 6px;
            margin-bottom: 22px;
            max-width: 90%;
        }

        /* ============================================================
           CLIMATE SUMMARY
           ============================================================ */
        .climate-summary {
            background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
            border-radius: 16px;
            padding: 16px 22px;
            margin-bottom: 28px;
            border-left: 5px solid #2e7d32;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 24px;
        }

        .climate-summary .location {
            font-weight: 700;
            color: #1a2e1f;
            font-size: 15px;
        }

        .climate-summary .badge {
            background: #2e7d32;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 30px;
            letter-spacing: 0.3px;
        }

        .climate-summary .stat {
            font-size: 13px;
            color: #2d4a2a;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .climate-summary .stat i {
            font-style: normal;
        }

        /* ============================================================
           PLANT CARDS
           ============================================================ */
        .plant-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .plant-card {
            background: #fafcfa;
            border-radius: 16px;
            padding: 18px 22px;
            border: 1px solid #eef3ee;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .plant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            border-radius: 16px 0 0 16px;
        }

        .plant-card:hover {
            border-color: #c8e6c9;
            transform: translateX(6px);
            box-shadow: 0 8px 30px rgba(46, 125, 50, 0.08);
        }

        .plant-card.excellent::before {
            background: #2e7d32;
        }
        .plant-card.good::before {
            background: #f59e0b;
        }
        .plant-card.moderate::before {
            background: #f97316;
        }
        .plant-card.low::before {
            background: #ef4444;
        }

        /* ============================================================
           PLANT CARD CONTENT
           ============================================================ */
        .plant-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }

        .plant-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a2e1f;
        }

        .plant-match {
            font-size: 13px;
            font-weight: 700;
            padding: 4px 16px;
            border-radius: 40px;
            white-space: nowrap;
            letter-spacing: 0.3px;
            border: 1px solid transparent;
        }

        .plant-match.excellent {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #a5d6a7;
        }
        .plant-match.good {
            background: #fef3c7;
            color: #b45309;
            border-color: #fcd34d;
        }
        .plant-match.moderate {
            background: #ffedd5;
            color: #9a3412;
            border-color: #fdba74;
        }
        .plant-match.low {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .plant-desc {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin: 8px 0 10px 0;
            padding-right: 10px;
        }

        .plant-requirements {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 14px;
        }

        .plant-requirements span {
            background: #f1f5f1;
            padding: 3px 14px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .plant-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
        }

        .btn-primary {
            background: #2e7d32;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        .btn-secondary {
            background: #f1f3f1;
            color: #4b5563;
            border: 1px solid #d1d5d1;
        }
        .btn-secondary:hover {
            background: #e5e7e5;
            transform: translateY(-2px);
        }

        /* ============================================================
           BOTTOM ACTION BAR
           ============================================================ */
        .bottom-bar {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #eef3ee;
            flex-wrap: wrap;
        }

        .btn-change-district {
            padding: 12px 32px;
            background: #f1f3f1;
            color: #2e7d32;
            border: 2px solid #2e7d32;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-change-district:hover {
            background: #2e7d32;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.25);
        }

        .btn-close-popup {
            padding: 12px 32px;
            background: #f1f3f1;
            color: #4b5563;
            border: 1px solid #d1d5d1;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
        }

        .btn-close-popup:hover {
            background: #e5e7e5;
            transform: translateY(-2px);
        }

        /* ============================================================
           EMPTY STATE
           ============================================================ */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state .icon {
            font-size: 56px;
            display: block;
            margin-bottom: 16px;
        }

        .empty-state h2 {
            color: #1a2e1f;
            font-size: 22px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 600px) {
            .popup {
                padding: 28px 18px;
                border-radius: 18px;
            }
            .popup-title {
                font-size: 21px;
            }
            .plant-top {
                flex-direction: column;
                align-items: flex-start;
            }
            .plant-actions {
                width: 100%;
            }
            .plant-actions .btn {
                flex: 1;
                justify-content: center;
            }
            .climate-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .popup-subtitle {
                max-width: 100%;
            }
            .bottom-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .bottom-bar .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    POPUP
    ============================================================ -->
    <div class="popup" id="popupContainer">

        <!-- Close Button -->
        <button class="close-btn" onclick="window.location.href='plants.php'">&times;</button>

        <!-- Content loaded by JavaScript -->
        <div id="resultsContent"></div>

    </div>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        const data = JSON.parse(sessionStorage.getItem('recommendations'));
        const container = document.getElementById('resultsContent');

        if (!data || !data.success || data.total === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="icon">🌱</span>
                    <h2>No recommendations found</h2>
                    <p>We couldn't find plants matching your district.<br>Try another location or check your spelling.</p>
                    <button class="btn btn-primary" onclick="window.location.href='recommendation.php'">Try Again →</button>
                </div>
            `;
        } else {
            // ============================================================
            // HEADER
            // ============================================================
            let html = `
                <span class="header-icon">🌿</span>
                <h1 class="popup-title">Your Personalized Plant Picks</h1>
                <p class="popup-subtitle">
                    These commercial-grade varieties are optimized for your local microclimate, 
                    considering recent rainfall patterns and seasonal soil conditions.
                </p>

                <!-- Climate Summary -->
                <div class="climate-summary">
                    <span class="location">📍 ${data.district}</span>
                    <span class="badge">${data.zone}</span>
                    <span class="stat">🌡️ ${data.temperature_range || '—'}</span>
                    <span class="stat">💧 ${data.humidity_range || '—'}</span>
                    <span class="stat">☔ ${data.rainfall_range || '—'}</span>
                    <span class="stat" style="color:#2e7d32;font-weight:600;">${data.total} plants found</span>
                </div>

                <div class="plant-grid">
            `;

            // ============================================================
            // PLANT CARDS 
            // ============================================================
            data.results.forEach(plant => {
                let matchClass = 'low';
                if (plant.score >= 80) matchClass = 'excellent';
                else if (plant.score >= 60) matchClass = 'good';
                else if (plant.score >= 40) matchClass = 'moderate';

                // Store plant for advice page
                sessionStorage.setItem('plant_' + plant.id, JSON.stringify(plant));

                html += `
                    <div class="plant-card ${matchClass}">
                        <div class="plant-top">
                            <span class="plant-name">${plant.name}</span>
                            <span class="plant-match ${matchClass}">${plant.score}% MATCH</span>
                        </div>
                        <p class="plant-desc">${plant.description || 'No description available.'}</p>
                        <div class="plant-requirements">
                            <span>🌡️ ${plant.requirements.temp}</span>
                            <span>💧 ${plant.requirements.humidity}</span>
                            <span>☔ ${plant.requirements.rainfall}</span>
                        </div>
                        <div class="plant-actions">
                            <button class="btn btn-primary" onclick="openAdvice(${plant.id})">
                                📖 View Advice
                            </button>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;

            // ============================================================
            // BOTTOM ACTION BAR (Change District + Close)
            // ============================================================
            html += `
                <div class="bottom-bar">
                    <button class="btn-change-district" onclick="window.location.href='recommendation.php'">
                        🔄 Change District
                    </button>
                    <button class="btn-close-popup" onclick="window.location.href='plants.php'">
                        ✕ Close
                    </button>
                </div>
            `;

            container.innerHTML = html;
        }

        // ============================================================
        // OPEN ADVICE
        // ============================================================
        function openAdvice(id) {
            const plant = data.results.find(p => p.id === id);
            if (plant) {
                sessionStorage.setItem('selected_plant', JSON.stringify(plant));
                window.location.href = 'recommendation_advice.php';
            }
        }
    </script>

</body>
</html>