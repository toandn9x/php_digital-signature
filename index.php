<?php
public function testhihiAction()
    {
        // Include the main TCPDF library (search for installation path).
        include APP_PATH . '/app/vendor/tcpdf/tcpdf.php';
        $p12File = 'C:\Users\Admin\Desktop\024700233_214454.p12';
        $password = '214454';
        // Trích xuất khóa và chứng chỉ từ file .p12
        $keys = extractKeysFromP12($p12File, $password);

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example 052');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 052', PDF_HEADER_STRING);

// set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

// ---------------------------------------------------------

        /*
        NOTES:
         - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
         - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
         - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
        */

// set certificate file

// set additional information
        $info = array(
            'Name' => iconv('UTF-8', 'UTF-8//IGNORE', 'Nguyễn Văn A'),
            'Location' => iconv('UTF-8', 'UTF-8//IGNORE', 'Hà Nội, Việt Nam'),
            'Reason' => iconv('UTF-8', 'UTF-8//IGNORE', 'Ký duyệt tài liệu'),
            'ContactInfo' => 'email@example.com'
        );

// set document signature
        $pdf->setSignature($keys['cert'], $keys['pkey'], $password, '', 2, $info);

// set font
        $pdf->SetFont('dejavusans', '', 12);

// add a page
        $pdf->AddPage();

// print a line of text
        $text = 'This is a <b color="#FF0000">digitally signed document</b> using the default (example) <b>tcpdf.crt</b> certificate.<br />To validate this signature you have to load the <b color="#006600">tcpdf.fdf</b> on the Arobat Reader to add the certificate to <i>List of Trusted Identities</i>.<br /><br />For more information check the source code of this example and the source code documentation for the <i>setSignature()</i> method.<br /><br /><a href="http://www.tcpdf.org">www.tcpdf.org</a>';
        $pdf->writeHTML($text, true, 0, true, 0);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// *** set signature appearance ***

// create content for signature (image and/or text)
        $pdf->Image('images/tcpdf_signature.png', 180, 60, 60, 20, 'PNG');

// define active area for signature appearance
        $pdf->setSignatureAppearance(150, 240, 60, 20);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// *** set an empty signature appearance ***
//        $pdf->addEmptySignatureAppearance(180, 80, 15, 15);

// ---------------------------------------------------------
        // Vẽ khung chữ ký trên PDF
        $signatureX = 150;  // Tọa độ X của khung chữ ký
        $signatureY = 240;  // Tọa độ Y của khung chữ ký
        $signatureWidth = 60;  // Chiều rộng khung chữ ký
        $signatureHeight = 20; // Chiều cao khung chữ ký

        // Vẽ khung chữ ký
        $pdf->SetDrawColor(255, 0, 0); // Đặt màu viền là màu đỏ (RGB: 255, 0, 0)
        $pdf->Rect($signatureX, $signatureY, $signatureWidth, $signatureHeight);
        $pdf->SetXY($signatureX + 2, $signatureY + 2);

        // Hiển thị thông tin trong khung chữ ký
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(
            $signatureWidth - 4,  // Bề rộng nội dung
            5,                   // Chiều cao mỗi dòng
            "Người ký: {$info['Name']}\nNgày ký: " . date('d/m/Y'),  // Nội dung
            0,                   // Không có viền
            'L',                 // Căn trái
            false                // Không làm nền
        );

//Close and output PDF document
        ob_end_clean();
        $pdf->Output('example_052.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+
    }

    function extractKeysFromP12($p12File, $password)
    {
        $certs = [];
        if (!openssl_pkcs12_read(file_get_contents($p12File), $certs, $password)) {
            throw new Exception('Không thể đọc file .p12. Vui lòng kiểm tra mật khẩu hoặc file.');
        }
        return $certs; // ['cert' => chứng chỉ số, 'pkey' => khóa riêng]
    }