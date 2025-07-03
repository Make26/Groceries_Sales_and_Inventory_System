<?php
// Include database connection
include 'db_connect.php';

// Fetch sales and inventory data if ID is set
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM sales_list WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $qry = $stmt->get_result()->fetch_assoc();
    if ($qry) {
        foreach ($qry as $k => $val) {
            $$k = $val;
        }
        $inv_stmt = $conn->prepare("SELECT * FROM inventory WHERE type = 2 AND form_id = ?");
        $inv_stmt->bind_param("i", $_GET['id']);
        $inv_stmt->execute();
        $inv = $inv_stmt->get_result();
    }
}
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="text-center">Point of Sale System</h4>
            </div>
            <div class="card-body">
                <form action="" id="manage-sales">
                    <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
                    <input type="hidden" name="ref_no" value="<?php echo isset($ref_no) ? $ref_no : ''; ?>">

                    <div class="col-md-12">
                        <div class="row mb-4">
                            <!-- Customer Dropdown -->
                            <div class="form-group col-md-5">
                                <label class="control-label font-weight-bold">Customer</label>
                                <select name="customer_id" class="custom-select select2">
                                    <option value="0" selected>Guest</option>
                                    <?php
                                    $customer = $conn->query("SELECT * FROM customer_list ORDER BY name ASC");
                                    while ($row = $customer->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <!-- Total Sales Today -->
                            <div class="alert alert-success col-md-4 ml-auto">
                                <p class="text-right mb-1"><b>Total Sales Today</b></p>
                                <hr>
                                <p class="text-right font-weight-bold text-primary h4">
                                    <?php
                                    $sales = $conn->query("SELECT SUM(total_amount) AS amount FROM sales_list WHERE DATE(date_updated) = CURDATE()");
                                    echo $sales->num_rows > 0 ? number_format($sales->fetch_array()['amount'], 2) : "0.00";
                                    ?>
                                </p>
                            </div>
                        </div>

                        <!-- Product Selection and Quantity -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="control-label font-weight-bold">Product</label>
                                <select name="product" id="product" class="custom-select select2">
                                    <option value="" selected disabled>Select a Product</option>
                                    <?php
                                    $product = $conn->query("SELECT * FROM product_list ORDER BY name ASC");
                                    while ($row = $product->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-description="<?php echo htmlspecialchars($row['description']); ?>">
                                            <?php echo htmlspecialchars($row['name']) . ' | ' . htmlspecialchars($row['sku']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="control-label font-weight-bold">Qty</label>
                                <input type="number" class="form-control text-right" step="any" id="qty" name="qty">
                            </div>
                        </div>

                        <!-- Product List Table -->
                        <div class="row">
                            <table class="table table-bordered table-striped" id="list">
                                <colgroup>
                                    <col width="30%">
                                    <col width="10%">
                                    <col width="25%">
                                    <col width="25%">
                                    <col width="10%">
                                </colgroup>
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center">Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Price</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($id)):
                                        while ($row = $inv->fetch_assoc()):
                                            foreach (json_decode($row['other_details']) as $k => $v) {
                                                $row[$k] = $v;
                                            }
                                    ?>
                                        <tr class="item-row">
                                            <td>
                                                <input type="hidden" name="inv_id[]" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="product_id[]" value="<?php echo $row['product_id']; ?>">
                                                <p class="pname font-weight-bold">Name: <?php echo $row['name']; ?></p>
                                                <p class="pdesc text-muted"><small>Description: <?php echo $row['description']; ?></small></p>
                                            </td>
                                            <td>
                                                <input type="number" min="1" step="any" name="qty[]" value="<?php echo $row['qty']; ?>" class="form-control text-right">
                                            </td>
                                            <td>
                                                <input type="hidden" name="price[]" value="<?php echo $row['price']; ?>">
                                                <p class="text-right text-success font-weight-bold">$<?php echo number_format($row['price'], 2); ?></p>
                                            </td>
                                            <td>
                                                <p class="amount text-right text-primary font-weight-bold">$<?php echo number_format($row['qty'] * $row['price'], 2); ?></p>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm" onclick="rem_list($(this))"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-right" colspan="3">Total</th>
                                        <th class="text-right tamount text-primary font-weight-bold">P0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Payment Modal Trigger -->
                        <div class="row mt-4">
                            <button class="btn btn-primary btn-lg btn-block" type="button" id="pay">Pay</button>
                        </div>
                    </div>

                   <!-- Payment Modal -->
<div class="modal fade" id="pay_modal" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Payment</h5>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-group">
                        <label class="font-weight-bold">Total Amount</label>
                        <input type="text" name="tamount" value="0" class="form-control text-right" readonly>
                                        </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Amount Tendered</label>
                        <input type="number" name="amount_tendered" value="0" min="0" class="form-control text-right">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Change</label>
                        <input type="number" name="change" value="0" min="0" class="form-control text-right" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" type="button" id="manage-sales">Print</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Product Row Template -->
<div id="tr_clone">
    <table>
        <tr class="item-row">
            <td>
                <input type="hidden" name="inv_id[]" value="">
                <input type="hidden" name="product_id[]" value="">
                <p class="pname">Name: <b>product</b></p>
                <p class="pdesc"><small><i>Description: <b>Description</b></i></small></p>
            </td>
            <td>
                <input type="number" min="1" step="any" name="qty[]" value="" class="text-right">
            </td>
            <td>
                <input type="hidden" min="1" step="any" name="price[]" value="" class="text-right" readonly="">
                <p class="price text-right">0</p>
            </td>
            <td>
                <p class="amount text-right"></p>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-danger" onclick="rem_list($(this))"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    </table>
</div>
</div>
<style type="text/css">
	#tr_clone{
		display: none;
	}
	td{
		vertical-align: middle;
	}
	td p {
		margin: unset;
	}
	td input[type='number']{
		height: calc(100%);
		width: calc(100%);

	}
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button { 
	  -webkit-appearance: none; 
	  margin: 0; 
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
<script>
$(document).ready(function() {

    // Initialize select2 for product search
    $('.select2').select2({
        placeholder: "Please select here",
        width: "100%"
    });

    // Pay button click handler
    $('#pay').click(function() {
        if ($("#list .item-row").length <= 0) {
            alert_toast("Please insert at least 1 item first.", 'danger');
            return false;
        }

        // Calculate total before showing the modal
        calculateTotal();

        // Set the total amount in the payment modal
        const totalAmount = $('[name="tamount"]').val();
        $('#pay_modal [name="tamount"]').val(totalAmount);

        // Show the payment modal
        $('#pay_modal').modal('show');
    });

    // Recalculate total when quantity or price changes
    $('[name="qty[]"], [name="price[]"]').on('input', function() {
        calculateTotal();
    });

    // Add keyboard shortcuts for specific actions
    $(document).keydown(function(e) {
        // Enter key: Trigger Pay button click
        if (e.key === "Enter" || e.keyCode === 13) {
            e.preventDefault();
            $('#pay').click();
        }

        // Insert key: Focus on the select2 input for the product
        if (e.key === "Insert" || e.keyCode === 45) {
            e.preventDefault();
            $('#product').focus();
        }

        // End key: Trigger form submit for managing sales
        if (e.key === "End" || e.keyCode === 35) {
            e.preventDefault();
            $('#manage-sales').submit();
        }
    });

    // Automatically add the product when selected
    $('#product').change(function() {
        var product = $(this).val();
        var qty = $('#qty').val();
        if (product != '' && qty != '') {
            addProductToList(product, qty);
        }

        // After selecting a product, focus on the quantity field
        $('#qty').focus();
    });

    // Automatically trigger when quantity is changed
    $('#qty').keyup(function() {
        var product = $('#product').val();
        var qty = $(this).val();
        if (product != '' && qty != '') {
            addProductToList(product, qty);
        }
    });
    // Detect click on the "Print" button inside modal (without renaming it)
$('button#manage-sales[type="button"]').click(function () {
    // Optional: close modal before submitting
    $('#pay_modal').modal('hide');

    // Trigger the original form submission
    $('form#manage-sales').submit();
});


    // Submit form when 'manage-sales' form is clicked
    $('#manage-sales').submit(function(e) {
        e.preventDefault();
        start_load();
        if ($("#list .item-row").length <= 0) {
            alert_toast("Please insert at least 1 item first.", 'danger');
            end_load();
            return false;
        }

        $.ajax({
            url: 'ajax.php?action=save_sales',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp > 0) {
                    end_load();
                    alert_toast("Data successfully submitted", 'success');
                    uni_modal('Print', "print_sales.php?id=" + resp);
                    $('#uni_modal').modal({ backdrop: 'static', keyboard: false });
                }
            }
        });
    });

    // Toggle the Gcash reference input field visibility based on selected payment method
    $('#payment_method').on('change', function() {
        if (this.value === 'gcash') {
            $('#gcash_reference').show();
        } else {
            $('#gcash_reference').hide();
        }
    });

    // Automatically calculate change when amount tendered is updated
    $('[name="amount_tendered"]').on('input', function() {
        const tendered = parseFloat($(this).val()) || 0;
        const totalAmount = parseFloat($('[name="tamount"]').val()) || 0;
        const change = tendered - totalAmount;

        // Update the change field
        $('[name="change"]').val(change.toFixed(2));
    });

    // Remove product from the list when trash button is clicked
    $(document).on('click', '.btn-danger', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });

});

