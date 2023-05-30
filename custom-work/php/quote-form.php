<?php
require_once  '../../wp-load.php';

// Check if form data is submitted
if (isset($_POST) && !empty($_POST)) {



    extract($_POST);
    $data =  array();
    $data['name'] = $_POST['name-1'];
    $data['email'] = $_POST['email-1'];
    $data['phone'] = $_POST['phone-1'];
    $data['quantity'] = $_POST['number-1'];
    $data['notes'] = $_POST['textarea-1'];
    $data['document'] = implode('||', $_POST['select-1']);
    $data['source_lng'] = $_POST['select-2'];
    $data['target_lng'] = implode('||', $_POST['select-3']);


   

   



    ///database connection
    global $wpdb;
    $table_name = $wpdb->prefix . 'get_a_quote';
    $wpdb->insert($table_name, $data);

    if ($wpdb->insert_id == false) {
        $data = array('code' => "error", 'message' => "The form was not submitted.");
        echo json_encode($data);
        die;
    } else {
        $data['quote_id'] = $wpdb->insert_id;
        ///check uploaded documents 
        if (isset($_FILES['upload-1']) && $_FILES['upload-1']['error'][0] !== 4) {
            $targetFolder = ABSPATH . 'custom-work/documents/quote_' . $wpdb->insert_id;

            if (!file_exists($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }

            ///upload multiple files  
            foreach ($_FILES['upload-1']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['upload-1']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES['upload-1']['name'][$key];

                    $targetPath = $targetFolder . '/' . $fileName;
                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        $data = array('code' => "error", 'message' => "Error uploading file");
                        echo json_encode($data);
                        die;
                    }
                }
            }
        } 

        $word = "Driver License";
        $data['is_driver_license'] = false;
        if (preg_match("/\b$word\b/i", $data['document'])) { 
            $data['is_driver_license'] = true;
        }

        
        ///response is all ok 
        $data = array('code' => "success", 'data' => $data);
        echo json_encode($data);
        die;
    }
}
