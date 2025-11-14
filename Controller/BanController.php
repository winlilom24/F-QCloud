<?php
require_once "Models/Ban.php";
class BanController{
    private $banModel;

    public function __construct(){
        $this->banModel= new Ban();
    }

    public function getTable(){
        return $this->banModel->getAll();
    }
}