<?php include 'db_connect.php';

if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM sales_list where id=".$_GET['id'])->fetch_array();
	foreach($qry as $k => $val){
		$$k = $val;
	}
	$inv = $conn->query("SELECT * FROM inventory where type=2 and form_id=".$_GET['id']);

}

?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<h4>POS</h4>
			</div>
		 <div class="card-body">
                    <?php echo "Welcome back " . ($_SESSION['login_name'] ?? 'Guest') . "!"; ?>
                     </div>
				<form action="" id="manage-sales">
					<input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>">
					<input type="hidden" name="ref_no" value="<?php echo isset($ref_no) ? $ref_no : '' ?>">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group col-md-5">
								<label class="control-label">Customer</label>
								<select name="customer_id" id="" class="custom-select browser-default select2">
									<option value="0" selected="">Guest</option>
								<?php 

								$customer = $conn->query("SELECT * FROM customer_list order by name asc");
								while($row=$customer->fetch_assoc()):
								?>
									<option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
								<?php endwhile; ?>
								</select>
							</div>
							    <!-- Total Sales Section -->
                <div class="alert alert-success col-md-4 ml-auto">
                    <p class="text-right"><b><large>Total Sales Today</large></b></p>
                    <hr>
                    <p class="text-right">
                        <b><large>
                            <?php 
                            include 'db_connect.php';
                            $sales = $conn->query("SELECT SUM(total_amount) AS amount FROM sales_list WHERE DATE(date_updated) = '" . date('Y-m-d') . "'");
                            echo $sales && $sales->num_rows > 0 ? number_format($sales->fetch_array()['amount'], 2) : "0.00";
                            ?>
                        </large></b>
						</div>
							</div>
						<hr>
						<div class="row mb-3">
								<div class="col-md-4">
									<label class="control-label">Product</label>
									<select name="" id="product" class="custom-select browser-default select2">
										<option value=""></option>
									<?php 
									$cat = $conn->query("SELECT * FROM category_list order by name asc");
										while($row=$cat->fetch_assoc()):
											$cat_arr[$row['id']] = $row['name'];
										endwhile;
									$product = $conn->query("SELECT * FROM product_list  order by name asc");
									while($row=$product->fetch_assoc()):
										$prod[$row['id']] = $row;
									?>
										<option value="<?php echo $row['id'] ?>" data-name="<?php echo $row['name'] ?>" data-description="<?php echo $row['description'] ?>"><?php echo $row['name'] . ' | ' . $row['sku'] ?></option>
									<?php endwhile; ?>
									</select>
								</div>
								<div class="col-md-2">
									<label class="control-label">Qty</label>
									<input type="number" class="form-control text-right" step="any" id="qty" >
								</div>
								<div class="col-md-3">
									<label class="control-label">&nbsp</label>
									<button class="btn btn-block btn-sm btn-primary" type="button" id="add_list"><i class="fa fa-plus"></i> Add to List</button>
								</div>


						</div>
						<div class="row">
							<table class="table table-bordered" id="list">
								<colgroup>
									<col width="30%">
									<col width="10%">
									<col width="25%">
									<col width="25%">
									<col width="10%">
								</colgroup>
								<thead>
									<tr>
										<th class="text-center">Product</th>
										<th class="text-center">Qty</th>
										<th class="text-center">Price</th>
										<th class="text-center">Amount</th>
										<th class="text-center"></th>
									</tr>
								</thead>
								<tbody>
									<?php 
									if(isset($id)):
									while($row = $inv->fetch_assoc()): 
										foreach(json_decode($row['other_details']) as $k=>$v){
											$row[$k] = $v;
										}
									?>
										<tr class="item-row">
											<td>
												<input type="hidden" name="inv_id[]" value="<?php echo $row['id'] ?>">
												<input type="hidden" name="product_id[]" value="<?php echo $row['product_id'] ?>">
												<p class="pname">Name: <b><?php echo $prod[$row['product_id']]['name'] ?></b></p>
												<p class="pdesc"><small><i>Description: <b><?php echo $prod[$row['product_id']]['description'] ?></b></i></small></p>
											</td>
											<td>
												<input type="number" min="1" step="any" name="qty[]" value="<?php echo $row['qty'] ?>" class="text-right">
											</td>
											<td>
												<input type="hidden" min="1" step="any" name="price[]" value="<?php echo $row['price'] ?>" class="text-right">
												<p class="text-right"><?php echo $row['price'] ?></p>
											</td>
											<td>
												<p class="amount text-right"></p>
											</td>
											<td class="text-center">
												<buttob class="btn btn-sm btn-danger" onclick = "rem_list($(this))"><i class="fa fa-trash"></i></buttob>
											</td>
										</tr>
									<?php endwhile; ?>
									<?php endif; ?>
								</tbody>
								<tfoot>
									<tr>
										<th class="text-right" colspan="3">Total</th>
										<th class="text-right tamount"></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class="row">
							<button class="btn btn-primary btn-sm btn-block float-right " type="button" id="pay">Pay</button>
						</div>
					</div>
					<div class="modal fade" id="pay_modal" role='dialog'>
					    <div class="modal-dialog modal-md" role="document">
					      <div class="modal-content">
					        <div class="modal-header">
					        <h5 class="modal-title"></h5>
					      </div>
					      <div class="modal-body">
					      	<div class="container-fluid">
					      		<div class="form-group">
					      			<label for="" class="control-label">Total Amount</label>
					      			<input type="text" name="tamount" value="" class="form-control text-right" readonly="">
					      		</div>
					      		<div class="form-group">
					      			<label for="" class="control-label">Amount Tendered</label>
					      			<input type="number" name="amount_tendered" value="0" min="0" class="form-control text-right" >
					      		</div>
					      		<div class="form-group">
					      			<label for="" class="control-label">Change</label>
					      			<input type="number" name="change" value="0" min="0" class="form-control text-right" readonly="">
					      		</div>
					      	</div>
					      </div>
					      <div class="modal-footer">
					        <button class="btn btn-primary" type="button" id="manage-sales">Print</button>
					        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					      </div>
					      </div>
					    </div>
					  </div>
				</form>
			</div>
			
		</div>
	</div>
