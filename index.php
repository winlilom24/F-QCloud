<?php
require "View/BanUI.php";

$banUI = new BanUI();
$banUI->showTable();

echo "<hr>";

require "View/MonAnUI.php";
$monAnUI = new MonAnUI();
$monAnUI->hienThiMonAn();

echo "<hr>";