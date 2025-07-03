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
</head>
<body>
    <div class="container mt-5">
        <h3>Sales Records</h3>

        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by date, reference #, or customer name">
            </div>
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
        });
    </script>
</body>
</html>
