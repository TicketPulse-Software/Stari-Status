<?php
include 'db.php';

$result = $conn->query("SELECT s.id, s.name, ss.status FROM service_status ss JOIN services s ON ss.service_id = s.id WHERE ss.checked_at = (SELECT MAX(checked_at) FROM service_status WHERE service_id = ss.service_id)");

while ($row = $result->fetch_assoc()) {
    $color = $row['status'] === 'Operational' ? 'green' : 'red';
    echo '<div class="monitor py-8 bg-' . $color . '-400 bg-opacity-10">';
    echo '<div class="container flex items-center justify-between mb-3">';
    echo '<h3 class="text-2xl text-gray-800 dark:text-gray-100">' . $row['name'] . '</h3>';
    echo '<span class="text-' . $color . '-600 dark:text-' . $color . '-400 font-semibold">' . $row['status'] . '</span>';
    echo '<form action="report.php" method="post">';
    echo '<input type="hidden" name="service_id" value="' . $row['id'] . '">';
    echo '<button type="submit" class="bg-blue-500 text-white rounded px-2 py-1">Report</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}

$conn->close();
?>
