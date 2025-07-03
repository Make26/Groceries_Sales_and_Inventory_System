<?php
include 'db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['date'])) {
    $date = $_GET['date'];

    try {
        $query = $conn->prepare("
            SELECT 
                p.name AS product_name, 
                p.price AS price, 
                SUM(s.quantity) AS total_sold 
            FROM sales_items s 
            INNER JOIN products p ON s.product_id = p.id 
            INNER JOIN sales_list sl ON s.sale_id = sl.id 
            WHERE DATE(sl.date_updated) = ? 
            GROUP BY s.product_id
        ");

        $query->bind_param("s", $date);
        $query->execute();
        $result = $query->get_result();

        $salesSummary = [];
        while ($row = $result->fetch_assoc()) {
            $salesSummary[] = [
                'product_name' => $row['product_name'],
                'price' => (float)$row['price'],
                'total_sold' => (int)$row['total_sold']
            ];
        }

        echo json_encode($salesSummary);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No date parameter provided.']);
}
?>
