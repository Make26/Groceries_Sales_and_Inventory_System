<?php
include 'db_connect.php';

if (isset($_POST['sales_id']) && isset($_POST['receipt_content'])) {
    $sales_id = $_POST['sales_id'];
    $receipt_content = $_POST['receipt_content'];

    $stmt = $conn->prepare("INSERT INTO receipts (sales_id, receipt_content) VALUES (?, ?)");
    $stmt->bind_param("is", $sales_id, $receipt_content);

    if ($stmt->execute()) {
        echo "Receipt saved successfully.";
    } else {
        echo "Error saving receipt: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid data received.";
}
?>
