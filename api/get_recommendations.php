<?php
header('Content-Type: application/json');

include '../includes/dbConnection.php';

$district = isset($_GET['district']) ? $_GET['district'] : '';

if (empty($district)) {
    echo json_encode(['error' => 'Please enter your district']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT zone_name, 
               typical_temp_min, typical_temp_max,
               typical_humidity_min, typical_humidity_max,
               annual_rainfall_min, annual_rainfall_max
        FROM agro_ecological_zones 
        WHERE district LIKE ?
    ");
    $stmt->execute(['%' . $district . '%']);
    $zone = $stmt->fetch();

    if (!$zone) {
        echo json_encode(['error' => 'District not found']);
        exit;
    }

    $plants = $pdo->query("SELECT * FROM plant_recommendations")->fetchAll();
    $results = [];

    foreach ($plants as $plant) {
        $score = 0;

        // Temperature (40)
        if ($zone['typical_temp_min'] >= $plant['min_temp_c'] && 
            $zone['typical_temp_max'] <= $plant['max_temp_c']) {
            $score += 40;
        } elseif ($zone['typical_temp_min'] >= $plant['min_temp_c'] || 
                  $zone['typical_temp_max'] <= $plant['max_temp_c']) {
            $score += 20;
        }

        // Humidity (30)
        if ($zone['typical_humidity_min'] >= $plant['min_humidity_pct'] && 
            $zone['typical_humidity_max'] <= $plant['max_humidity_pct']) {
            $score += 30;
        } elseif ($zone['typical_humidity_min'] >= $plant['min_humidity_pct'] || 
                  $zone['typical_humidity_max'] <= $plant['max_humidity_pct']) {
            $score += 15;
        }

        // Rainfall (20)
        if ($zone['annual_rainfall_min'] >= $plant['min_rainfall_mm'] && 
            $zone['annual_rainfall_max'] <= $plant['max_rainfall_mm']) {
            $score += 20;
        } elseif ($zone['annual_rainfall_min'] >= $plant['min_rainfall_mm'] || 
                  $zone['annual_rainfall_max'] <= $plant['max_rainfall_mm']) {
            $score += 10;
        }

        // Zone Bonus (10)
        if (stripos($plant['description'], $zone['zone_name']) !== false) {
            $score += 10;
        }

        if ($score > 0) {
            $results[] = [
                'id' => $plant['id'],
                'name' => $plant['plant_name'],
                'description' => $plant['description'] ?: 'No description.',
                'score' => $score,
                'requirements' => [
                    'temp' => $plant['min_temp_c'] . '°C - ' . $plant['max_temp_c'] . '°C',
                    'humidity' => $plant['min_humidity_pct'] . '% - ' . $plant['max_humidity_pct'] . '%',
                    'rainfall' => $plant['min_rainfall_mm'] . ' - ' . $plant['max_rainfall_mm'] . 'mm'
                ]
            ];
        }
    }

    usort($results, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    echo json_encode([
        'success' => true,
        'district' => $district,
        'zone' => $zone['zone_name'],
        'total' => count($results),
        'results' => $results
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>