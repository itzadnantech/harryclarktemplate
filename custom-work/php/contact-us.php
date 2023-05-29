<?php
require_once  '../../wp-load.php';
require_once  ABSPATH . 'vendor/autoload.php';

//email files are here 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Check if form data is submitted 
if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['quote_id']) && (($_POST['quote_id'] != 0))) {

        $data = array();
        $image1Path = null;
        $image2Path = null;
        $data['license_number'] = $_POST['number-1'];
        $data['license_issuing_country'] = $_POST['select-1'];
        $data['conditions_applying_to_license'] = $_POST['select-2'];
        $data['date_license_first_obtained'] = $_POST['date-1'];
        $data['date_license_card_issued'] = $_POST['date-2'];
        $data['date_license_card_due_to_expire'] = $_POST['date-3'];



        ///personal info for license holder
        $data['license_holder_fname'] = $_POST['name-1-first-name'];
        $data['license_holder_mname'] = $_POST['name-1-middle-name'];
        $data['license_holder_lname'] = $_POST['name-1-last-name'];
        $data['license_holder_full_name'] = $_POST['name-1-first-name'] . ' ' . $_POST['name-1-middle-name'] . ' ' . $_POST['name-1-last-name'];
        $data['license_holder_place_of_birth'] = $_POST['text-1'];
        $data['license_holder_date_of_birth'] = $_POST['date-4'];
        $data['license_holder_gender'] = $_POST['select-3'];

        ///Manage Files 
        $quote_id = $_POST['quote_id'];

        ///get quote data from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'get_a_quote';
        $quote_object = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quote_id = %d", $quote_id));
        if (!$quote_object) {
            $data = array('code' => "error", 'message' => "Sorry quote data not found!");
            echo json_encode($data);
            die;
        }
        // Convert the object to an array
        $quote_data = get_object_vars($quote_object);
        // [quote_id] => 2
        // [name] => Lacey Sampson
        // [email] => jodirubyf@mailinator.com
        // [phone] => +1 (976) 419-1661
        // [quantity] => 92
        // [document] => Driver License
        // [source_lng] => Albanian
        // [target_lng] => Arabic||Armenian
        // [notes] => Duis a commodo in et

        $targetFolder = ABSPATH . 'custom-work/documents/quote_' . $quote_id;
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        for ($i = 1; $i <= 2; $i++) {
            if (isset($_FILES['upload-' . $i]) && $_FILES['upload-' . $i]['error'] == UPLOAD_ERR_OK) {
                $fileName = $_FILES["upload-" . $i]["name"];
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                if ($i == 1) {
                    $newFileName = "front_side." . $fileExtension;
                    $targetFile = $targetFolder . '/' . $newFileName;
                    $image1Path = $targetFile;
                } else {
                    $newFileName = "back_side." . $fileExtension;
                    $targetFile = $targetFolder . '/' . $newFileName;
                    $image2Path = $targetFile;
                }




                // Check if the file already exists
                if (!file_exists($targetFile)) {
                    if (!move_uploaded_file($_FILES["upload-" . $i]["tmp_name"], $targetFile)) {
                        $data = array('code' => "error", 'message' => "file not uploaded $newFileName");
                        echo json_encode($data);
                        die;
                    }
                }
            } else {
                $data = array('code' => "error", 'message' => "files not uploaded");
                echo json_encode($data);
                die;
            }
        }
        if (!Create_Word_Document($data, $quote_data, $image1Path, $image2Path)) {
            $data = array('code' => "error", 'message' => "Something wrong with docs");
            echo json_encode($data);
            die;
        }
        if (!Sent_Mail_User()) {
            $data = array('code' => "error", 'message' => "Something wrong with Email");
            echo json_encode($data);
            die;
        }
        $data = array('code' => "success", 'message' => "Thank You!");
        echo json_encode($data);
        die;
    } else {
        $data = array('code' => "error", 'message' => "Sorry Fill Quotation Form First!");
        echo json_encode($data);
        die;
    }
}


