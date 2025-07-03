<?php 
include 'db_connect.php';

// Start session to access session variables
session_start();

if (isset($_GET['id'])) {
    // Use prepared statements to avoid SQL injection
    $qry = $conn->prepare("SELECT * FROM sales_list WHERE id = ?");
    $qry->bind_param("i", $_GET['id']);
    $qry->execute();
    $result = $qry->get_result();
    $sale = $result->fetch_assoc();

    if (!$sale) {
        echo "Sale record not found.";
        exit;
    }

    foreach ($sale as $k => $val) {
        $$k = $val;
    }

    // Fetch inventory details
    $inv_query = $conn->prepare("SELECT * FROM inventory WHERE type = 2 AND form_id = ?");
    $inv_query->bind_param("i", $_GET['id']);
    $inv_query->execute();
    $inv = $inv_query->get_result();

    // Fetch customer details
    if ($customer_id > 0) {
        $cname_query = $conn->prepare("SELECT name FROM customer_list WHERE id = ?");
        $cname_query->bind_param("i", $customer_id);
        $cname_query->execute();
        $cname_result = $cname_query->get_result();
        $cname = $cname_result->num_rows > 0 ? $cname_result->fetch_assoc()['name'] : "Guest";
    } else {
        $cname = "Guest";
    }

    // Fetch the name of the logged-in user
    if (isset($_SESSION['login_id'])) {  // Fix the condition here
        $user_id = $_SESSION['login_id'];
        $user_query = $conn->prepare("SELECT name FROM users WHERE id = ?");
        if (!$user_query) {
            die("Query preparation failed: " . $conn->error);
        }
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        $user = $user_result->fetch_assoc();

        $logged_in_user = $user['name'] ?? "Unknown User"; // Fallback in case name is not found
    } else {
        $logged_in_user = "Unknown User";  // Default value if not logged in
    }

    // Fetch product details
    $product_query = $conn->query("SELECT * FROM product_list ORDER BY name ASC");
    while ($row = $product_query->fetch_assoc()) {
        $prod[$row['id']] = $row;
    }
}
?>

<div class="container-fluid" id="print-sales">
    <style>
        table {
            border-collapse: collapse;
        }
        .wborder {
            border: 1px solid gray;
        }
        .bbottom {
            border-bottom: 1px solid black;
        }
        td p, th p {
            margin: unset;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .clear {
            padding: 10px;
        }
        #uni_modal .modal-footer {
            display: none;
        }
    </style>
    <table width="100%">
        <tr>
            <th class="text-center">
                <p><b>Receipt</b></p>
            </th>
        </tr>
        <tr>
            <td class="clear">&nbsp;</td>
        </tr>
        <tr>
            <td>
                <table width="100%">
                    <tr>
                        <td width="20%" class="text-right">Customer :</td>
                        <td width="40%" class="bbottom"><?php echo ucwords($cname); ?></td>
                        <td width="20%" class="text-right">Date :</td>
                        <td width="20%" class="bbottom"><?php echo date("Y-m-d", strtotime($date_updated)); ?></td>
                    </tr>
                    <tr>
                        <td width="20%" class="text-right">Reference Number :</td>
                        <td width="80%" class="bbottom" colspan="3"><?php echo htmlspecialchars($ref_no); ?></td>
                    </tr>
                    <tr>
                        <td width="20%" class="text-right">Processed By :</td>
                        <td width="80%" class="bbottom" colspan="3"><?php echo htmlspecialchars($logged_in_user); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="clear">&nbsp;</td>
        </tr>
        <tr>
            <table width="100%">
                <tr>
                    <th width="20%" class="wborder">Qty</th>
                    <th width="30%" class="wborder">Product</th>
                    <th width="25%" class="wborder">Unit Price</th>
                    <th width="25%" class="wborder">Amount</th>
                </tr>
                <?php while ($row = $inv->fetch_assoc()): ?>
                    <?php
                        foreach (json_decode($row['other_details'], true) as $k => $v) {
                            $row[$k] = $v;
                        }

                        $product_name = $prod[$row['product_id']]['name'] ?? "Unknown Product";
                        $product_description = $prod[$row['product_id']]['description'] ?? "No Description";
                        $price = is_numeric($row['price']) ? (float) $row['price'] : 0.00;
                        $qty = is_numeric($row['qty']) ? (float) $row['qty'] : 0.00;
                        $amount = $price * $qty;
                    ?>
                    <tr>
                        <td class="wborder text-center"><?php echo $row['qty']; ?></td>
                        <td class="wborder">
                            <?php if ($product_name != "Unknown Product" && $product_description != "No Description"): ?>
                                <p class="pname">Name: <b><?php echo $product_name; ?></b></p>
                                <p class="pdesc"><small><i>Description: <b><?php echo $product_description; ?></b></i></small></p>
                            <?php else: ?>
                                <p class="pname">Name: <b><?php echo $product_name; ?></b></p>
                            <?php endif; ?>
                        </td>
                        <td class="wborder text-right"><?php echo number_format($price, 2); ?></td>
                        <td class="wborder text-right"><?php echo number_format($amount, 2); ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <th class="text-right wborder" colspan="3">Total</th>
                    <th class="text-right wborder"><?php echo number_format($total_amount, 2); ?></th>
                </tr>
                <tr>
                    <th class="text-right wborder" colspan="3">Amount Tendered</th>
                    <th class="text-right wborder"><?php echo number_format($amount_tendered, 2); ?></th>
                </tr>
                <tr>
                    <th class="text-right wborder" colspan="3">Change</th>
                    <th class="text-right wborder"><?php echo number_format($amount_change, 2); ?></th>
                </tr>
            </table>
        </tr>
        <tr>
            <td class="clear">&nbsp;</td>
        </tr>
        <tr>
            <th>
                <p class="text-center"><i>This is an official receipt.</i></p>
            </th>
        </tr>
        <!-- Added logged-in user info -->
        <tr>
            <td class="clear">&nbsp;</td>
        </tr>
    </table>
</div>
<hr>
<div class="text-right">
    <button type="button" class="btn btn-sm btn-primary" id="print"><i class="fa fa-print"></i> Print</button>
    <button type="button" class="btn btn-sm btn-secondary" id="new-sales-btn" onclick="location.reload()">
        <i class="fa fa-plus"></i> New Sales
    </button>
</div>

<script>
    $('#print').click(function() {
        var _html = $('#print-sales').clone();
        var newWindow = window.open("", "_blank", "menubar=no,scrollbars=yes,resizable=yes,width=700,height=600");
        newWindow.document.write(_html.html());
        newWindow.document.close();
        newWindow.focus();
        newWindow.print();
        setTimeout(function() {
            newWindow.close();
            // After printing, redirect to the New Sales page
            window.location.href = "index.php?page=home";  // Adjust this URL to match your "New Sales" page
        }, 1500);
    });

    $(document).keydown(function(e) {
        switch (e.key) {
            case "Delete":
                e.preventDefault();
                $('#new-sales-btn').click();
                break;
            case "Enter":  // Add this case for Enter key
                e.preventDefault();  // Prevent the default action for Enter
                $('#print').click();  // Trigger the print function
                break;
        }
    });
</script>
