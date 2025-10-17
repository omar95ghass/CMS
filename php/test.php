<?php
require '../libs/mike42/escpos-php/autoload.php';
use Endroid\QrCode\QrCode;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

$connector = new NetworkPrintConnector('192.168.1.87', 9100);
            $printer = new Printer($connector);

            // QR code generation
            // $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->qrCode("TEST", Printer::QR_ECLEVEL_M, 6);
            
            // Print details
            // $printer->setTextSize(6, 6);
            $printer->text("221354");
            
            $printer->feed(2);
            // $printer->cut();
            $printer->pulse();

            $printer->close();
        
?>