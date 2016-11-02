<?php
namespace Admin\Controller;

class SystemController extends CommonController
{

    /*
     * 添加节点页面
     * */
    public function authNodesAdd(){
        $nav=M('nav')->where(array('level'=>1))->getField('id,title');
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     * 获取节点
     * */
    public function getNodes(){
        $nav=M('nav')->where(array('mid'=>I('get.mid')))->getField('id,title');
        $str="<option value=''>请选择</option>";
        foreach($nav as $key=>$vo){
            $str.="<option value='".$key."'>".$vo."</option>";
        }
        echo $str;
    }

    /*
     *
     * 添加节点操作
     * */
    public function authNodesAddAction(){
        $data['title']=I('title');
        $data['url']=I('url');
        $data['sort']=I('sort',100,'int');

        if(I('post.action') && !I('post.operate')){
            $data['level']=3;
            $data['mid']=I('post.action');
            $content="添加三级节点成功";
        }elseif(I('post.operate')){
            $data['level']=4;
            $data['mid']=I('post.operate');
            $content="添加页面节点成功";
        }else{
            $data['level']=2;
            $data['mid']=I('post.module');
            $content="添加二级节点成功";
        }

        if(M('nav')->add($data)){
            $result['code']=1;
            $result['msg']=$content;
        }else{
            $result['code']=0;
            $result['msg']="操作失败";
        }
        $this->ajaxReturn($result);
    }
}