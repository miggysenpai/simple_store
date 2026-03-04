<?php 
require_once 'users/init.php';
ob_start();
if(isAdmin()){
//do nothing
}else{
//redirect to home if not an admin 
header("Location: index.php");
die();
}

//required stripe file
require_once 'usersc/plugins/simple_store/assets/stripe/vendor/autoload.php';

//required fpdf file
require "usersc/plugins/simple_store/assets/fpdf/fpdf.php";

//create pdf object with fpdf. size is 4inx6in printing label. 
$pdf = new FPDF('P','mm', array(100,150));

    
//add new fpdf page
$pdf->AddPage();
$pdf->AddFont('OneLittleFont','','OneLittleFont.php');


//set font, regular, 14pt
$pdf->SetFont('OneLittleFont','',14);

//Cell(width , height , text , border , end line , [align] )
$pdf->Cell(80 ,5,$_POST['site_name'],0,1,'C');

//set font, regular, 6pt
$pdf->SetFont('OneLittleFont','',6);

//Cell(width , height , text , border , end line , [align] )
$pdf->Cell(80 ,5,$_POST["contact_email"],0,1, 'C');
$pdf->Cell(59 ,5,'',0,1);

//Cell(width , height , text , border , end line , [align] )
$pdf->Cell(40,5,'Order No. :',0,0);
$pdf->Cell(40 ,5,$_POST["order_number"],0,1,'R');
$pdf->Cell(40 ,5,'Date :',0,0);
$pdf->Cell(40 ,5,$_POST["order_date"],0,1, 'R');


//make a dummy empty cell as a vertical spacer
$pdf->Cell(80 ,5,'',0,1);

//Customer Info
$pdf->Cell(80 ,5,'Customer information',0,1);
$pdf->Cell(32 ,5,'Customer Name',0,0);
$pdf->Cell(48 ,5, $_POST["customer_name"], 0, 1);
$pdf->Cell(32 ,5,'Customer Email',0,0);
$pdf->Cell(48 ,5,$_POST["customer_email"],0 , 1);
$pdf->Cell(32 ,5,'Customer Address',0 ,0 );
$pdf->Cell(48 ,5,$_POST["customer_address_1"],0 ,1);
$pdf->Cell(32 ,5,'',0 ,0 );
$pdf->Cell(48 ,5,$_POST["customer_address_2"],0 ,1);

//make a dummy empty cell as a vertical spacer
$pdf->Cell(80 ,5 ,'',0,1);//end of line


$pdf->Cell(65 ,5,'Product',1,0);
$pdf->Cell(15 ,5,'Quanity',1,1);

//simple loop to show all purchased products
foreach ($_POST["product"] as $prod) {
    $pdf->Cell(65 ,5,$prod["name"],1,0);
    $pdf->Cell(15 ,5,$prod["quanity"],1,1);
}



//summary
$pdf->Cell(130 ,5,'',0,0);
$pdf->Cell(25 ,5,'Subtotal',0,0);
$pdf->Cell(4 ,5,'$',1,0);
$pdf->Cell(30 ,5,'4,450',1,1,'R');//end of line

$pdf->Cell(130 ,5,'',0,0);
$pdf->Cell(25 ,5,'Taxable',0,0);
$pdf->Cell(4 ,5,'$',1,0);
$pdf->Cell(30 ,5,'0',1,1,'R');//end of line

$pdf->Cell(130 ,5,'',0,0);
$pdf->Cell(25 ,5,'Tax Rate',0,0);
$pdf->Cell(4 ,5,'$',1,0);
$pdf->Cell(30 ,5,'10%',1,1,'R');//end of line

$pdf->Cell(130 ,5,'',0,0);
$pdf->Cell(25 ,5,'Total Due',0,0);
$pdf->Cell(4 ,5,'$',1,0);
$pdf->Cell(30 ,5,'4,450',1,1,'R');//end of line



//output the result
$pdf->Output();

?>