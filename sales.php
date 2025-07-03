<?php 
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Sales Records</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }

        .navbar {
            background: linear-gradient(90deg, #4CAF50, #2196F3);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .btn {
            border-radius: 30px;
        }

        /* Table Styling */
        table {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background-color: #2196F3;
            color: white;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Button Styling */
        .btn-success {
            background-color: #4CAF50;
            border: none;
        }

        .btn-success:hover {
            background-color: #45A049;
        }

        .btn-info {
            background-color: #2196F3;
            border: none;
        }

        .btn-info:hover {
            background-color: #1E88E5;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #2196F3;
            color: white;
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
        }

        /* Dark Mode */
        .dark-mode {
            background-color: #343a40 !important;
            color: white !important;
        }

        .dark-mode .navbar {
            background: linear-gradient(90deg, #333, #555);
        }

        .dark-mode .table thead {
            background-color: #555 !important;
        }

        .dark-mode .table tbody tr:nth-child(odd) {
            background-color: #444 !important;
        }
    </style>
</head>
z<body>
    <div class="container mt-5">
        <h3>Sales Records</h3>

        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by date, reference #, or customer name">
            </div>
        </div>

        <!-- Print Day Sales Button -->
        <div class="row mb-3">
            <div class="col-md-6">
                
           
        </div>

        <!-- Sales Table -->
        <table class="table table-bordered" id="salesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Reference #</th>
                    <th>Customer</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $customer_list = [];
                $customers = $conn->query("SELECT * FROM customer_list");
                while ($row = $customers->fetch_assoc()) {
                    $customer_list[$row['id']] = $row['name'];
                }
                $customer_list[0] = "Guest";

                // Get sales for the day (grouped by date)
                $sales = $conn->query("SELECT * FROM sales_list ORDER BY DATE(date_updated) DESC");
                while ($row = $sales->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo date("M d, Y", strtotime($row['date_updated'])); ?></td>
                    <td><?php echo $row['ref_no']; ?></td>
                    <td><?php echo isset($customer_list[$row['customer_id']]) ? $customer_list[$row['customer_id']] : 'N/A'; ?></td>
                    <td>
                        <button 
                            class="btn btn-sm btn-info view-receipt" 
                            data-id="<?php echo $row['id']; ?>" 
                            data-bs-toggle="modal" 
                            data-bs-target="#receiptModal">
                            View Receipt
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Structure -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="receiptContent">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Search functionality
            $('#searchInput').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('#salesTable tbody tr').filter(function () {
                    $(this).toggle(
                        $(this).text().toLowerCase().indexOf(value) > -1
                    );
                });
            });

            // View receipt functionality
            $('.view-receipt').click(function () {
                var salesId = $(this).data('id');
                $('#receiptContent').html('Loading...'); // Show loading indicator
                $.ajax({
                    url: 'fetch_receipt.php',
                    method: 'GET',
                    data: { id: salesId },
                    success: function (response) {
                        $('#receiptContent').html(response); // Load receipt content
                    },
                    error: function () {
                        $('#receiptContent').html('<p class="text-danger">An error occurred while loading the receipt.</p>');
                    }
                });
            });

            // Print Day Sales functionality
            $('#printDaySales').click(function () {
                let salesSummary = '';
                let totalSalesAmount = 0;
                let salePromises = [];  // To hold the promises

                // Loop through the table and collect the sales for that day
                $('#salesTable tbody tr').each(function () {
                    const saleDate = $(this).find('td').eq(1).text().trim();
                    const refNo = $(this).find('td').eq(2).text().trim();

                    // Fetch items sold for the specific sale (by ref_no)
                    const saleId = $(this).find('.view-receipt').data('id');
                    
                    // Create a promise for each AJAX request
                    let salePromise = $.ajax({
                        url: 'fetch_sales_items.php',
                        method: 'GET',
                        data: { sale_id: saleId },
                        success: function (items) {
                            items.forEach(function(item) {
                                let itemName = item.name;
                                let quantitySold = item.quantity;
                                let price = item.price;
                                let totalItemSales = price * quantitySold;

                                salesSummary += ` 
                                    <tr>
                                        <td>${itemName}</td>
                                        <td>${quantitySold}</td>
                                        <td>${price.toFixed(2)}</td>
                                        <td>${totalItemSales.toFixed(2)}</td>
                                    </tr>`;
                                totalSalesAmount += totalItemSales;
                            });
                        }
                    });

                    salePromises.push(salePromise);  // Add each promise to the array
                });

                // Wait for all AJAX calls to finish before generating the report
                Promise.all(salePromises).then(function () {
                    // Generate printable summary
                    const printWindow = window.open('', '_blank', 'width=800,height=600');
                    let printContent = `
                        <html>
                        <head>
                            <title>Day Sales Summary</title>
                            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
                            <style>
                                table { width: 100%; border-collapse: collapse; }
                                th, td { padding: 10px; border: 1px solid black; text-align: center; }
                                h4 { text-align: center; }
                            </style>
                        </head>
                        <body>
                            <h4>Day's Sales Summary</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Quantity Sold</th>
                                        <th>Price</th>
                                        <th>Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${salesSummary}
                                </tbody>
                            </table>

                            <h4>Total Sales Summary</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${totalSalesAmount.toFixed(2)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </body>
                        </html>`;
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.print();
                });
            });
        });
    </script>
</body>
</html>
