<?php

function Create_Word_Document($data)
{
    // Create a new Word document
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    /* Note: any element you append to a document must reside inside of a Section. */
    // Adding an empty Section to the document...
    $section = $phpWord->addSection();
    // add image to section
    $section->addImage(ABSPATH . 'custom-work\logo.png', [
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

    //$table = $section->addTable('My Document', array('size' => 14, 'bold' => true), array('align' => 'right'));
    $table->addRow();
    $cell = $table->addCell(10000);
    $cell->addText('Approval number (T200101)', array('bold' => true));
    $cell->addText('approved by Waka Kotahi to provide translations for driver licensing purposes', array());
    $table->addCell(5000)->addText('');
    $table->addRow();
    $table->addCell(1000)->addText('License number', array('bold' => true));
    $table->addCell(5000)->addText($data['license_number']);



    $footer = $section->addFooter();
    $footer->addText('Office: 3067 Great North Road, New Lynn, Auckland 0600, New Zealand mob: +64 27 241 3656, tel: 0800 27 99 27, e: info@harryclark.co.nz, web: www.HarryClarkTranslation.co.nz', array('color' => 'FF0000'), array('align' => 'center'));

    // Saving the document as OOXML file...
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');


    // Save the document and check if it was created successfully
    $filePath = ABSPATH . 'custom-work/myDocument.docx';
    $isSaved = false;
    $isSaved = $objWriter->save($filePath);

    
    return $isSaved;
}