function Create_Word_Document($data = array(), $quote_data = array(), $image1Path = null, $image2Path = null)
{


    // Create a new Word document
    $phpWord = new \PhpOffice\PhpWord\PhpWord();

    /* Note: any element you append to a document must reside inside of a Section. */
    // Adding an empty Section to the document...
    $section = $phpWord->addSection();

    // add image to section
    $section->addImage('../logo.png', [
        'width' => 450,
        'height' => 100,
        'setAlignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, // optional, default is left-aligned
    ]);
    // echo "ok step 3";
    // die;


    // Define the table style
    $tableStyle = array(
        'borderSize' => 6,
        'borderColor' => '000000',
        'cellMargin' => 10,
        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        'bgColor' => 'f8f4f4'
    );

    $phpWord->addTableStyle('myTableStyle', $tableStyle);

    // Add the table to the document using the defined style
    $table = $section->addTable('myTableStyle');
    $section->addTextBreak(1);

    //$table = $section->addTable('My Document', array('size' => 14, 'bold' => true), array('align' => 'right'));
    $table->addRow();
    $cell = $table->addCell(10000);
    $cell->addText('Approval number (T200101)', array('bold' => true));
    $cell->addText('approved by Waka Kotahi to provide translations for driver licensing purposes', array());
    $table->addCell(5000)->addText('');

    $table->addRow();
    $table->addCell(1000)->addText('License number', array('bold' => true));
    $table->addCell(5000)->addText($data['license_number']);

    $table->addRow();
    $table->addCell(1000)->addText('Language of document', array('bold' => true));
    $table->addCell(5000)->addText($quote_data['source_lng']);

    $table->addRow();
    $table->addCell(1000)->addText('Type of document', array('bold' => true));
    $table->addCell(3000)->addText($quote_data['document']);

    $table->addRow();
    $table->addCell(1000)->addText('Licence issuing country', array('bold' => true));
    $table->addCell(3000)->addText($data['license_issuing_country']);

    $table->addRow();
    $table->addCell(1000)->addText('Licence issuing authority', array('bold' => true));
    $table->addCell(3000)->addText('');

    $table->addRow();
    $table->addCell(2000)->addText('Date licence first obtained', array('bold' => true));
    $table->addCell(2000)->addText($data['date_license_first_obtained']);

    $table->addRow();
    $table->addCell(2000)->addText('Date licence card was issued', array('bold' => true));
    $table->addCell(2000)->addText($data['date_license_card_issued'], array());
    // Adding new rows to the table...
    $table->addRow();
    $table->addCell(2000)->addText('Date licence card is due to expire', array('bold' => true));
    $table->addCell(2000)->addText($data['date_license_card_due_to_expire'], array());
    $table->addRow();
    $table->addCell(2000)->addText('Licence class/es held', array('bold' => true));
    $table->addCell(2000)->addText('');
    $table->addRow();
    $table->addCell(2000)->addText('Description of licence class/es', array('bold' => true));
    $table->addCell(2000)->addText('');
    $table->addRow();
    $table->addCell(2000)->addText('Conditions applying to licence', array('bold' => true));
    $table->addCell(2000)->addText($data['conditions_applying_to_license'], array());
    $table->addRow();
    $table->addCell(2000)->addText("Licence holder's Full Name", array('bold' => true));
    $table->addCell(2000)->addText($data['license_holder_full_name'], array());
    $table->addRow();
    $table->addCell(2000)->addText("Licence holder's place of birth", array('bold' => true));
    $table->addCell(2000)->addText($data['license_holder_place_of_birth'], array());
    $table->addRow();
    $table->addCell(2000)->addText("Licence holder's date of birth", array('bold' => true));
    $table->addCell(2000)->addText($data['license_holder_date_of_birth'], array());

    $table->addRow();
    $table->addCell(2000)->addText("Gender", array('bold' => true));
    $table->addCell(2000)->addText($data['license_holder_gender'], array());
    $table->addRow();
    $table->addCell(2000)->addText('Other relevant licence details', array('bold' => true));
    $table->addCell(2000)->addText('', array());
    $table->addRow();
    $table->addCell(2000)->addText("Translator's additional comments", array('bold' => true));
    $table->addCell(2000);
    $table->addRow();
    $table->addCell(2000)->addText("Translator's name", array('bold' => true));
    $table->addCell(2000);
    $table->addRow();
    $table->addCell(2000)->addText("Translator's signature", array('bold' => true));
    $table->addCell(2000);
    $table->addRow();
    $table->addCell(2000)->addText('Date of translation', array('bold' => true));
    $table->addCell(2000);

    ///add images
    $table->addRow();
    $table->addCell(3000)->addText("Document Image one", array('bold' => true));
    $table->addCell(1000)->addImage($image1Path, array('width' => 150, 'height' => 150));
    $table->addRow();
    $table->addCell(3000)->addText("Document Image two", array('bold' => true));
    $table->addCell(1000)->addImage($image2Path, array('width' => 150, 'height' => 150));




    $footer = $section->addFooter();
    $footer->addText('Office: 3067 Great North Road, New Lynn, Auckland 0600, New Zealand mob: +64 27 241 3656, tel: 0800 27 99 27, e: info@harryclark.co.nz, web: www.HarryClarkTranslation.co.nz', array('color' => 'FF0000'), array('align' => 'center'));

    // Saving the document as OOXML file...
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');


    // Save the document and check if it was created successfully

    $filePath = '../myDocument.docx';

    try {
        $objWriter->save($filePath);
        return true;
    } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
        echo "Error saving the file: " . $e->getMessage();
        die;
    }
}


 
function Sent_Mail_User()
{
    // Instantiate PHPMailer
    $mail = new PHPMailer(true);

    // Set mailer to use SMTP
    $mail->isSMTP(); 
    $mail->SMTPDebug = 0;    
    $mail->Host = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true; 
    $mail->Port = 587;  

    // Set your Gmail username and password
    $mail->Username = 'itzadnantech@gmail.com';
    $mail->Password = 'nyhosiqejbkacdjz'; 
    $mail->setFrom('itzadnantech@gmail.com', 'Adnan Hussain');

     

    $mail->addAddress('cto@agato.net', 'CTO Agato');
    $mail->Subject = 'Quotation Email';
    $mail->Body = 'A new order has arrived.';
    $mail->isHTML(true); 
    $wordDocumentPath = '../myDocument.docx';
    $mail->addAttachment($wordDocumentPath, 'myDocument.docx');
    try {
        $mail->send(); 
        return true;
    } catch (Exception $e) { 
        return false;
    }
}
