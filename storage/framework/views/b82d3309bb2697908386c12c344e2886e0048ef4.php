<!DOCTYPE html>
<html>
<head>
    <title>Laravel</title>

    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>QR_pdf</title>




</head>
<body>
<?php echo e(QrCode::format('svg')->size(399)->color(40,40,40)->generate('Make me a QrCode!')); ?>

<?php echo e(QrCode::size(250)->color(150,90,10)->backgroundColor(10,14,244)->generate('Make me a QrCode!')); ?>

<?php echo QrCode::size(250)->color(150,90,10)->backgroundColor(10,14,244)->generate('Make me a QrCode!'); ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\QR-Generator\resources\views/qrPDF.blade.php ENDPATH**/ ?>