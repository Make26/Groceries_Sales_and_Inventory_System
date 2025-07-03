<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? null;

    if (!$date) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT p.name AS product_name, i.qty AS qty_out, DATE(i.date_created) AS date 
        FROM inventory i 
        JOIN product_list p ON i.product_id = p.id 
        WHERE i.type = 2 AND DATE(i.date_created) = ?
    ");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    $stmt->close();
    exit;
}
?>
