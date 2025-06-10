<?php
// export_applications.php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=uk_applications_export_'.date('Y-m-d').'.csv');

$host = 'localhost';
$db   = 'careerbridge';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Message', 'Submitted At']);

if (!$conn->connect_error) {
    $result = $conn->query('SELECT * FROM uk_applications ORDER BY submitted_at DESC');
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['message'],
                $row['submitted_at']
            ]);
        }
    }
    $conn->close();
}
fclose($output);
exit;
