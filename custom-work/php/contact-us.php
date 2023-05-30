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
        if (!Send_an_Email()) {
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

    $table->addRow();
    $c1 = $table->addCell(20000);
    $c1->addText('Approval number (T200101)', array('bold' => true));
    $c1->addText('approved by Waka Kotahi to provide translations for driver licensing purposes', array('italic' => true, 'size' => 8));
    $c2 = $table->addCell(30000);
    $c2->addText('');


    // $table->addRow(); 
    // $table->addCell(3500)->addText('I certify that this is the correct translation of the original document presented to me');
    // $table->addCell(1000)->addText('');
    


    $table->addRow();
    $table->addCell(2000)->addText('Translation reference number', array()); 
    $table->addCell(3000)->addText('000000-0000');


    

    $table->addRow();
    $table->addCell(2000)->addText('Language of document');
    $table->addCell(3000)->addText($quote_data['source_lng']);

    $table->addRow();
    $table->addCell(2000)->addText('Type of document');
    $table->addCell(3000)->addText($quote_data['document']);

    $table->addRow();
    $table->addCell(2000)->addText('License issuing authority and country');
    $table->addCell(3000)->addText('....');

    $table->addRow();
    $table->addCell(2000)->addText('License number');
    $table->addCell(3000)->addText($data['license_number']);

    $table->addRow();
    $table->addCell(2000)->addText('Date license first obtained');
    $table->addCell(3000)->addText($data['date_license_first_obtained']);

    $table->addRow();
    $table->addCell(2000)->addText('Date license card was issued');
    $table->addCell(3000)->addText($data['date_license_card_issued']);


    // Adding new rows to the table...
    $table->addRow();
    $table->addCell(2000)->addText('Date license card is due to expire');
    $table->addCell(3000)->addText($data['date_license_card_due_to_expire']);

    $table->addRow();
    $table->addCell(2000)->addText('License class/es held');
    $table->addCell(3000)->addText('....');

    $table->addRow();
    $table->addCell(2000)->addText('Description of license class/es');
    $table->addCell(3000)->addText('....', array());

    $table->addRow();
    $table->addCell(2000)->addText('Conditions applying to license');
    $table->addCell(3000)->addText($data['conditions_applying_to_license']);

    ////License Holder information

    $table->addRow();
    $table->addCell(2000)->addText("License holder's first name");
    $table->addCell(3000)->addText($data['license_holder_fname']); 

    $table->addRow();
    $table->addCell(2000)->addText("License holder's middle name");
    $table->addCell(3000)->addText($data['license_holder_mname']);

    $table->addRow();
    $table->addCell(2000)->addText("license holder's last name");
    $table->addCell(3000)->addText($data['license_holder_lname']);

    $table->addRow();
    $table->addCell(2000)->addText("License holder's place of birth");
    $table->addCell(3000)->addText($data['license_holder_place_of_birth']);

    $table->addRow(); 
    $table->addCell(2000)->addText("License holder's date of birth");
    $table->addCell(3000)->addText($data['license_holder_date_of_birth']);

    $table->addRow();
    $table->addCell(2000)->addText("Gender");
    $table->addCell(3000)->addText($data['license_holder_gender']);


    $table->addRow();
    $table->addCell(2000)->addText('Other relevant license details');
    $table->addCell(3000)->addText('....',);


    ///office use

    $table->addRow();
    $table->addCell(2000)->addText("Translator's additional comments");
    $table->addCell(3000)->addText('....');


    $table->addRow();
    $table->addCell(2000)->addText("Translator's name");
    $table->addCell(3000)->addText('Hadi Abu Ghazala');
    
    
    $table->addRow();
    $table->addCell(2000)->addText("Translator's signature");
    $table->addCell(3000);

    $table->addRow();
    $table->addCell(2000)->addText('Date of translation');
    $table->addCell(3000)->addText(date('Y-m-d'));

    $section = $phpWord->addSection();

    // add image to section
    $section->addImage('../logo.png', [
        'width' => 450,
        'height' => 100,
        'setAlignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, // optional, default is left-aligned
    ]);




    $footer = $section->addFooter();
    $footer->addText('Office: 3067 Great North Road, New Lynn, Auckland 0600, New Zealand mob: +64 27 241 3656, tel: 0800 27 99 27, e: info@harryclark.co.nz, web: www.HarryClarkTranslation.co.nz', array('color' => 'FF0000'), array('align' => 'center'));

    // Saving the document as OOXML file...
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');


    // Save the document and check if it was created successfully

    $filePath = '../myDocument.docx';

    try {
        $objWriter->save($filePath);
        echo "saved";
        die;
        return true;
    } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
        echo "Error saving the file: " . $e->getMessage();
        die;
        return false;
    }
}



function Send_an_Email()
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
