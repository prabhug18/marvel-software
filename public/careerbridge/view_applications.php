<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Submitted Applications - Career Bridge</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  <style>
    body { background: #f8f9fa; }
    .container { margin-top: 40px; }
    h2 { color: #c41230; margin-bottom: 30px; }
    table.dataTable thead th { background: #c41230; color: #fff; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Submitted UK University Applications</h2>
    <div class="d-flex justify-content-end mb-2">
      <a href="export_applications.php" class="btn btn-success">Export to CSV</a>
    </div>
    <div class="table-responsive">
      <table id="applications-table" class="display table table-bordered table-striped" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Submitted At</th>
          </tr>
        </thead>
        <tbody>
        <?php
        // Database config
        $host = 'localhost';
        $db   = 'careerbridge';
        $user = 'root';
        $pass = '';
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
          echo '<tr><td colspan="6">Database connection failed.</td></tr>';
        } else {
          $result = $conn->query('SELECT * FROM uk_applications ORDER BY submitted_at DESC');
          if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['id']) . '</td>';
              echo '<td>' . htmlspecialchars($row['name']) . '</td>';
              echo '<td>' . htmlspecialchars($row['email']) . '</td>';
              echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
              echo '<td>' . nl2br(htmlspecialchars($row['message'])) . '</td>';
              echo '<td>' . htmlspecialchars($row['submitted_at']) . '</td>';
              echo '</tr>';
            }
          } else {
            echo '<tr><td colspan="6">No applications found.</td></tr>';
          }
          $conn->close();
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#applications-table').DataTable();
    });
  </script>
</body>
</html>
