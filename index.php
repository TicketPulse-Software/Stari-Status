<?php
include 'db.php';

// Fetch services
$query = $pdo->query('SELECT * FROM services');
$services = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch incidents within the last 90 days
$incidentQuery = $pdo->prepare('SELECT * FROM incidents WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) ORDER BY created_at DESC');
$incidentQuery->execute();
$incidents = $incidentQuery->fetchAll(PDO::FETCH_ASSOC);

// Function to get incident for a specific day
function getIncidentsForDay($date, $incidents) {
    $dayIncidents = [];
    foreach ($incidents as $incident) {
        if (date('Y-m-d', strtotime($incident['created_at'])) === $date) {
            $dayIncidents[] = $incident;
        }
    }
    return $dayIncidents;
}

// Function to get status class
function getStatusClass($status) {
    return $status === 'up' ? 'Operational' : ($status === 'down' ? 'Major Outage' : 'Degraded Performance');
}

// Function to get status color
function getStatusColor($status) {
    return $status === 'up' ? 'text-green-600 dark:text-green-400' : ($status === 'down' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400');
}

// Function to get status background color
function getStatusBgColor($status) {
    return $status === 'up' ? 'bg-green-400' : ($status === 'down' ? 'bg-red-400' : 'bg-yellow-400');
}

// Function to get uptime bars
function getUptimeBars($serviceId, $pdo) {
    $date = new DateTime();
    $date->modify('-90 days');
    $pastDate = $date->format('Y-m-d');

    $stmt = $pdo->prepare('SELECT DATE(checked_at) as day, status FROM service_logs WHERE service_id = ? AND checked_at >= ?');
    $stmt->execute([$serviceId, $pastDate]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bars = array_fill(0, 90, 'bg-gray-300'); // Default to unknown status
    foreach ($logs as $log) {
        $dayIndex = (new DateTime($log['day']))->diff(new DateTime($pastDate))->days;
        $bars[$dayIndex] = $log['status'] === 'up' ? 'bg-green-400' : ($log['status'] === 'down' ? 'bg-red-400' : 'bg-yellow-400');
    }
    return $bars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Service Status</title>
    <link rel="stylesheet" href="css/styles.css" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine.min.js" defer></script>
</head>
<body>
    <div class="dark:bg-dark">
        <header class="py-8 md:py-12 mb-8">
            <div class="container flex flex-col items-center md:flex-row justify-between">
                <div class="logo mb-8 md:mb-0 text-gray-800 dark:text-gray-100">
                    <!-- Add your logo here -->
                    <h1 class="text-3xl" style="color:#fff;">Service Status</h1>
                </div>
                <div class="links flex space-x-2 items-center leading-none font-medium">
                    <button x-data="{ dark: false }" @click="dark = !dark; document.body.classList.toggle('dark')" type="button" class="inline-flex items-center p-2 border border-gray-300 dark:border-dark rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2">
                        <svg x-show="dark" style="display: none" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg x-show="!dark" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-dark rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22" fill="currentColor" aria-hidden="true">
                            <path d="M11 15h2v2h-2v-2m0-8h2v6h-2V7m1-5C6.47 2 2 6.5 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18a8 8 0 0 1-8-8a8 8 0 0 1 8-8a8 8 0 0 1 8 8a8 8 0 0 1-8 8z" fill="currentColor"></path>
                        </svg>
                        Report an Issue
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-dark rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.5002 19.2498c.9625 0 1.75-.7875 1.75-1.75H8.75024c0 .9625.7875 1.75 1.74996 1.75zm5.25-5.25V9.62478c0-2.68625-1.4262-4.93499-3.9375-5.52999v-.595c0-.72625-.5862-1.3125-1.3125-1.3125-.72621 0-1.31246.58625-1.31246 1.3125v.595c-2.5025.595-3.9375 2.83499-3.9375 5.52999v4.37502l-1.75 1.75v.875H17.5002v-.875l-1.75-1.75zm-1.75.875H7.00024V9.62478c0-2.17 1.32125-3.9375 3.49996-3.9375 2.1788 0 3.5 1.7675 3.5 3.9375v5.25002zM6.63274 3.56979L5.38149 2.31854c-2.1 1.60125-3.4825 4.06874-3.605 6.86874h1.75c.06198-1.10966.37347-2.19105.91128-3.16366.53781-.9726 1.28809-1.81136 2.19497-2.45383zM17.474 9.18728h1.75c-.1313-2.8-1.5138-5.26749-3.605-6.86874l-1.2425 1.25125c.903.64563 1.6499 1.48532 2.1859 2.4574.536.97208.8475 2.05188.9116 3.16009z"></path>
                        </svg>
                        Subscribe
                    </button>
                </div>
            </div>
            <div class="container">
                <div class="flex items-center p-5 mt-8 md:mt-24 status font-semibold text-dark-green">
                    <svg class="mr-4" width="29" height="29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.3733 0c6.8363 0 12.3734 2.87113 12.3734 6.41593 0 3.5448-5.5371 6.41597-12.3734 6.41597C5.53707 12.8319 0 9.96073 0 6.41593 0 2.87113 5.53707 0 12.3733 0zM0 9.62389c0 3.54481 5.53707 6.41591 12.3733 6.41591 6.8363 0 12.3734-2.8711 12.3734-6.41591v4.94031L23.2 14.4358c-4.0059 0-7.424 2.6306-8.7232 6.3197l-2.1035.0963C5.53707 20.8518 0 17.9806 0 14.4358V9.62389zm0 8.01991c0 3.5448 5.53707 6.4159 12.3733 6.4159H13.92c0 1.6842.4176 3.2722 1.16 4.6516l-2.7067.1604C5.53707 28.8717 0 26.0006 0 22.4558v-4.812zM21.6533 29L17.4 24.1881l1.7941-1.8607 2.4592 2.5343 5.5526-5.7422L29 21.3811 21.6533 29z" />
                    </svg>
                    All Systems Operational
                </div>
            </div>
        </header>

        <main>
            <h2 class="container text-xs tracking-wide text-gray-500 dark:text-gray-300 uppercase font-bold mb-8">
                Monitors
            </h2>
            <div class="monitors space-y-6">
                <?php foreach ($services as $service): ?>
                <div class="monitor py-8 <?= getStatusBgColor($service['status']) ?> bg-opacity-10">
                    <div class="container flex items-center justify-between mb-3">
                        <h3 class="text-2xl text-gray-800 dark:text-gray-100"><?= htmlspecialchars($service['name']) ?></h3>
                        <span class="<?= getStatusColor($service['status']) ?> font-semibold"><?= getStatusClass($service['status']) ?></span>
                    </div>
                    <div class="container bars">
                        <div class="flex space-x-px">
                            <?php foreach (getUptimeBars($service['id'], $pdo) as $barClass): ?>
                            <div class="bars <?= $barClass ?> flex-1 h-10 rounded hover:opacity-80 cursor-pointer"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="container mt-2">
                        <div class="flex items-center">
                            <span class="pr-2 flex-shrink-0 <?= getStatusColor($service['status']) ?> text-xs font-semibold">90 days ago</span>
                            <div class="h-px <?= getStatusColor($service['status']) ?> w-full"></div>
                            <span class="px-2 flex-shrink-0 <?= getStatusColor($service['status']) ?> text-xs font-semibold">Today</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="incidents py-16">
                <h2 class="container text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    Past Incidents
                </h2>
                <div class="container past-incidents">
                    <?php
                    $date = new DateTime();
                    for ($i = 0; $i < 90; $i++) {
                        $dateString = $date->format('Y-m-d');
                        $dayIncidents = getIncidentsForDay($dateString, $incidents);
                        ?>
                        <div class="day mt-12">
                            <h4 class="inline-block bg-gray-100 dark:bg-gray-800 rounded-md px-2 font-medium pb-1 mb-2 text-gray-900 dark:text-gray-300">
                                <? echo "<hr>"; ?>
                                <?= $date->format('M d, Y') ?>
                            </h4>
                            <div class="incidents text-gray-700 dark:text-gray-300">
                                <?php if (empty($dayIncidents)): ?>
                                    <p>No incidents reported.</p>
                                <?php else: ?>
                                    <?php foreach ($dayIncidents as $incident): ?>
                                        <div class="text-blue-700 dark:text-blue-400 text-xl font-semibold">
                                            <?= htmlspecialchars($incident['title']) ?>
                                        </div>
                                        <p class="mt-3">
                                            <strong class="text-gray-900 dark:text-gray-300"><?= htmlspecialchars($incident['status']) ?></strong> - <?= htmlspecialchars($incident['description']) ?>
                                            <div class="text-gray-500"><?= date('M d, H:i T', strtotime($incident['created_at'])) ?></div>
                                        </p>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        $date->modify('-1 day');
                        echo "<br>";
                        echo "<hr>";
                    }
                    ?>
                </div>
            </div>
        </main>

        <footer class="py-16 text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-800">
            <div class="container flex justify-between">
                <a href="#" class="flex items-center">
                    <svg class="h-4 w-4 mr-1 -mb-px" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true">
                        <path d="M224 128a8 8 0 0 1-8 8H59.314l58.343 58.343a8 8 0 0 1-11.314 11.314l-72-72a8 8 0 0 1 0-11.314l72-72a8 8 0 0 1 11.314 11.314L59.314 120H216a8 8 0 0 1 8 8z" fill="currentColor"></path>
                    </svg>
                    Incident history
                </a>
                <div>
                    Powered by <span class="font-semibold text-black dark:text-white">TicketPulse Software | Stari-Status</span>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>