</div>
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
			<buttob class="btn btn-sm btn-danger" onclick = "rem_list($(this))"><i class="fa fa-trash"></i></buttob>
		</td>
	</tr>
	</table>
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
                end_load();
                return false;
            }
            $('#pay_modal').modal('show');
        });

        $(document).ready(function() {
    // Add keyboard shortcuts for specific actions
    $(document).keydown(function(e) {
        // Enter key: Trigger Pay button click (Key code 13)
        if (e.key === "Enter" || e.keyCode === 13) {
            e.preventDefault();  // Prevent default action (form submission)
            $('#pay').click();   // Trigger the pay button click
        }

        // Insert key: Focus on the select2 input for the product (Key code 45)
        if (e.key === "Insert" || e.keyCode === 45) {
            e.preventDefault();  // Prevent the default action
            $('#product').focus();  // Focus on the select2 product input
        }

        // End key: Trigger the form submit (for managing sales) (Key code 35)
        if (e.key === "End" || e.keyCode === 35) {
            e.preventDefault();  // Prevent the default action
            $('#manage-sales').submit();  // Trigger the submit of manage-sales form
        }

        // Insert key again: Open the select2 dropdown for product selection (Key code 45)
        if (e.key === "Insert" || e.keyCode === 45) {
            e.preventDefault();  // Prevent the default action (if any)
            $('#product').select2('open');  // Open the select2 dropdown for product selection
        }
    });
});

        // Automatically add the product when it is selected
        $('#product').change(function() {
            var product = $(this).val();
            var qty = $('#qty').val();
            if (product != '' && qty != '') {
                add_product_to_list(product, qty);
            }

            // After selecting a product, automatically focus on the quantity field
            $('#qty').focus(); // Set focus to the quantity input field
        });

        // Automatically trigger when quantity is changed
        $('#qty').keyup(function() {
            var product = $('#product').val();
            var qty = $(this).val();
            if (product != '' && qty != '') {
                add_product_to_list(product, qty);
            }
        });

        // Submit the form when the 'manage-sales' form is clicked
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
    });

    function add_product_to_list(product, qty) {
        if (product === '' || qty === '') return;

        var tr = $('#tr_clone tr.item-row').clone();

        // Check if the product already exists in the list
        if ($('#list').find('tr[data-id="' + product + '"]').length > 0) {
            alert_toast("Product already on the list", 'danger');
            return false;
        }

        // Fetch product availability from the server via AJAX
        $.ajax({
            url: 'ajax.php?action=chk_prod_availability',
            method: 'POST',
            data: { id: product },
            success: function(resp) {
                resp = JSON.parse(resp);
                if (resp.available >= qty) {
                    // Update the item row with the product data
                    tr.attr('data-id', product);
                    tr.find('.pname b').html($("#product option[value='" + product + "']").attr('data-name'));
                    tr.find('.pdesc b').html($("#product option[value='" + product + "']").attr('data-description'));
                    tr.find('.price').html(resp.price);
                    tr.find('[name="product_id[]"]').val(product);
                    tr.find('[name="qty[]"]').val(qty);
                    tr.find('[name="price[]"]').val(resp.price);

                    // Calculate the amount based on price and quantity
                    var amount = parseFloat(resp.price) * parseFloat(qty);
                    tr.find('.amount').html(amount.toLocaleString('en-US', { style: 'decimal', maximumFractionDigits: 2, minimumFractionDigits: 2 }));

                    // Add the row to the list
                    $('#list tbody').append(tr);
                    calculate_total(); // Recalculate the total

                    // Clear the input fields
                    $('#product').val('').select2({
                        placeholder: "Please select here",
                        width: "100%"
                    });
                    $('#qty').val(''); // Optionally reset the quantity field
                    $('#price').val('');
                } else {
                    alert_toast("Product quantity is greater than available stock.", 'danger');
                }
            }
        });
    }

    // Calculate the total amount
    function calculate_total() {
        var total = 0;
        $('#list tbody').find('.item-row').each(function() {
            var _this = $(this).closest('tr');
            var qty = parseFloat(_this.find('[name="qty[]"]').val());
            var price = parseFloat(_this.find('[name="price[]"]').val());
            var amount = qty * price;
            amount = amount > 0 ? amount : 0;
            _this.find('p.amount').html(amount.toLocaleString('en-US', { style: 'decimal', maximumFractionDigits: 2, minimumFractionDigits: 2 }));
            total += amount;
        });
        $('[name="tamount"]').val(total);
        $('#list .tamount').html(total.toLocaleString('en-US', { style: 'decimal', maximumFractionDigits: 2, minimumFractionDigits: 2 }));
    }

    // Update the amount when qty or price changes
    $('[name="qty[]"], [name="price[]"]').keyup(function() {
        calculate_total();
    });

    // Update the change on tendered amount change
    $('[name="amount_tendered"]').keyup(function() {
        var tendered = $(this).val();
        var tamount = $('[name="tamount"]').val();
        $('[name="change"]').val(parseFloat(tendered) - parseFloat(tamount));
    });

    // Remove product from the list
    function rem_list(_this) {
        _this.closest('tr').remove();
        calculate_total(); // Recalculate total when item is removed
    }
</script>
