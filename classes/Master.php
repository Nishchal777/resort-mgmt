<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_message(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!is_numeric($v))
					$v = $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `message_list` set {$data} ";
		}else{
			$sql = "UPDATE `message_list` set {$data} where id = '{$id}' ";
		}
		
		$save = $this->conn->query($sql);
		if($save){
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['status'] = 'success';
			if(empty($id))
				$resp['msg'] = "Your message has successfully sent.";
			else
				$resp['msg'] = "Message details has been updated successfully.";
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occured.";
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if($resp['status'] =='success' && !empty($id))
		$this->settings->set_flashdata('success',$resp['msg']);
		if($resp['status'] =='success' && empty($id))
		$this->settings->set_flashdata('pop_msg',$resp['msg']);
		return json_encode($resp);
	}
	function delete_message(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `message_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Message has been deleted successfully.");

		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_room() {
		$_POST['description'] = htmlentities($_POST['description']);
		extract($_POST);
	
		// Initialize data string for SQL query
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!is_numeric($v)) {
					$v = $this->conn->real_escape_string($v);
				}
				if (!empty($data)) {
					$data .= ",";
				}
				$data .= " `{$k}`='{$v}' ";
			}
		}
	
		// Determine SQL query based on presence of ID
		if (empty($id)) {
			$sql = "INSERT INTO `room_list` SET {$data}";
		} else {
			$sql = "UPDATE `room_list` SET {$data} WHERE `id`='{$id}'";
		}
	
		// Execute SQL query
		$save = $this->conn->query($sql);
	
		// Initialize response array
		$resp = array();
	
		if ($save) {
			// Retrieve inserted/updated room ID
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['id'] = $rid;
			$resp['status'] = 'success';
	
			// Set success message based on operation (insert or update)
			if (empty($id)) {
				$resp['msg'] = "Room has been successfully added.";
			} else {
				$resp['msg'] = "Room details have been updated successfully.";
			}
	
			// Handle image upload if a file is provided
			if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
				// Ensure the uploads directory exists or create it
				if (!is_dir(base_app . 'uploads/rooms')) {
					mkdir(base_app . 'uploads/rooms', 0777, true);
				}
	
				$fname = 'uploads/rooms/' . $rid . '.png';
				$dir_path = base_app . $fname;
				$upload = $_FILES['img']['tmp_name'];
				$type = mime_content_type($upload);
				$allowed = array('image/png', 'image/jpeg');
	
				// Validate file type
				if (!in_array($type, $allowed)) {
					$resp['msg'] .= " But Image failed to upload due to invalid file type.";
				} else {
					// Move uploaded file to destination
					if (move_uploaded_file($upload, $dir_path)) {
						// Update database with image path
						$this->conn->query("UPDATE `room_list` SET `image_path`='{$fname}?v=" . time() . "' WHERE `id`='{$rid}'");
					} else {
						$resp['msg'] .= " But Image failed to upload.";
					}
				}
			}
		} else {
			// If query execution fails
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred.";
			$resp['err'] = $this->conn->error . "[{$sql}]";
		}
	
		// Set flash message for success
		if ($resp['status'] == 'success') {
			$this->settings->set_flashdata('success', $resp['msg']);
		}
	
		// Return JSON-encoded response
		return json_encode($resp);
	}
	
	function delete_room(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `room_list` set delete_flag = 1 where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Room has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_service(){
		$_POST['room_ids'] = implode(',',$_POST['room_ids']);
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!is_numeric($v))
					$v = $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `service_list` set {$data} ";
		}else{
			$sql = "UPDATE `service_list` set {$data} where id = '{$id}' ";
		}
		$check = $this->conn->query("SELECT * FROM `service_list` where `name` ='{$name}' and room_ids = '{$room_ids}' and delete_flag = 0 ".($id > 0 ? " and id != '{$id}' " : ""))->num_rows;
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Service already exists.";
		}else{
			$save = $this->conn->query($sql);
			if($save){
				$rid = !empty($id) ? $id : $this->conn->insert_id;
				$resp['status'] = 'success';
				if(empty($id))
					$resp['msg'] = "Service has successfully added.";
				else
					$resp['msg'] = "Service has been updated successfully.";
			}else{
				$resp['status'] = 'failed';
				$resp['msg'] = "An error occured.";
				$resp['err'] = $this->conn->error."[{$sql}]";
			}
			if($resp['status'] =='success')
			$this->settings->set_flashdata('success',$resp['msg']);
		}
		return json_encode($resp);
	}
	function delete_service(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `service_list` set delete_flag = 1 where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Service has been deleted successfully.");

		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_reservation(){
		if(empty($_POST['id'])){
			$prefix = date("Ym")."-";
			$code = sprintf("%'.04d",1);
			while(true){
				$check = $this->conn->query("SELECT * FROM `reservation_list` where `code` = '{$prefix}{$code}'")->num_rows;
				if($check > 0){
					$code = sprintf("%'.04d",ceil($code)+ 1);
				}else{
					break;
				}
			}
			$_POST['code'] = $prefix.$code;
		}
		extract($_POST);
		$check = $this->conn->query("SELECT * FROM `reservation_list` where room_id = '{$room_id}' and ((date('{$check_in}') BETWEEN `check_in` and `check_out`) or (date('{$check_out}') BETWEEN `check_in` and `check_out`)) and `status` in (0,1) ")->num_rows;
		if($check > 0){
			$resp['status'] = "failed";
			$resp['msg'] = "Your Date of Reservation for this room complicates with other reservations.";
			return json_encode($resp);
		}
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!is_numeric($v))
					$v = $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `reservation_list` set {$data} ";
		}else{
			$sql = "UPDATE `reservation_list` set {$data} where id = '{$id}' ";
		}
		$save = $this->conn->query($sql);
		if($save){
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['id'] = $rid;
			$resp['status'] = 'success';
			if(empty($id))
				$resp['msg'] = "Room Reservation has successfully submitted.";
			else
				$resp['msg'] = "Room Reservation details has been updated successfully.";
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occured.";
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		
		if($resp['status'] =='success')
		$this->settings->set_flashdata('success',$resp['msg']);
		return json_encode($resp);
	}
	function delete_reservation(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `reservation_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Reservation Details has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function update_reservation_status(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `reservation_list` set `status` = '{$status}' where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Reservation status has been updated successfully.");

		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
	function save_activity() {
		$_POST['description'] = htmlentities($_POST['description']);
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!is_numeric($v)) {
					$v = $this->conn->real_escape_string($v);
				}
				if (!empty($data)) {
					$data .= ",";
				}
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if (empty($id)) {
			$sql = "INSERT INTO `activity_list` SET {$data}";
		} else {
			$sql = "UPDATE `activity_list` SET {$data} WHERE id = '{$id}'";
		}
		$save = $this->conn->query($sql);
	
		if ($save) {
			$rid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['id'] = $rid;
			$resp['status'] = 'success';
			if (empty($id)) {
				$resp['msg'] = "Activity has successfully been added.";
			} else {
				$resp['msg'] = "Activity details have been updated successfully.";
			}
			
			if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
				if (!is_dir(base_app . 'uploads/activities')) {
					mkdir(base_app . 'uploads/activities', 0777, true);
				}
	
				$fname = 'uploads/activities/' . $rid . '.png';
				$dir_path = base_app . $fname;
				$upload = $_FILES['img']['tmp_name'];
				$type = mime_content_type($upload);
				$allowed = array('image/png', 'image/jpeg', 'image/jpg');
	
				if (!in_array($type, $allowed)) {
					$resp['msg'] .= " But image failed to upload due to invalid file type.";
				} else {
					// Move uploaded file to destination
					if (move_uploaded_file($upload, $dir_path)) {
						// Update database with image path
						$this->conn->query("UPDATE activity_list SET `image_path` = '{$fname}' WHERE id = '{$rid}'");
					} else {
						$resp['msg'] .= " But image failed to upload due to an unknown reason.";
					}
				}
			}
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred.";
			$resp['err'] = $this->conn->error . "[{$sql}]";
		}
	
		if ($resp['status'] == 'success') {
			$this->settings->set_flashdata('success', $resp['msg']);
		}
		return json_encode($resp);
	}
	
	function delete_activity(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `activity_list` set delete_flag = 1 where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Activity has been deleted successfully.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_reservation':
		echo $Master->save_reservation();
	break;
	case 'delete_reservation':
		echo $Master->delete_reservation();
	break;
	case 'update_reservation_status':
		echo $Master->update_reservation_status();
	break;
	case 'save_message':
		echo $Master->save_message();
	break;
	case 'delete_message':
		echo $Master->delete_message();
	break;
	case 'save_room':
		echo $Master->save_room();
	break;
	case 'delete_room':
		echo $Master->delete_room();
	break;
	case 'save_service':
		echo $Master->save_service();
	break;
	case 'delete_service':
		echo $Master->delete_service();
	break;
	case 'save_activity':
		echo $Master->save_activity();
	break;
	case 'delete_activity':
		echo $Master->delete_activity();
	break;
	default:
		// echo $sysset->index();
		break;
}