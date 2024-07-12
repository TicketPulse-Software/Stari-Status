<?php
include 'db.php';

// Fetch services
$query = $pdo->query('SELECT * FROM services');
$services = $query->fetchAll(PDO::FETCH_ASSOC);

function getStatusClass($status) {
    return $status === 'up' ? 'Operational' : ($status === 'down' ? 'Major Outage' : 'Degraded Performance');
}

function getStatusColor($status) {
    return $status === 'up' ? 'text-green-600 dark:text-green-400' : ($status === 'down' ? 'text-red-600 dark:text-red-400' : ($status === 'degraded' ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400'));
}

function getStatusBgColor($status) {
    return $status === 'up' ? 'bg-green-400' : ($status === 'down' ? 'bg-red-400' : ($status === 'degraded' ? 'bg-yellow-400' : 'bg-gray-400'));
}

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
        $bars[$dayIndex] = $log['status'] === 'up' ? 'bg-green-400' : ($log['status'] === 'down' ? 'bg-red-400' : ($log['status'] === 'degraded' ? 'bg-yellow-400' : 'bg-gray-300'));
    }
    return $bars;
}

function calculateUptimePercentage($serviceId, $pdo) {
    $date = new DateTime();
    $date->modify('-90 days');
    $pastDate = $date->format('Y-m-d');

    $stmt = $pdo->prepare('SELECT status FROM service_logs WHERE service_id = ? AND checked_at >= ?');
    $stmt->execute([$serviceId, $pastDate]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $upDays = 0;
    foreach ($logs as $log) {
        if ($log['status'] === 'up') {
            $upDays++;
        }
    }
    $totalDays = 90;
    $uptimePercentage = ($upDays / $totalDays) * 100;

    // Ensure uptime percentage is between 0 and 100
    return max(0, min(100, $uptimePercentage));
}

function getIncidentsForDay($date, $pdo) {
    $stmt = $pdo->prepare('SELECT * FROM incidents WHERE DATE(created_at) = ?');
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getIncidentSteps($incidentId, $pdo) {
    $stmt = $pdo->prepare('SELECT * FROM incident_steps WHERE incident_id = ? ORDER BY created_at');
    $stmt->execute([$incidentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStatusIcon($status) {
    switch ($status) {
        case 'Investigating':
            return '<i class="bx bx-search-alt-2"></i>';
        case 'Identified':
            return '<i class="bx bxs-star-half"></i>';
        case 'Monitoring':
            return '<i class="bx bxs-component"></i>';
        case 'Resolved':
            return '<i class="bx bxs-check-circle"></i>';
        default:
            return '';
    }
}

function getOverallStatus($services) {
    $status = 'All Systems Operational';
    $icon = '<path d="M12.3733 0c6.8363 0 12.3734 2.87113 12.3734 6.41593 0 3.5448-5.5371 6.41597-12.3734 6.41597C5.53707 12.8319 0 9.96073 0 6.41593 0 2.87113 5.53707 0 12.3733 0zM0 9.62389c0 3.54481 5.53707 6.41591 12.3733 6.41591 6.8363 0 12.3734-2.8711 12.3734-6.41591v4.94031L23.2 14.4358c-4.0059 0-7.424 2.6306-8.7232 6.3197l-2.1035.0963C5.53707 20.8518 0 17.9806 0 14.4358V9.62389zm0 8.01991c0 3.5448 5.53707 6.4159 12.3733 6.4159H13.92c0 1.6842.4176 3.2722 1.16 4.6516l-2.7067.1604C5.53707 28.8717 0 26.0006 0 22.4558v-4.812zM21.6533 29L17.4 24.1881l1.7941-1.8607 2.4592 2.5343 5.5526-5.7422L29 21.3811 21.6533 29z" />';
    
    foreach ($services as $service) {
        if ($service['status'] === 'down') {
            $status = 'Major Outage';
            $icon = '<path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>';
            break;
        } elseif ($service['status'] === 'degraded') {
            $status = 'Degraded Performance';
            $icon = '<path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8zm-1-5h2v2h-2zm0-2h2v-6h-2z"/>';
        }
    }

    return [$status, $icon];
}

list($overallStatus, $statusIcon) = getOverallStatus($services);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Service Status</title>
    <link rel="stylesheet" href="css/styles.css" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.1/css/boxicons.min.css" integrity="sha512-JZ1zFV51ZD8L+kUsrsstA4l8G5U60iEjP6gXz7L2LZcFUkd7OMRu/b1UXReZ0HxSV7ABX2nOTL+ZvlP0gfybMQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .modal-enter {
            opacity: 0;
            transform: scale(90%);
        }
        .modal-enter-active {
            transition: opacity 0.2s ease, transform 0.2s ease;
            opacity: 1;
            transform: scale(100%);
        }
        .modal-leave {
            opacity: 1;
            transform: scale(100%);
        }
        .modal-leave-active {
            transition: opacity 0.2s ease, transform 0.2s ease;
            opacity: 0;
            transform: scale(90%);
        }
        .modal-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            width: 800px;
            max-width: 90%;
        }
        .incident-document {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .incident-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .incident-status {
            display: flex;
            align-items: center;
        }
        .incident-status i {
            margin-right: 8px;
        }
        .incident-body {
            margin-top: 10px;
        }
        .incident-step {
            margin-top: 15px;
            padding-left: 20px;
            border-left: 3px solid #ccc;
        }
        .incident-step i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="dark:bg-dark" x-data="{ showModal: false }">
        <header class="py-8 md:py-12 mb-8">
            <div class="container flex flex-col items-center md:flex-row justify-between">
                <div class="logo mb-8 md:mb-0 text-gray-800 dark:text-gray-100">
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
                    <button type="button" @click="showModal = true" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-dark rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 report-text">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22" fill="currentColor" aria-hidden="true">
                            <path d="M11 15h2v2h-2v-2m0-8h2v6h-2V7m1-5C6.47 2 2 6.5 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18a8 8 0 0 1-8-8a8 8 0 0 1 8-8a8 8 0 0 1 8 8a8 8 0 0 1-8 8z" fill="currentColor"></path>
                        </svg>
                        Report an Issue
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-dark rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-100 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 subscribe-text">
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
                        <?= $statusIcon ?>
                    </svg>
                    <?= $overallStatus ?>
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
                            <span class="pr-2 flex-shrink-0 text-green-500 text-xs font-semibold">90 days ago</span>
                            <div class="h-px bg-green-500 w-full"></div>
                            <span class="px-2 flex-shrink-0 text-green-500 text-xs font-semibold"><?= round(calculateUptimePercentage($service['id'], $pdo), 2) ?>%</span>
                            <div class="h-px bg-green-500 w-full"></div>
                            <span class="pl-2 flex-shrink-0 text-green-500 text-xs font-semibold">Today</span>
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
                    for ($i = 0; $i < 91; $i++) {
                        $currentDate = $date->format('Y-m-d');
                        $incidents = getIncidentsForDay($currentDate, $pdo);
                        echo '<div class="day mt-12">';
                        echo '<hr style="padding-bottom:15px;"><h4 class="inline-block bg-gray-100 dark:bg-gray-800 rounded-md px-2 font-medium pb-1 mb-2 text-gray-900 dark:text-gray-300">' . $date->format('M j, Y') . '</h4>';
                        if (empty($incidents)) {
                            echo '<div class="incidents text-gray-500 dark:text-gray-400">No incidents reported.</div>';
                        } else {
                            foreach ($incidents as $incident) {
                                echo '<div class="incident-document">';
                                echo '<div class="incident-header">';
                                echo '<div class="text-blue-700 dark:text-blue-400 text-xl font-semibold">' . htmlspecialchars($incident['title']) . '</div>';
                                echo '<div class="incident-status">';
                                echo getStatusIcon($incident['status']) . ' <span>' . $incident['status'] . '</span>';
                                echo '</div>';
                                echo '</div>';
                                echo '<div class="incident-body">';
                                echo '<p><strong>Created at: </strong>' . $incident['created_at'] . '</p><br>';
                                echo '<p>' . nl2br(htmlspecialchars($incident['description'])) . '</p><br>';
                                $steps = getIncidentSteps($incident['id'], $pdo);
                                foreach ($steps as $step) {
                                    echo '<div class="incident-step">';
                                    echo getStatusIcon($step['step']) . ' <strong>' . htmlspecialchars($step['step']) . ':</strong> ' . htmlspecialchars($step['description']);
                                    echo '<br><div class="text-gray-500">' . $step['created_at'] . '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                        echo '</div>';
                        $date->modify('-1 day');
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
                    Powered by <span class=><a href="https://ticketpulsesoftware.com/Stari-Status/">TicketPulse Software | Stari-Status</a></span>
                </div>
            </div>
        </footer>

        <!-- Report Modal -->
        <div x-show="showModal" x-transition:enter="modal-enter modal-enter-active" x-transition:leave="modal-leave modal-leave-active" class="modal-container" style="display: none;">
            <div class="relative mx-auto p-5 border shadow-lg rounded-md bg-white modal-content">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Report an Issue</h3>
                    <form method="post" action="report.php" class="mt-4">
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                            <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                            <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="button" @click="showModal = false" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none">Cancel</button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>