<?php 
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Fetch sales data
    $sales = $conn->query("SELECT * FROM sales_list WHERE id = $id");
    if ($sales && $sales->num_rows > 0) {
        $sales_data = $sales->fetch_array();
        $customer_id = $sales_data['customer_id'];
        $ref_no = $sales_data['ref_no'];
        $date_updated = $sales_data['date_updated'];
        $total_amount = $sales_data['total_amount'];

        // Fetch customer name
        $cname = "Guest";
        if ($customer_id > 0) {
            $customer_query = $conn->query("SELECT name FROM customer_list WHERE id = $customer_id");
            if ($customer_query && $customer_query->num_rows > 0) {
                $cname = $customer_query->fetch_array()['name'];
            }
        }

        // Fetch inventory with product details
        $inventory = $conn->query("
            SELECT 
                i.qty,
                p.name AS product_name,
                p.price 
            FROM 
                inventory i
            JOIN 
                product_list p 
            ON 
                i.product_id = p.id
            WHERE 
                i.form_id = $id AND i.type = 2
        ");

        // Receipt content
        echo "<p><b>Date:</b> " . date("M d, Y", strtotime($date_updated)) . "</p>";
        echo "<p><b>Reference #:</b> $ref_no</p>";
        echo "<p><b>Customer:</b> $cname</p>";
        echo "<hr>";
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Qty</th><th>Product</th><th>Unit Price</th><th>Total</th></tr></thead>";
        echo "<tbody>";
        while ($item = $inventory->fetch_assoc()) {
            echo "<tr>
                    <td>{$item['qty']}</td>
                    <td>{$item['product_name']}</td>
                    <td>" . number_format($item['price'], 2) . "</td>
                    <td>" . number_format($item['qty'] * $item['price'], 2) . "</td>
                  </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "<hr>";
        echo "<p><b>Total Amount:</b> " . number_format($total_amount, 2) . "</p>";
    } else {
        echo "<p class='text-danger'>Invalid Sales ID.</p>";
    }
} else {
    echo "<p class='text-danger'>No Sales ID provided.</p>";
}
?>
