<?php
require_once '../PHPWord.php';

$PHPWord = new PHPWord();

$document = $PHPWord->loadTemplate('Template.docx');

$document->setValue('Value1', 'Русские буквы');
$document->setValue('Value2', 'Mercury');
$document->setValue('myReplacedValue', 'Venus 9.99$');
$document->setValue('ssd', 'Earth');
// $document->setValue('Value5', 'Mars');
// $document->setValue('Value6', 'Jupiter');
// $document->setValue('Value7', 'Saturn');
// $document->setValue('Value8', 'Uranus');
// $document->setValue('Value9', 'Neptun');
// $document->setValue('Value10', 'Pluto');

$document->setValue('weekday', date('l'));
$document->setValue('timie', date('H:i'));

//$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$document->save('Solarsystem.docx');
?>