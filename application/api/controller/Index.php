<?php
namespace app\api\controller;

use app\common\controller\Base;
use think\Db;

class Index extends Base
{
    // 继承父级的控制器
    public function __construct()
    {
        parent::__construct();
    }
    // 查询部门所有数据
    public function deplist()
    {
        $action=request()->post('action');
		if($action=='person'){
			$deplist=Db::name('department')->select();
			$data=[
			'id'=>0,
			'name'=>'全部'
			];
			$deplist[]=$data;
		sort($deplist);
		if($deplist){
            $this->finish('查询成功',$deplist);
        }else {
            $this->warning('暂时数据');
        }
        // 这个查询数据没有条件 那么直接查询所有的数据 注意一个点 前端要的字段名 跟我们返回的数据里面的字段名是否一致 如果不是的就改
		}else{
		$deplist = Db::name('department')->field("id as value,name as label")->select();
        if($deplist){
            $this->finish('查询成功',$deplist);
        }else {
            $this->warning('暂时数据');
        }
		}
		

		
    }
    // 查询职位所有数据
    public function joblist()
    {
        // 前端的传过来的参数
        $id = request()->post('id');
        /* where 第一个参数是表字段 第二个参数才是传过来的参数 */
        $joblist = Db::name('position')->where('id',$id)->field('id as value,name as label')->select();
        if($joblist){
            $this->finish('查询成功',$joblist);
        }else{
            $this->warning('暂无数据');
        }
    }
	public function add()
    {
        $data = request()->post();
        
        // 性别的字符串转化相应的数字
        switch ($data['sex']) {
            case '男':
                $data['sex'] = 1;
                break;
            case '女':
                $data['sex'] = 2;
                break;
            default:
                $data['sex'] = 0;
                break;
        }
        // 通过部门的名称去拿到这个部门id
        $depdata = Db::name('department')->where('name',$data['depname'])->find();
       
        // 通过职位的名称去拿到这个职位id
        $jobdata = Db::name('position')->where('name',$data['jobname'])->find();
       
        // 封装一个插入数据
        $person = [
            'name' => $data['name'],
            'sex' => $data['sex'],
            'mobile' => $data['mobile'],
            'depid' => $depdata['id'],
            'jobid' => $jobdata['id']
        ];
        // 把数据存入数据库
        $result = Db::name('staff')->insert($person);
        if($result){
            $this->finish('新建成功');
        }else{
            $this->warning('新建失败，请重新新建');
        }

    }
	
	public function search()
    {
		$name = request()->post('name');
		$where = [
                'name' => $name
            ];
		$PerList = Db::name('staff')->where('name|mobile', 'like',"%$name%")->select();
		// halt($PerList);
		        if($PerList){
            $this->finish('查询成功',$PerList);
        }else {
            $this->warning('暂无数据');
        }
	}
	public function PerList()
    {
        $index = request()->post('index');
        // halt($index);
        $deplist = Db::name('department')->select();
        $data = [
            'id' => 0,
            'name' => '全部'
        ];
        $deplist[] = $data;
        sort($deplist);
        // halt($deplist);

        //  where depid = 0 -> where 1
        $depid = '';
        foreach($deplist as $key => $v){
            if($index == $key){
                $depid = $v['id'];
				
            }
        }
        // halt($depid);
        $where = [];
        if($depid == 0){
            $where = [];
        }else{
            $where = [
                'depid' => $depid
            ];
        }
        $PerList = Db::name('staff')->where($where)->select();
        foreach($PerList as &$v){
            $v['show'] = false;
        }
        // halt($PerList);
        if($PerList){
            $this->finish('查询成功',$PerList);
        }else {
            $this->warning('暂无数据');
        }
    }
	public function getprofile()
    {
        $id = request()->post('id');
        if(!$id){
            $this->warning('选择的无效信息，请重新选择');
        }

        $profile = Db::name('staff')
                    ->alias('p')
                    ->join('department d','p.depid = d.id')
                    ->join('position j','p.jobid = j.id')
                    ->field('p.*,d.name as depname,j.name as jobname')
                    ->where('p.id',$id)
                    ->find();
        
        if($profile){
            switch ($profile['sex']) {
                case '1':
                    $profile['sex'] = '男';
                    break;
                
                case '2':
                    $profile['sex'] = '女';
                    break;
                default:
                    $profile['sex'] = '保密';
                    break;
            }
            $this->finish('查询成功',$profile);
        }else {
            $this->warning('查询不到数据，请重新选择');
        }
    }
	public function del()
    {
		$id=request()->post('id');
		if(!$id){
			$this->warning('删除失败');
		}
		$res=Db::name('staff')->where('id',$id)->delete();
		if($res){
			$this->finish('删除成功');
			
		}else{
			$this->warning('删除失败');
		}
	}
	public function call()
    {
        $id = request()->post('id');

        if(!$id){
            $this->warning('选择的无效信息，请重新选择');
        }

        $result = Db::name('staff')->where('id',$id)->field('name,mobile')->find();

        // halt($result);
        if($result){
            $this->finish('查询成功',$result);
        }else {
            $this->warning('所选的用户不存在，请重新选择');
        }
    }
	public function edit()
    {
      $data = request()->post();
	  $id = $data['id'];
	  $name = $data['name'];
      $sex = $data['sex'];
	  switch ($data['sex']) {
                case '男':
                    $sex = 1;
                    break;
                
                case '女':
                    $sex = 2;
                    break;
                default:
                    $sex = 0;
                    break;
            }
      $mobile = $data['mobile'];
      $depid =Db::name('department')->where('name',$data['depname'])->field('id')->find()['id'] ;
	  $jobid =Db::name('position')->where('name',$data['jobname'])->field('id')->find()['id'];
	  //halt($depid,$jobid );
      // $salt = $this->AdminModel
      //   ->field('salt')->where('id', $id)
      //   ->find()['salt'];
      //$salt = Menus::get_str(4);
      // $salt=get_str(4);
      $arr = [
        'sex' => $sex,
        'name' => $name,
        'sex' => $sex,
        'mobile' => $mobile,
		'depid' =>$depid ,
		'jobid' =>$jobid
      ];
    
        $result = Db::name('staff')
          ->where('id', $id)
          ->update($arr);
		 if($result){
            $this->finish('修改成功',$result);
        }else {
            $this->warning('失败'.$result);
        }

      
	}
}
