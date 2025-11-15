<?php
require "View/BanUI.php";

$banUI = new BanUI();
$banUI->showTable();

echo "<hr>";

require "View/MonAnUI.php";
$monAnUI = new MonAnUI();
$monAnUI->hienThiMonAn();

echo "<hr>";

require 'View/QuanLyUI.php';
$quanLyUI = new QuanLyUI();
$quanLyUI->hienThiDanhSachNhanVien();

echo "<hr>";

require 'View/OrderUI.php';
$orderUI = new OrderUI();
$orderUI->hienThiChiTiet();

echo "<hr>";

require 'View/LoginUI.php';
$loginUI = new LoginUI();
$loginUI->dangNhap();