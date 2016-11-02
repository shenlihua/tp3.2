<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller
{
    public function _initialize()
    {

    }

    public function index()
    {
        $this->display();
    }
}