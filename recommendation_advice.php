<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Advice</title>
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
            max-width: 520px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px 35px;
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
           TOP BAR (Close + Back)
           ============================================================ */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .top-bar .close-btn {
            font-size: 26px;
            color: #ccc;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.25s, transform 0.2s;
            padding: 4px 8px;
            line-height: 1;
        }
        .top-bar .close-btn:hover {
            color: #1a2e1f;
            transform: rotate(90deg);
        }

        .btn-back {
            padding: 6px 16px;
            background: #f1f3f1;
            color: #2e7d32;
            border: 2px solid #2e7d32;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: #2e7d32;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.25);
        }

        /* ============================================================
           PLANT ICON & NAME
           ============================================================ */
        .plant-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 4px;
        }

        .plant-name {
            font-size: 24px;
            font-weight: 800;
            color: #1a2e1f;
            letter-spacing: -0.3px;
        }

        .plant-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 22px;
        }

        /* ============================================================
           ADVICE SECTIONS
           ============================================================ */
        .advice-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 22px;
        }

        .advice-card {
            background: #fafcfa;
            border-radius: 16px;
            padding: 16px 20px;
            border: 1px solid #eef3ee;
            transition: all 0.25s ease;
        }

        .advice-card:hover {
            border-color: #c8e6c9;
            transform: translateX(4px);
            box-shadow: 0 4px 16px rgba(46, 125, 50, 0.06);
        }

        .advice-card .advice-icon {
            font-size: 18px;
            margin-right: 8px;
        }

        .advice-card h4 {
            font-size: 14px;
            font-weight: 700;
            color: #1a2e1f;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }

        .advice-card p {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin: 0;
            padding-left: 30px;
        }

        /* ============================================================
           CLIMATE REQUIREMENTS
           ============================================================ */
        .climate-section {
            background: #f1f8f1;
            border-radius: 16px;
            padding: 16px 20px;
            border: 1px solid #dce8dc;
            margin-bottom: 22px;
        }

        .climate-section h4 {
            font-size: 13px;
            font-weight: 700;
            color: #1a2e1f;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .climate-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .climate-tags span {
            background: #ffffff;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 12px;
            color: #2d4a2a;
            border: 1px solid #c8dcc8;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ============================================================
           EMPTY STATE
           ============================================================ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state .icon {
            font-size: 48px;
            display: block;
            margin-bottom: 12px;
        }

        .empty-state h2 {
            color: #1a2e1f;
            font-size: 20px;
            margin-bottom: 6px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 16px;
        }

        .btn-try-again {
            padding: 10px 28px;
            background: #2e7d32;
            color: #fff;
            border: none;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
        }

        .btn-try-again:hover {
            background: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 600px) {
            .popup {
                padding: 28px 18px;
                border-radius: 18px;
            }
            .plant-name {
                font-size: 20px;
            }
            .plant-icon {
                font-size: 38px;
            }
            .advice-card p {
                padding-left: 0;
            }
            .top-bar {
                flex-wrap: wrap;
            }
            .btn-back {
                font-size: 11px;
                padding: 4px 12px;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    POPUP
    ============================================================ -->
    <div class="popup" id="popupContainer">

        <!-- Top Bar: Close + Back -->
        <div class="top-bar">
            <button class="close-btn" onclick="window.location.href='recommendation_results.php'">&times;</button>
            <button class="btn-back" onclick="window.location.href='recommendation_results.php'">
                ← Back to Results
            </button>
        </div>

        <!-- Content loaded by JavaScript -->
        <div id="adviceContent"></div>

    </div>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        const plant = JSON.parse(sessionStorage.getItem('selected_plant'));
        const container = document.getElementById('adviceContent');

        if (!plant) {
            container.innerHTML = `
                <div class="empty-state">
                    <span class="icon">🌿</span>
                    <h2>No advice found</h2>
                    <p>We couldn't find growing advice for this plant.</p>
                    <button class="btn-try-again" onclick="window.location.href='recommendation_results.php'">
                        Go Back
                    </button>
                </div>
            `;
        } else {
            // ============================================================
            // GENERATE ADVICE
            // ============================================================
            const tips = {
                'Tomato': 'Plant seeds 0.5cm deep in well-draining soil. Maintain spacing of 45cm between plants. Use stakes or cages for support.',
                'Cucumber': 'Plant seeds directly in well-drained soil. Maintain spacing of 45cm between plants. Optimum temperature 30°C.',
                'Chilli': 'Start seeds in seedbeds. Transplant when 10-15cm tall. Space plants 45cm apart.',
                'Mango': 'Plant seeds or saplings in well-drained soil. Space 8-10m between trees.',
                'Banana': 'Plant suckers in well-drained soil. Space 3-4m between plants.',
                'Pomegranate': 'Plant saplings in well-drained soil. Space 4-5m between trees.',
                'Pineapple': 'Plant crowns 30-45cm apart. Well-drained soil required.',
                'Rambutan': 'Plant saplings in well-drained soil. Space 8-10m between trees.',
                'Durian': 'Plant saplings in well-drained soil. Space 10-12m between trees.',
                'Cinnamon': 'Plant seeds in nursery. Transplant when 50cm tall. Space 1-2m between plants.',
                'Potato': 'Plant seed potatoes 10cm deep. Space 30cm between plants. Cool climate required.',
                'Onions': 'Plant bulbs 2-3cm deep. Space 10-15cm apart. Well-drained soil required.',
                'Green Gram': 'Plant seeds 2-3cm deep. Space rows 30-45cm apart. Rainfed crop.'
            };

            const watering = {
                'Tomato': 'Water deeply but infrequently. Avoid wetting leaves to prevent fungal diseases. Drip irrigation recommended.',
                'Cucumber': 'Water deeply and regularly. Avoid wetting leaves to prevent fungal diseases. Drip irrigation recommended.',
                'Chilli': 'Water regularly. Avoid waterlogging. Good drainage is essential.',
                'Mango': 'Water regularly during establishment. Drought tolerant once mature.',
                'Banana': 'Requires consistent moisture. Water regularly during dry periods.'
            };

            const pest = {
                'Tomato': 'Watch for aphids, whiteflies, and tomato hornworms. Use neem oil spray or introduce beneficial insects.',
                'Cucumber': 'Watch for aphids and whiteflies. Use neem oil spray for organic control.',
                'Chilli': 'Watch for thrips and fruit borer. Use appropriate pest control measures.'
            };

            const name = plant.name;
            const defaultPlanting = 'Plant in well-drained soil. Water regularly. Provide adequate sunlight.';
            const defaultWatering = 'Water regularly. Keep soil moist but not waterlogged.';
            const defaultPest = 'Monitor regularly for pests. Use organic pest control methods when possible.';

            // ============================================================
            // BUILD HTML
            // ============================================================
            let html = `
                <span class="plant-icon">🌿</span>
                <h1 class="plant-name">${plant.name}</h1>
                <p class="plant-subtitle">Growing advice for your selected plant</p>

                <div class="advice-grid">
                    <div class="advice-card">
                        <h4><span class="advice-icon">🌱</span> Planting</h4>
                        <p>${tips[name] || defaultPlanting}</p>
                    </div>
                    <div class="advice-card">
                        <h4><span class="advice-icon">💧</span> Watering</h4>
                        <p>${watering[name] || defaultWatering}</p>
                    </div>
                    <div class="advice-card">
                        <h4><span class="advice-icon">🐞</span> Pest Control</h4>
                        <p>${pest[name] || defaultPest}</p>
                    </div>
                </div>

                <div class="climate-section">
                    <h4>📋 Climate Requirements</h4>
                    <div class="climate-tags">
                        <span>🌡️ ${plant.requirements.temp}</span>
                        <span>💧 ${plant.requirements.humidity}</span>
                        <span>☔ ${plant.requirements.rainfall}</span>
                    </div>
                </div>
            `;

            container.innerHTML = html;
        }
    </script>

</body>
</html>