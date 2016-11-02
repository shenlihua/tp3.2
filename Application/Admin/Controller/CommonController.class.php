<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller
{
    public function _initialize()
    {
        $action=strtolower(CONTROLLER_NAME.'-'.ACTION_NAME);

        //无需验证的操作
        $no_operate='index-login';


        if(strpos($no_operate, $action)===false){
            if(!session('nodes')) {
                $nodes = self::getNodes();
                session('nodes', $nodes);
            }
        }
//        dump($this->getNodes());exit;
    }

    //首页
    public function index()
    {
        $action=strtoupper(CONTROLLER_NAME.'/'.ACTION_NAME);

        $nodes=I('session.nodes');
        //左侧导航
        $left_nav=$nodes[$action]['child'];

        $child=$nodes[$action]['child'];
        $first=array_shift($child);
        $url=array_shift($first['child']);

        $this->assign('left_nav', $left_nav);
        $this->assign('url', $url['url']);
        $this->display('Common/index');
    }


    //验证码
    public function verify()
    {
        $verify = new \Think\Verify();
        $verify->length=4;
        $verify->codeSet='0123456789';
        $verify->imageH='45';
        $verify->entry();
    }

    /*
 * 数据列表
 * */
    protected function lists($db, $where=array(), $field='*', $order='')
    {
//        $db=M('goods');
        $count=$db->where($where)->count();
        $page=new \Think\Page($count,10);
        $show=$page->show();
        $list=$db->field($field)->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();
    }

    /*
    * @param   $db             //数据库实例对象
     * * @param   $data           //编辑或修改数据
     * * @param   $url            //转跳页面
     * * @param   $time           //等待时间
     * * @param   $editKey        //按主键修改
     * * 共用添加和编辑操作     *
     * */
    public function addEditOperate($db,$data,$url,$time=3,$editKey=''){
        $data=$db->create($data);
//        dump($data);exit;
        if(!$data){
            $info['code']=-1;
            $info['msg']=$db->getError();
        }else{
            $key=$editKey?$editKey:$db->getPk();
            if($data[$key]){
                if($db->save($data)){
                    $info['code']=0;
                    $info['msg']='修改成功!';
                }else{
                    $info['code']=1;
                    $info['msg']='修改失败!';
                }
            }elseif($db->add($data)){
                $info['code']=0;
                $info['msg']='添加成功!';
            }else{
                $info['code']=1;
                $info['msg']='添加失败!';
            }
        }
        $this->ajaxReturn($info);
    }
    /*
    * @param       $db     //数据库对象
     * * @param       $data   //保存数据
     * * @param       $del    //删除数据 1删除 0保存
     * * */
    public function operateDataBase($db,$data,$del=0){
        $key=$db->getPk();//获取表主键
        if($data[$key]){
            switch($del) {
                case 1:
                    $rs=$db->where($data)->setField('status',0);
                    break;
                default:
                    $rs=$db->save($data);
                    break;
            }
            if ($rs) {
                $result['code'] = 0;
                $result['msg'] = "操作成功!";
            } else {
                $result['code'] = 1;
                $result['msg'] = "操作失败!";
            }
        }else{
            $result['code']=2;
            $result['msg']="请求出错!";
        }
        $this->ajaxReturn($result);
    }

    /*
     * 数据状态切换
     * */
    protected function dataStatusSwitch($db,$data)
    {
        $key=$db->getPk();//获取表主键
        $trueTableName=$db->getTableName();
        if($data[$key]){
            if($db->execute('update '.$trueTableName.' set `status`=if(`status`=1,2,1) where '.$key .' = '.$data[$key])){
                $result['code'] = 0;
                $result['msg'] = "操作成功!";
            }else{
                $result['code'] = 1;
                $result['msg'] = "操作失败!";
            }
        }else{
            $result['code']=0;
            $result['msg']="请求出错!";
        }
        $this->ajaxReturn($result);
    }
    /*
    * 2016年8月8日 18:23:57     * 获取所有节点
     * * */
    protected function getNodes($where=array()){
        $nav=M('nav')->field('id,title,url,level,mid')->where($where)->where('status=1')->order('level asc,mid asc,sort asc')->select();
        foreach($nav as $vo){
            $vo['level']==1 && $one[]=$vo;
            $vo['level']==2 && $two[]=$vo;
            $vo['level']==3 && $three[]=$vo;
        }        foreach($one as $st){
            $menu[strtoupper($st['url'])]=$st;
        }        foreach($menu as &$no){
            foreach($two as $k=>&$tw){
                foreach($three as $key=>$th){
                    if($th['mid']==$tw['id']){
                        $tw['child'][$th['id']]=$th;
                        unset($three[$key]);

                    }
                }
                if($tw['mid']==$no['id']){
                    $no['child'][$tw['id']]=$tw;
                    unset($two[$k]);
                }
            }
        }
        return $menu;
    }
}