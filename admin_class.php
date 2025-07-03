<?php
session_start();
ini_set('display_errors', 1);

class Action {
    private $db;

    public function __construct() {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }

    function __destruct() {
        $this->db->close();
        ob_end_flush();
    }

    function login() {
        extract($_POST);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        if (!$stmt) return "Query error: " . $this->db->error;
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            foreach ($row as $key => $value) {
                if ($key != 'password') {
                    $_SESSION['login_' . $key] = $value;
                }
            }
            return 1;
        } else {
            return 0;
        }
    }

    function login2() {
        extract($_POST);
        $qry = $this->db->query("SELECT * FROM user_info WHERE email = '$email' AND password = '$password'");
        if ($qry && $qry->num_rows > 0) {
            foreach ($qry->fetch_array() as $key => $value) {
                if ($key != 'password' && !is_numeric($key)) {
                    $_SESSION['login_' . $key] = $value;
                }
            }
            $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $this->db->query("UPDATE cart SET user_id = '{$_SESSION['login_user_id']}' WHERE client_ip = '$ip'");
            return 1;
        } else {
            return 3;
        }
    }

    function logout() {
        session_unset();
        session_destroy();
        header("location:login.php");
    }

    function logout2() {
        session_unset();
        session_destroy();
        header("location:../index.php");
    }

    function save_user() {
        extract($_POST);
        $data = "name = '$name', username = '$username', password = '$password', type = '$type'";
        if (empty($id)) {
            $save = $this->db->query("INSERT INTO users SET $data");
        } else {
            $save = $this->db->query("UPDATE users SET $data WHERE id = $id");
        }
        return $save ? 1 : 0;
    }

    function signup() {
        extract($_POST);
        $data = "first_name = '$first_name', last_name = '$last_name', mobile = '$mobile', address = '$address', email = '$email', password = '$password'";
        $chk = $this->db->query("SELECT * FROM user_info WHERE email = '$email'")->num_rows;
        if ($chk > 0) return 2;

        $save = $this->db->query("INSERT INTO user_info SET $data");
        if ($save) {
            $login = $this->login2();
            return 1;
        }
        return 0;
    }

    function save_settings() {
        extract($_POST);
        $data = "name='$name', email='$email', contact='$contact', about_content='" . addslashes($about_content) . "'";
        if (!empty($_FILES['cover_img']['tmp_name'])) {
            $fname = 'uploads/' . time() . '_' . $_FILES['cover_img']['name'];
            if (move_uploaded_file($_FILES['cover_img']['tmp_name'], $fname)) {
                $data .= ", cover_img='" . addslashes($fname) . "'";
            }
        }
        $check = $this->db->query("SELECT * FROM system_settings");
        $update = $check->num_rows > 0 ?
            $this->db->query("UPDATE system_settings SET $data") :
            $this->db->query("INSERT INTO system_settings SET $data");
        return $update ? 1 : 0;
    }

    // Continue implementing other save_*, delete_*, and utility methods as needed
    // Example: save_product(), delete_product(), save_customer(), etc.

	
	function save_category(){
		extract($_POST);
		$data = " name = '$name' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO category_list set ".$data);
		}else{
			$save = $this->db->query("UPDATE category_list set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}
	function delete_category(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM category_list where id = ".$id);
		if($delete)
			return 1;
	}
	function save_supplier(){
		extract($_POST);
		$data = " supplier_name = '$name' ";
		$data .= ", contact = '$contact' ";
		$data .= ", address = '$address' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO supplier_list set ".$data);
		}else{
			$save = $this->db->query("UPDATE supplier_list set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}
	function delete_supplier(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM supplier_list where id = ".$id);
		if($delete)
			return 1;
	}
	function save_product(){
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", sku = '$sku' ";
		$data .= ", category_id = '$category_id' ";
		$data .= ", description = '$description' ";
		$data .= ", price = '$price' ";

		if(empty($id)){
			$save = $this->db->query("INSERT INTO product_list set ".$data);
		}else{
			$save = $this->db->query("UPDATE product_list set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}

	function delete_product(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM product_list where id = ".$id);
		if($delete)
			return 1;
	}
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}

	function save_receiving(){
		extract($_POST);
		$data = " supplier_id = '$supplier_id' ";
		$data .= ", total_amount = '$tamount' ";
		
		if(empty($id)){
			$ref_no = sprintf("%'.08d\n", $ref_no);
			$i = 1;

			while($i == 1){
				$chk = $this->db->query("SELECT * FROM receiving_list where ref_no ='$ref_no'")->num_rows;
				if($chk > 0){
					$ref_no = mt_rand(1,99999999);
					$ref_no = sprintf("%'.08d\n", $ref_no);
				}else{
					$i=0;
				}
			}
			$data .= ", ref_no = '$ref_no' ";
			$save = $this->db->query("INSERT INTO receiving_list set ".$data);
			$id =$this->db->insert_id;
			foreach($product_id as $k => $v){
				$data = " form_id = '$id' ";
				$data .= ", product_id = '$product_id[$k]' ";
				$data .= ", qty = '$qty[$k]' ";
				$data .= ", type = '1' ";
				$data .= ", stock_from = 'receiving' ";
				$details = json_encode(array('price'=>$price[$k],'qty'=>$qty[$k]));
				$data .= ", other_details = '$details' ";
				$data .= ", remarks = 'Stock from Receiving-".$ref_no."' ";

				$save2[]= $this->db->query("INSERT INTO inventory set ".$data);
			}
			if(isset($save2)){
				return 1;
			}
		}else{
			$save = $this->db->query("UPDATE receiving_list set ".$data." where id =".$id);
			$ids = implode(",",$inv_id);
			$this->db->query("DELETE FROM inventory where type = 1 and form_id ='$id' and id NOT IN (".$ids.") ");
			foreach($product_id as $k => $v){
				$data = " form_id = '$id' ";
				$data .= ", product_id = '$product_id[$k]' ";
				$data .= ", qty = '$qty[$k]' ";
				$data .= ", type = '1' ";
				$data .= ", stock_from = 'receiving' ";
				$details = json_encode(array('price'=>$price[$k],'qty'=>$qty[$k]));
				$data .= ", other_details = '$details' ";
				$data .= ", remarks = 'Stock from Receiving-".$ref_no."' ";
				if(!empty($inv_id[$k])){
									$save2[]= $this->db->query("UPDATE inventory set ".$data." where id=".$inv_id[$k]);
				}else{
					$save2[]= $this->db->query("INSERT INTO inventory set ".$data);
				}
			}
			if(isset($save2)){
				
				return 1;
			}

		}
	}

	function delete_receiving(){
		extract($_POST);
		$del1 = $this->db->query("DELETE FROM receiving_list where id = $id ");
		$del2 = $this->db->query("DELETE FROM inventory where type = 1 and form_id = $id ");
		if($del1 && $del2)
			return 1;
	}
	function save_customer(){
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", contact = '$contact' ";
		$data .= ", address = '$address' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO customer_list set ".$data);
		}else{
			$save = $this->db->query("UPDATE customer_list set ".$data." where id=".$id);
		}
		if($save)
			return 1;
	}
	function delete_customer(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM customer_list where id = ".$id);
		if($delete)
			return 1;
	}

	function chk_prod_availability(){
		extract($_POST);
		$price = $this->db->query("SELECT * FROM product_list where id = ".$id)->fetch_assoc()['price'];
		$inn = $this->db->query("SELECT sum(qty) as inn FROM inventory where type = 1 and product_id = ".$id);
		$inn = $inn && $inn->num_rows > 0 ? $inn->fetch_array()['inn'] : 0;
		$out = $this->db->query("SELECT sum(qty) as `out` FROM inventory where type = 2 and product_id = ".$id);
		$out = $out && $out->num_rows > 0 ? $out->fetch_array()['out'] : 0;
		$available = $inn - $out;
		return json_encode(array('available'=>$available,'price'=>$price));

	}
	function save_sales(){
		extract($_POST);
		$data = " customer_id = '$customer_id' ";
		$data .= ", total_amount = '$tamount' ";
		$data .= ", amount_tendered = '$amount_tendered' ";
		$data .= ", amount_change = '$change' ";
		
		if(empty($id)){
			$ref_no = sprintf("%'.08d\n", $ref_no);
			$i = 1;

			while($i == 1){
				$chk = $this->db->query("SELECT * FROM sales_list where ref_no ='$ref_no'")->num_rows;
				if($chk > 0){
					$ref_no = mt_rand(1,99999999);
					$ref_no = sprintf("%'.08d\n", $ref_no);
				}else{
					$i=0;
				}
			}
			$data .= ", ref_no = '$ref_no' ";
			$save = $this->db->query("INSERT INTO sales_list set ".$data);
			$id =$this->db->insert_id;
			foreach($product_id as $k => $v){
				$data = " form_id = '$id' ";
				$data .= ", product_id = '$product_id[$k]' ";
				$data .= ", qty = '$qty[$k]' ";
				$data .= ", type = '2' ";
				$data .= ", stock_from = 'Sales' ";
				$details = json_encode(array('price'=>$price[$k],'qty'=>$qty[$k]));
				$data .= ", other_details = '$details' ";
				$data .= ", remarks = 'Stock out from Sales-".$ref_no."' ";

				$save2[]= $this->db->query("INSERT INTO inventory set ".$data);
			}
			if(isset($save2)){
				return $id;
			}
		}else{
			$save = $this->db->query("UPDATE sales_list set ".$data." where id=".$id);
			$ids = implode(",",$inv_id);
			$this->db->query("DELETE FROM inventory where type = 1 and form_id ='$id' and id NOT IN (".$ids.") ");
			foreach($product_id as $k => $v){
				$data = " form_id = '$id' ";
				$data .= ", product_id = '$product_id[$k]' ";
				$data .= ", qty = '$qty[$k]' ";
				$data .= ", type = '2' ";
				$data .= ", stock_from = 'Sales' ";
				$details = json_encode(array('price'=>$price[$k],'qty'=>$qty[$k]));
				$data .= ", other_details = '$details' ";
				$data .= ", remarks = 'Stock out from Sales-".$ref_no."' ";

				if(!empty($inv_id[$k])){
					$save2[]= $this->db->query("UPDATE inventory set ".$data." where id=".$inv_id[$k]);
				}else{
					$save2[]= $this->db->query("INSERT INTO inventory set ".$data);
				}
			}
			if(isset($save2)){
				return $id;
			}
		}
	}
	function delete_sales(){
		extract($_POST);
		$del1 = $this->db->query("DELETE FROM sales_list where id = $id ");
		$del2 = $this->db->query("DELETE FROM inventory where type = 2 and form_id = $id ");
		if($del1 && $del2)
			return 1;
	}

}