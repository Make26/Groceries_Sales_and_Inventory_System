    <?php include 'db_connect.php'; ?>
    <div class="container-fluid">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><b>Today's sales</b></h4>
                            <!-- Button to print the day's summary -->
                            <button id="print_daily_summary" class="btn btn-success btn-sm">
                                <i class="fa fa-print"></i> Print Day's Item Sold Summary
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered" id="inventory_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Product Name</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Items Sold</th>
                                        <th class="text-center">Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    $total_sales = 0;
                                    $total_sold_items = 0;

                                    // Query to fetch items sold today
                                    $stmt = $conn->prepare("
                                        SELECT p.id, p.name, p.price, 
                                               (SELECT SUM(qty) 
                                                FROM inventory 
                                                WHERE type = 2 
                                                  AND product_id = p.id 
                                                  AND DATE(date_updated) = CURDATE()) AS sold_qty
                                        FROM product_list p
                                        WHERE (SELECT SUM(qty) 
                                               FROM inventory 
                                               WHERE type = 2 
                                                 AND product_id = p.id 
                                                 AND DATE(date_updated) = CURDATE()) > 0
                                        ORDER BY p.name ASC
                                    ");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $sales_data = []; // To store data for printing receipt

                                    while ($row = $result->fetch_assoc()):
                                        $out = $row['sold_qty'] ?? 0;
                                        $price = $row['price'] ?? 0;
                                        $sales = $out * $price;
                                        $total_sales += $sales;
                                        $total_sold_items += $out;

                                        // Prepare data for printing receipt
                                        $sales_data[] = [
                                            'name' => htmlspecialchars($row['name']),
                                            'price' => $price,
                                            'qty' => $out,
                                            'sales' => $sales
                                        ];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td class="text-right"><?php echo number_format($price, 2); ?></td>
                                        <td class="text-right"><?php echo $out; ?></td>
                                        <td class="text-right"><?php echo number_format($sales, 2); ?></td>
                                    </tr>
                                    <?php endwhile; 
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                            <h5 class="text-right">Total Items Sold: <?php echo $total_sold_items; ?></h5>
                            <h5 class="text-right">Total Sales: <?php echo number_format($total_sales, 2); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><b>Available Product</b></h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Product Name</th>
                                    <th class="text-center">Stocks</th>
                                    <th class="text-center">Sold</th>
                                    <th class="text-center">Remaining</th>
                                </thead>
                                <tbody>
                                <?php 
                                    $i = 1;
                                    $product = $conn->query("SELECT * FROM product_list r order by name asc");
                                    while($row=$product->fetch_assoc()):
                                    $inn = $conn->query("SELECT sum(qty) as inn FROM inventory where type = 1 and product_id = ".$row['id']);
                                    $inn = $inn && $inn->num_rows > 0 ? $inn->fetch_array()['inn'] : 0;
                                    $out = $conn->query("SELECT sum(qty) as `out` FROM inventory where type = 2 and product_id = ".$row['id']);
                                    $out = $out && $out->num_rows > 0 ? $out->fetch_array()['out'] : 0;
                                    $available = $inn - $out;
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td class=""><?php echo $row['name'] ?></td>
                                        <td class="text-right"><?php echo $inn ?></td>
                                        <td class="text-right"><?php echo $out ?></td>
                                        <td class="text-right"><?php echo $available ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <!-- Include necessary libraries -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <script>
        $(document).ready(function() {
            $('table').dataTable()
        $('#new_receiving').click(function(){
            location.href = "index.php?page=manage_receiving"
        })
        $('.delete_receiving').click(function(){
            _conf("Are you sure to delete this data?","delete_receiving",[$(this).attr('data-id')])
        })
        function delete_receiving($id){
            start_load()
            $.ajax({
                url:'ajax.php?action=delete_receiving',
                method:'POST',
                data:{id:$id},
                success:function(resp){
                    if(resp==1){
                        alert_toast("Data successfully deleted",'success')
                        setTimeout(function(){
                            location.reload()
                        },1500)

                    }
                }
            })
        }
            // Initialize DataTable
            $('#inventory_table').DataTable();

            // Print summary for the day
            $('#print_daily_summary').click(function() {
                const salesData = <?php echo json_encode($sales_data); ?>;
                const totalItemsSold = <?php echo $total_sold_items; ?>;
                const totalSales = <?php echo $total_sales; ?>;

                const printWindow = window.open('', '_blank', 'width=800,height=600');
                let printContent = `
                    <html>
                    <head>
                        <title>Today's Sales Summary</title>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
                        <style>
                            table { width: 100%; border-collapse: collapse; }
                            th, td { padding: 10px; border: 1px solid black; text-align: center; }
                            h4, h5 { text-align: center; }
                        </style>
                    </head>
                    <body>
                        <h4>Today's Sales Summary</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Items Sold</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                salesData.forEach((item, index) => {
                    printContent += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.name}</td>
                            <td>${item.price.toFixed(2)}</td>
                            <td>${item.qty}</td>
                            <td>${item.sales.toFixed(2)}</td>
                        </tr>
                    `;
                });

                printContent += `
                            </tbody>
                        </table>
                        <h5>Total Items Sold: ${totalItemsSold}</h5>
                        <h5>Total Sales: ${totalSales.toFixed(2)}</h5>
                    </body>
                    </html>
                `;

                printWindow.document.write(printContent);
                printWindow.document.close();
                printWindow.print();
            });
        });
    </script>
