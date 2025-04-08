<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'formula1');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle delete
if (isset($_GET['delete'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];
    
    $idColumns = [
        'tim' => 'IDtim',
        'vozac' => 'IDvozac',
        'utrka' => 'IDutrka',
        'statistika' => 'IDstatistika'
    ];

    if (!array_key_exists($table, $idColumns)) die("Invalid table");

    try {
        $conn->begin_transaction();
        
        if ($table === 'tim') {
            $stmt = $conn->prepare("UPDATE vozac SET timID = NULL WHERE timID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        
        $stmt = $conn->prepare("DELETE FROM $table WHERE {$idColumns[$table]} = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        header("Location: index.php?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

// Fetch data
$teams = $conn->query("SELECT * FROM tim")->fetch_all(MYSQLI_ASSOC);
$drivers = $conn->query("SELECT v.*, t.naziv as team_name FROM vozac v LEFT JOIN tim t ON v.timID = t.IDtim")->fetch_all(MYSQLI_ASSOC);
$races = $conn->query("SELECT * FROM utrka")->fetch_all(MYSQLI_ASSOC);
$stats = $conn->query("SELECT s.*, v.ime, v.prezime, u.naziv as race_name FROM statistika s JOIN vozac v ON s.IDvozac = v.IDvozac JOIN utrka u ON s.IDutrka = u.IDutrka")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 20px; }
        .table-container { overflow-x: auto; }
        .nav-tabs { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Formula 1 Management System</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operation completed successfully!</div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams" type="button" role="tab">Teams</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="drivers-tab" data-bs-toggle="tab" data-bs-target="#drivers" type="button" role="tab">Drivers</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="races-tab" data-bs-toggle="tab" data-bs-target="#races" type="button" role="tab">Races</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab">Statistics</button>
            </li>
        </ul>
        
        <div class="tab-content" id="myTabContent">
            <!-- Teams Tab -->
            <div class="tab-pane fade show active" id="teams" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Teams</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">Add Team</button>
                    </div>
                    <div class="card-body table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams as $team): ?>
                                <tr>
                                    <td><?= $team['IDtim'] ?></td>
                                    <td><?= $team['naziv'] ?></td>
                                    <td><?= $team['br_bodova'] ?></td>
                                    <td>
                                        <a href="index.php?delete=1&table=tim&id=<?= $team['IDtim'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                        <a href="edit_team.php?id=<?= $team['IDtim'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Drivers Tab -->
            <div class="tab-pane fade" id="drivers" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Drivers</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDriverModal">Add Driver</button>
                    </div>
                    <div class="card-body table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Points</th>
                                    <th>Races</th>
                                    <th>Team</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drivers as $driver): ?>
                                <tr>
                                    <td><?= $driver['IDvozac'] ?></td>
                                    <td><?= $driver['ime'] ?></td>
                                    <td><?= $driver['prezime'] ?></td>
                                    <td><?= $driver['br_bodova'] ?></td>
                                    <td><?= $driver['br_utrka'] ?></td>
                                    <td><?= $driver['team_name'] ?? 'No Team' ?></td>
                                    <td>
                                        <a href="index.php?delete=1&table=vozac&id=<?= $driver['IDvozac'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                        <a href="edit_vozac.php?id=<?= $driver['IDvozac'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Races Tab -->
            <div class="tab-pane fade" id="races" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Races</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRaceModal">Add Race</button>
                    </div>
                    <div class="card-body table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Track Length</th>
                                    <th>Laps</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($races as $race): ?>
                                <tr>
                                    <td><?= $race['IDutrka'] ?></td>
                                    <td><?= $race['naziv'] ?></td>
                                    <td><?= $race['lokacija'] ?></td>
                                    <td><?= $race['duzina_staze'] ?> km</td>
                                    <td><?= $race['br_krugova'] ?></td>
                                    <td>
                                        <a href="index.php?delete=1&table=utrka&id=<?= $race['IDutrka'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                        <a href="edit_utrka.php?id=<?= $race['IDutrka'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Tab -->
            <div class="tab-pane fade" id="stats" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Race Statistics</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStatModal">Add Statistic</button>
                    </div>
                    <div class="card-body table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Driver</th>
                                    <th>Race</th>
                                    <th>Start Pos</th>
                                    <th>Points</th>
                                    <th>Avg Speed</th>
                                    <th>Fastest Lap</th>
                                    <th>Total Time</th>
                                    <th>Finish Pos</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats as $stat): ?>
                                <tr>
                                    <td><?= $stat['IDstatistika'] ?></td>
                                    <td><?= $stat['ime'] ?> <?= $stat['prezime'] ?></td>
                                    <td><?= $stat['race_name'] ?></td>
                                    <td><?= $stat['start_pozicija'] ?></td>
                                    <td><?= $stat['br_bodova'] ?></td>
                                    <td><?= $stat['prosjecna_brzina'] ?> km/h</td>
                                    <td><?= $stat['najbrzi_krug'] ?>s</td>
                                    <td><?= $stat['ukupno_vrijeme'] ?>s</td>
                                    <td><?= $stat['pozicija'] ?></td>
                                    <td>
                                        <a href="index.php?delete=1&table=statistika&id=<?= $stat['IDstatistika'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                        <a href="edit_stat.php?id=<?= $stat['IDstatistika'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Team Modal -->
    <div class="modal fade" id="addTeamModal" tabindex="-1" aria-labelledby="addTeamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_team.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTeamModalLabel">Add New Team</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="teamName" class="form-label">Team Name</label>
                            <input type="text" class="form-control" id="teamName" name="naziv" required>
                        </div>
                        <div class="mb-3">
                            <label for="teamPoints" class="form-label">Points</label>
                            <input type="number" class="form-control" id="teamPoints" name="br_bodova" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_vozac.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDriverModalLabel">Add New Driver</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="driverFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="driverFirstName" name="ime" required>
                        </div>
                        <div class="mb-3">
                            <label for="driverLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="driverLastName" name="prezime" required>
                        </div>
                        <div class="mb-3">
                            <label for="driverPoints" class="form-label">Points</label>
                            <input type="number" class="form-control" id="driverPoints" name="br_bodova" required>
                        </div>
                        <div class="mb-3">
                            <label for="driverRaces" class="form-label">Races</label>
                            <input type="number" class="form-control" id="driverRaces" name="br_utrka" required>
                        </div>
                        <div class="mb-3">
                            <label for="driverTeam" class="form-label">Team</label>
                            <select class="form-select" id="driverTeam" name="timID">
                                <option value="">No Team</option>
                                <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['IDtim'] ?>"><?= $team['naziv'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Race Modal -->
    <div class="modal fade" id="addRaceModal" tabindex="-1" aria-labelledby="addRaceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_utrka.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRaceModalLabel">Add New Race</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="raceName" class="form-label">Race Name</label>
                            <input type="text" class="form-control" id="raceName" name="naziv" required>
                        </div>
                        <div class="mb-3">
                            <label for="raceLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="raceLocation" name="lokacija" required>
                        </div>
                        <div class="mb-3">
                            <label for="raceLength" class="form-label">Track Length (km)</label>
                            <input type="number" step="0.01" class="form-control" id="raceLength" name="duzina_staze" required>
                        </div>
                        <div class="mb-3">
                            <label for="raceLaps" class="form-label">Number of Laps</label>
                            <input type="number" class="form-control" id="raceLaps" name="br_krugova" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Race</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Statistic Modal -->
    <div class="modal fade" id="addStatModal" tabindex="-1" aria-labelledby="addStatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="add_stat.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStatModalLabel">Add New Statistic</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="statDriver" class="form-label">Driver</label>
                            <select class="form-select" id="statDriver" name="IDvozac" required>
                                <?php foreach ($drivers as $driver): ?>
                                <option value="<?= $driver['IDvozac'] ?>"><?= $driver['ime'] ?> <?= $driver['prezime'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statRace" class="form-label">Race</label>
                            <select class="form-select" id="statRace" name="IDutrka" required>
                                <?php foreach ($races as $race): ?>
                                <option value="<?= $race['IDutrka'] ?>"><?= $race['naziv'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statStartPos" class="form-label">Starting Position</label>
                            <input type="number" class="form-control" id="statStartPos" name="start_pozicija" required>
                        </div>
                        <div class="mb-3">
                            <label for="statPoints" class="form-label">Points Earned</label>
                            <input type="number" class="form-control" id="statPoints" name="br_bodova" required>
                        </div>
                        <div class="mb-3">
                            <label for="statAvgSpeed" class="form-label">Average Speed (km/h)</label>
                            <input type="number" class="form-control" id="statAvgSpeed" name="prosjecna_brzina" required>
                        </div>
                        <div class="mb-3">
                            <label for="statFastestLap" class="form-label">Fastest Lap (seconds)</label>
                            <input type="number" step="0.01" class="form-control" id="statFastestLap" name="najbrzi_krug" required>
                        </div>
                        <div class="mb-3">
                            <label for="statTotalTime" class="form-label">Total Time (seconds)</label>
                            <input type="number" step="0.01" class="form-control" id="statTotalTime" name="ukupno_vrijeme" required>
                        </div>
                        <div class="mb-3">
                            <label for="statFinishPos" class="form-label">Finishing Position</label>
                            <input type="number" class="form-control" id="statFinishPos" name="pozicija" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Statistic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>