<?php
require_once '../PHPWord.php';

$PHPWord = new PHPWord();

$document = $PHPWord->loadTemplate('myTest.docx');

$document->setValue('ssd', 'Vasya realno pidor');

$document->save('myFin.docx');
?>