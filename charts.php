<?php 
include 'db_connect.php'; 

// Ensure database connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch daily sales data
$salesQuery = $conn->prepare("
    SELECT 
        DATE(i.date_updated) AS sale_date, 
        SUM(i.qty) AS sold_qty, 
        SUM(i.qty * p.price) AS total_sales 
    FROM 
        inventory i
    JOIN 
        product_list p ON i.product_id = p.id 
    WHERE 
        i.type = 2
    GROUP BY 
        sale_date
    ORDER BY 
        sale_date ASC
");
$salesQuery->execute();
$salesData = $salesQuery->get_result();

// Get the stock data (No change needed)
$stockQuery = $conn->prepare("
    SELECT p.id, p.name, 
           COALESCE(SUM(CASE WHEN i.type = 1 THEN i.qty ELSE 0 END), 0) AS stock_in, 
           COALESCE(SUM(CASE WHEN i.type = 2 THEN i.qty ELSE 0 END), 0) AS stock_out 
    FROM product_list p
    LEFT JOIN inventory i ON p.id = i.product_id
    GROUP BY p.id 
    ORDER BY p.name ASC
");
$stockQuery->execute();
$stockData = $stockQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* You can style the chart container and page here */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .chart-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
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
<body>

    <!-- Sales Report Section -->
    <div class="chart-container">
        <h2>Daily Sales Report</h2>
        <canvas id="salesChart"></canvas>
    </div>

    <!-- Product Stock Overview Section -->
    <div class="chart-container">
        <h2>Product Stock Overview</h2>
        <canvas id="stockChart"></canvas>
    </div>

    <script>
        // Prepare data for the Sales Chart
        const saleDates = [], soldQty = [], totalSales = [];
        <?php while ($row = $salesData->fetch_assoc()): ?>
            saleDates.push("<?php echo $row['sale_date']; ?>");
            soldQty.push(<?php echo $row['sold_qty']; ?>);
            totalSales.push(<?php echo $row['total_sales']; ?>);
        <?php endwhile; ?>

        // Initialize the Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line', // Chart type (line graph)
            data: {
                labels: saleDates, // Dates for X-axis
                datasets: [
                    { 
                        label: 'Items Sold', 
                        data: soldQty, // Items sold data for Y-axis
                        backgroundColor: 'rgba(54, 162, 235, 0.5)', 
                        borderColor: 'rgba(54, 162, 235, 1)', 
                        borderWidth: 2, 
                        fill: true // Fill the area under the line
                    },
                    { 
                        label: 'Total Sales (PHP)', 
                        data: totalSales, // Total sales data for Y-axis
                        backgroundColor: 'rgba(75, 192, 192, 0.5)', 
                        borderColor: 'rgba(75, 192, 192, 1)', 
                        borderWidth: 2, 
                        fill: true // Fill the area under the line
                    }
                ]
            },
            options: { 
                responsive: true, // Make the chart responsive
                plugins: { 
                    legend: { position: 'top' }, // Position of the legend
                    tooltip: { 
                        mode: 'index', 
                        intersect: false, 
                        callbacks: {
                            label: function(tooltipItem) {
                                let value = tooltipItem.raw;
                                let formattedValue = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(value);
                                return tooltipItem.datasetIndex === 0 ? 
                                    `Items Sold: ${tooltipItem.raw}` : 
                                    `Total Sales: ${formattedValue}`;
                            }
                        }
                    }
                },
                scales: { 
                    x: { 
                        grid: { display: true }, 
                        title: { display: true, text: 'Date' } // Label for X-axis
                    },
                    y: { 
                        beginAtZero: true, 
                        grid: { display: true }, 
                        title: { display: true, text: 'Quantity / Sales (PHP)' } // Label for Y-axis
                    }
                }
            }
        });

        // Prepare data for the Stock Chart
        const labels = [], stockIn = [], stockOut = [], stockAvailable = [];
        <?php while ($row = $stockData->fetch_assoc()):
            $available = $row['stock_in'] - $row['stock_out']; ?>
            labels.push("<?php echo $row['name']; ?>");
            stockIn.push(<?php echo $row['stock_in']; ?>);
            stockOut.push(<?php echo $row['stock_out']; ?>);
            stockAvailable.push(<?php echo $available; ?>);
        <?php endwhile; ?>

        // Stock Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar', // Bar chart for stock data
            data: {
                labels: labels, // Product names for X-axis
                datasets: [
                    { 
                        label: 'Stock In', 
                        data: stockIn, 
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', 
                        borderColor: 'rgba(54, 162, 235, 1)', 
                        borderWidth: 1 
                    },
                    { 
                        label: 'Stock Out', 
                        data: stockOut, 
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', 
                        borderColor: 'rgba(255, 99, 132, 1)', 
                        borderWidth: 1 
                    },
                    { 
                        label: 'Available Stock', 
                        data: stockAvailable, 
                        backgroundColor: 'rgba(75, 192, 192, 0.6)', 
                        borderColor: 'rgba(75, 192, 192, 1)', 
                        borderWidth: 1 
                    }
                ]
            },
            options: { 
                responsive: true, 
                plugins: { 
                    legend: { position: 'top' }, 
                    tooltip: { mode: 'index', intersect: false } 
                },
                scales: { 
                    x: { 
                        grid: { display: true }, 
                        title: { display: true, text: 'Products' }
                    },
                    y: { 
                        beginAtZero: true, 
                        grid: { display: true }, 
                        title: { display: true, text: 'Stock Quantities' }
                    }
                }
            }
        });
    </script>

</body>
</html>
