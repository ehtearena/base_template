<?php
    $filename ="excelreport.xls";
    header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
?>
@CONTENT@