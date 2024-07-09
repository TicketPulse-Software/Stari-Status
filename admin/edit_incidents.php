<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

$id = $_GET['id'];
$incident = $pdo->query('SELECT * FROM incidents WHERE id = ' . $id)->fetch(PDO::FETCH_ASSOC);
$services = $pdo->query('SELECT * FROM services')->fetchAll(PDO::FETCH_ASSOC);
$steps = $pdo->query('SELECT * FROM incident_steps WHERE incident_id = ' . $id)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare('UPDATE incidents SET service_id = ?, title = ?, description = ?, status = ? WHERE id = ?');
    $stmt->execute([$service_id, $title, $description, $status, $id]);

    // Handle steps
    foreach ($_POST['steps'] as $step) {
        if (isset($step['id']) && !empty($step['id'])) {
            $stmt = $pdo->prepare('UPDATE incident_steps SET step = ?, description = ? WHERE id = ?');
            $stmt->execute([$step['step'], $step['description'], $step['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO incident_steps (incident_id, step, description) VALUES (?, ?, ?)');
            $stmt->execute([$id, $step['step'], $step['description']]);
        }
    }

    header('Location: incidents.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Incident - Admin Panel</title>
    <link href="../css/styles.css" rel="stylesheet">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Edit Incident</h1>
    <form method="post">
        <div class="form-group">
            <label for="service_id">Service:</label>
            <select id="service_id" name="service_id" class="form-control" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>" <?= $incident['service_id'] == $service['id'] ? 'selected' : '' ?>><?= htmlspecialchars($service['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($incident['title']) ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($incident['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" class="form-control" required>
                <option value="Investigating" <?= $incident['status'] == 'Investigating' ? 'selected' : '' ?>><i class='bx bx-search-alt-2'></i> Investigating</option>
                <option value="Identified" <?= $incident['status'] == 'Identified' ? 'selected' : '' ?>><i class='bx bxs-star-half' ></i> Identified</option>
                <option value="Monitoring" <?= $incident['status'] == 'Monitoring' ? 'selected' : '' ?>><i class='bx bxs-component' ></i> Monitoring</option>
                <option value="Resolved" <?= $incident['status'] == 'Resolved' ? 'selected' : '' ?>><i class='bx bxs-check-circle' ></i> Resolved</option>
            </select>
        </div>

        <h3 class="mt-4">Incident Steps</h3>
        <div id="steps">
            <?php foreach ($steps as $step): ?>
                <div class="step mb-3">
                    <input type="hidden" name="steps[<?= $step['id'] ?>][id]" value="<?= $step['id'] ?>">
                    <div class="form-group">
                        <label for="step">Step:</label>
                        <select name="steps[<?= $step['id'] ?>][step]" class="form-control" required>
                            <option value="Investigating" <?= $step['step'] == 'Investigating' ? 'selected' : '' ?>><i class='bx bx-search-alt-2'></i> Investigating</option>
                            <option value="Identified" <?= $step['step'] == 'Identified' ? 'selected' : '' ?>><i class='bx bxs-star-half' ></i> Identified</option>
                            <option value="Monitoring" <?= $step['step'] == 'Monitoring' ? 'selected' : '' ?>><i class='bx bxs-component' ></i> Monitoring</option>
                            <option value="Resolved" <?= $step['step'] == 'Resolved' ? 'selected' : '' ?>><i class='bx bxs-check-circle' ></i> Resolved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="steps[<?= $step['id'] ?>][description]" class="form-control" rows="3" required><?= htmlspecialchars($step['description']) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-secondary mb-3" onclick="addStep()">Add Step</button>
        <button type="submit" class="btn btn-primary">Update Incident</button>
    </form>
</div>
<script>
function addStep() {
    const stepsContainer = document.getElementById('steps');
    const stepIndex = stepsContainer.children.length;
    const stepHtml = `
        <div class="step mb-3">
            <div class="form-group">
                <label for="step">Step:</label>
                <select name="steps[${stepIndex}][step]" class="form-control" required>
                    <option value="Investigating"><i class='bx bx-search-alt-2'></i> Investigating</option>
                    <option value="Identified"><i class='bx bxs-star-half' ></i> Identified</option>
                    <option value="Monitoring"><i class='bx bxs-component' ></i> Monitoring</option>
                    <option value="Resolved"><i class='bx bxs-check-circle' ></i> Resolved</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="steps[${stepIndex}][description]" class="form-control" rows="3" required></textarea>
            </div>
        </div>`;
    stepsContainer.insertAdjacentHTML('beforeend', stepHtml);
}
</script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>