// Function to add product to list
function addProductToList(product, qty) {
    if (product === '' || qty === '') return;

    var tr = $('#tr_clone tr.item-row').clone();

    // Check if the product already exists in the list
    if ($('#list').find('tr[data-id="' + product + '"]').length > 0) {
        alert_toast("⚠️Product already on the list", 'danger');
        return false;
    }

    // Fetch product availability from the server
    $.ajax({
        url: 'ajax.php?action=chk_prod_availability',
        method: 'POST',
        data: { id: product },
        success: function(resp) {
            resp = JSON.parse(resp);
            if (resp.available >= qty) {
                // Update item row with product data
                tr.attr('data-id', product);
                tr.find('.pname b').html($("#product option[value='" + product + "']").attr('data-name'));
                tr.find('.pdesc b').html($("#product option[value='" + product + "']").attr('data-description'));
                tr.find('.price').html(resp.price);
                tr.find('[name="product_id[]"]').val(product);
                tr.find('[name="qty[]"]').val(qty);
                tr.find('[name="price[]"]').val(resp.price);

                // Calculate amount
                var amount = parseFloat(resp.price) * parseFloat(qty);
                tr.find('.amount').html(amount.toLocaleString('en-US', { style: 'decimal', maximumFractionDigits: 2, minimumFractionDigits: 2 }));

                // Add the row to the list
                $('#list tbody').append(tr);
                calculateTotal(); // Recalculate total

                // Clear input fields
                $('#product').val('').select2({ placeholder: "Please select here", width: "100%" });
                $('#qty').val('');
            } else {
            alert_toast("⚠️ WARNING: NO PRODUCT available.", 'danger');
            }
        }
    });
}

// Function to calculate the total amount
function calculateTotal() {
    let total = 0;

    // Iterate through each item row
    $('#list tbody').find('.item-row').each(function() {
        const row = $(this).closest('tr');
        const qty = parseFloat(row.find('[name="qty[]"]').val()) || 0;
        const price = parseFloat(row.find('[name="price[]"]').val()) || 0;
        const amount = Math.max(qty * price, 0);

        // Update amount in the row
        row.find('p.amount').html(amount.toLocaleString('en-US', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        // Add to total
        total += amount;
    });

    // Update total amount in hidden input and display field
    $('[name="tamount"]').val(total);
    $('#list .tamount').html(total.toLocaleString('en-US', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
}
</script>
