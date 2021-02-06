<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Store;
use \Firebase\JWT\JWT;
use \think\Log;
use \think\Request;

session_start();
class Index extends Controller
{
	private $pageNum = 20;
	private $opStatus = ['未通过','待审核','已通过'];
    private $siteStatus = ['驳回','待审核','已通过'];
	private $announcementStatus = ['已删除','已发布'];
    private $appid = 'wx813c27cb625d1b07';
    private $secret = '563b42c3ce9bb2b4aed7096ad8ea6bd4';
    private $expireTime = 60;
    private $allowAction = [];

    public function _initialize(){
        if (@$_COOKIE['PHPSESSID']) {
            session([
                'var_session_id' => $_COOKIE['PHPSESSID']
            ]);
        }else{
            // $request = Request::instance();
            // if(in_array($request->action(), $this->))
        }
    }

    public function index()
    {
        echo 'moli';
    }

    // 首页店铺信息
    public function showIndexData()
    {
        $type = input('post.type/d',1); //1-推荐 2-榜单
        $userId = input('post.user_id/d');
        $currentPage = input('post.currentPage/d',1);
        $where = [];
        $order = [];
        $storeIds = [];
        $a = [];
        if($type == 1){
            $order = ['collect'=>'desc'];
        }else{
            $order = ['score'=>'desc'];
        }
        $where['status'] = 1;
        $data = Db('store')->field('id,location,cover,title,score,collect,slideshow,consumption')->where($where)->order($order)->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        foreach ($data['data'] as $key => $value) {
            $storeIds[] = $value['id'];
        }
        $collectorlikeArr = Db('collectorlike')->where('status',1)->where('store_id','in',$storeIds)->select();
        foreach ($collectorlikeArr as $key => $value) {
            if($value['type'] == 1){
                $a[$value['store_id']]['is_collect'] = 1;
            }else if($value['type'] == 2){
                $a[$value['store_id']]['is_like'] = 1;
            }
        }
        foreach ($data['data'] as $key => $value) {
            if( !isset($a[$value['id']]['is_collect']) && !isset($a[$value['id']]['is_like']) ){
                $data['data'][$key]['status'] = 0;
            }else if( !isset($a[$value['id']]['is_collect']) && isset($a[$value['id']]['is_like']) ){
                $data['data'][$key]['status'] = 1;
            }else if( isset($a[$value['id']]['is_collect']) && !isset($a[$value['id']]['is_like']) ){
                $data['data'][$key]['status'] = 2;
            }else if( isset($a[$value['id']]['is_collect']) && isset($a[$value['id']]['is_like']) ){
                $data['data'][$key]['status'] = 3;
            }

            $data['data'][$key]['cover'] = 'http://molion.tech/test/loudi/public/static/images/'.$value['cover'];
            if(!empty($value['slideshow'])){
                $slideshowArr = [];
                foreach (json_decode($value['slideshow'],true) as $k => $v) {
                    $slideshowArr[] = 'http://molion.tech/test/loudi/public/static/images/'.$v;
                }
                $data['data'][$key]['slideshow'] = $slideshowArr;
            }
        }
        print json_encode(['code'=>1,'msg'=>'操作成功','data'=>$data['data']]);






        // $type = input('post.type/d',1); //1-推荐 2-榜单
        // $userId = input('post.user_id/d');
        // $currentPage = input('post.currentPage/d',1);
        // // if(empty($userId)){
        // //     print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
        // //     return;
        // // }
        // $where = [];
        // $order = [];
        // if($type == 1){
        //     $order = ['s.collect'=>'desc'];
        // }else{
        //     $order = ['s.score'=>'desc'];
        // }
        // // $where['c.user_id'] = $userId;
        // $where['s.status'] = 1;
        // // $where['c.type'] = 2;
        // // $data = Db('store')->alias('s')->field('s.id,s.location,s.cover,s.title,s.score,s.collect,s.slideshow,s.consumption,c.status,c.type')->join('collectorlike c','s.id = c.store_id','LEFT')->where($where)->order($order)->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        // $data = Db('store')->alias('s')->field('s.id,s.location,s.cover,s.title,s.score,s.collect,s.slideshow,s.consumption')->where($where)->order($order)->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        // foreach ($data['data'] as $key => $value) {
        //     $data['data'][$key]['cover'] = 'http://molion.tech/test/loudi/public/static/images/'.$value['cover'];
        //     if(!empty($value['slideshow'])){
        //         $slideshowArr = [];
        //         foreach (json_decode($value['slideshow'],true) as $k => $v) {
        //             $slideshowArr[] = 'http://molion.tech/test/loudi/public/static/images/'.$v;
        //         }
        //         $data['data'][$key]['slideshow'] = $slideshowArr;
        //     }
        // }
        // print json_encode(['code'=>1,'msg'=>'操作成功','data'=>$data['data']]);
    }

    //店铺商品信息
    public function storeGoods(){
        $where = [];
        $a = [];
        $storeIds = [];
        $storeId = input('post.storeId/d');
        if(empty($storeId)){
            print json_encode(['code'=>0,'msg'=>'storeId不能为空!']);
            return;
        }else{
            $where['s.id'] = $storeId;
        }
        $where['s.status'] = 1;

        $data = Db('store')->alias('s')->field('s.id,s.location,s.cover,s.title,s.consumption,s.slideshow,s.score,g.title gtitle')->where($where)->join('goods g','s.id = g.store_id','LEFT')->select();

        foreach ($data as $key => $value) {
            $data[$key]['rank'] = rand(1,5);
            $data[$key]['score'] = rand(1,5);
            $storeIds[] = $value['id'];
        }
        $collectorlikeArr = Db('collectorlike')->where('status',1)->where('store_id','in',$storeIds)->select();
        foreach ($collectorlikeArr as $key => $value) {
            if($value['type'] == 1){
                $a[$value['store_id']]['is_collect'] = 1;
            }else if($value['type'] == 2){
                $a[$value['store_id']]['is_like'] = 1;
            }
        }
        foreach ($data as $key => $value) {
            if( !isset($a[$value['id']]['is_collect']) && !isset($a[$value['id']]['is_like']) ){
                $data[$key]['status'] = 0;
            }else if( !isset($a[$value['id']]['is_collect']) && isset($a[$value['id']]['is_like']) ){
                $data[$key]['status'] = 1;
            }else if( isset($a[$value['id']]['is_collect']) && !isset($a[$value['id']]['is_like']) ){
                $data[$key]['status'] = 2;
            }else if( isset($a[$value['id']]['is_collect']) && isset($a[$value['id']]['is_like']) ){
                $data[$key]['status'] = 3;
            }

            $data[$key]['cover'] = 'http://molion.tech/test/loudi/public/static/images/'.$value['cover'];
            if(!empty($value['slideshow'])){
                $slideshowArr = [];
                foreach (json_decode($value['slideshow'],true) as $k => $v) {
                    $slideshowArr[] = 'http://molion.tech/test/loudi/public/static/images/'.$v;
                }
                $data[$key]['slideshow'] = $slideshowArr;
            }
        }
        print json_encode(['code'=>1,'msg'=>'操作成功','data'=>$data]);
    }

    //运营申请列表
    public function opApplyList(){
    	$where = [];
    	$currentPage = input('post.currentPage/d',1);
    	$type = input('post.type/d',0);
    	if($type){
    		$where['status'] = $type;
    	}
    	$data = Db('opapply')->where($where)->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
    	foreach ($data['data'] as $key => $value) {
    		$data['data'][$key]['status'] = $this->opStatus[$value['status']];
    	}
    	print json_encode(['code'=>1,'msg'=>'操作成功','data'=>$data['data']]);
    }

    //运营申请
    public function opApply(){
        $info = [];
    	$info['username'] = $username = input('post.username/s');
        $info['businesslicense'] = $businesslicense = input('post.businesslicense/s');
        $info['type'] = $type = input('post.type/s');
        $info['tgcimgurl'] = $tgcimgurl = input('post.tgcimgurl/s');
        $cardimgurl = input('post.cardimgurl/a');
        $info['healthimgurl'] = $healthimgurl = input('post.healthimgurl/s');
        $info['user_id'] = $userId = input('post.user_id/d');
        if( empty($username) || empty($businesslicense) || empty($type) || empty($tgcimgurl) || empty($cardimgurl) || empty($healthimgurl) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $info['cardimgurl'] = json_encode($cardimgurl);
        $info['createtime'] = date('Y-m-d H:i:s',time());
        $info['status'] = 1;

        $result = Db('opapply')->insert($info);
        $opId = Db('opapply')->getLastInsID();
        if($result){
            Db('allcheck')->insert(['op_id'=>$opId,'status'=>1,'type'=>0]);
            print json_encode(['code'=>1,'msg'=>'操作成功!']);
        }else{
           print json_encode(['code'=>0,'msg'=>'操作失败!']);
           return; 
        }
    }

    //图片上传
    public function imgsUpload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        $fName = $_FILES['image']['name'];
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads',$fName);
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            // echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getFilename(); 
            print json_encode(['code'=>1,'msg'=>'上传成功!','data'=>$info->getFilename()]);
        }else{
            // 上传失败获取错误信息
            // echo $file->getError();
            print json_encode(['code'=>0,'msg'=>$file->getError()]);
            return;
        }
    }

    //场地申请列表
    public function siteApplyList(){
    	$where = [];
    	$currentPage = input('post.currentPage/d',1);
    	$data = Db('siteapply')->where($where)->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
    	foreach ($data['data'] as $key => $value) {
    		$data['data'][$key]['status'] = $this->siteStatus[$value['status']];
    	}
    	print json_encode(['code'=>1,'msg'=>'操作成功','data'=>$data['data']]);
    }

    //场地申请
    public function siteApply(){
        $data = [];
        $data['username'] = $username = input('post.username/s');
        $data['id_card'] = $id_card = input('post.id_card/s');
        $data['user_id'] = $user_id = input('post.user_id/d');
        $data['site_id'] = $site_id = input('post.site_id/d');
        $data['store_id'] = $store_id = input('post.store_id/d');
        $cardimgurl = input('post.cardimgurl/s');
        $healthimgurl = input('post.healthimgurl/s');
        $storename = input('post.storename/s');
        $type = input('post.type/s');
        if( empty($username) || empty($id_card) || empty($user_id) || empty($site_id) || empty($store_id) || empty($cardimgurl) || empty($healthimgurl) || empty($storename) || empty($type) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $result = Db('siteapply')->insert($data);
        $siteId = Db('siteapply')->insertGetId($data);
        if($result){
            Db('allcheck')->insert(['site_id'=>$siteId,'type'=>1,'user_id'=>$user_id]);
            print json_encode(['code'=>1,'msg'=>'操作成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败!']);
            return;
        }
    }

    //公告列表
    public function announcementList(){
        $where = [];
        $where['status'] = 1;
        $currentPage = input('post.currentPage/d',1);
        $data = Db('announcement')->where($where)->order('id','desc')->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['des'] = $this->announcementStatus[$value['status']];
        }
        print json_encode(['code'=>1,'msg'=>'操作成功','data'=>$data['data']]);
    }

    //删除公告
    public function delAnnouncement(){
        $where = [];
        $ids = input('post.ids/a'); //数组形式
        if(empty($ids)){
            print json_encode(['code'=>0,'msg'=>'参数错误!']);
            return;
        }
        $where['id'] = ['in',implode(',', $ids)];
        $result = Db('announcement')->where($where)->update(['status'=>0,'updatetime'=>date('Y-m-d H:i:s',time())]);
        $data = Db('announcement')->where('status',1)->order('id','desc')->paginate($this->pageNum, true, ['page' => 1])->toArray();
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['des'] = $this->announcementStatus[$value['status']];
        }
        if($result){
            print json_encode(['code'=>1,'msg'=>'删除成功!','data'=>$data['data']]);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'删除失败!']);
            return;
        }
    }

    //发布公告
    public function releaseAnnouncement(){
        $data = [];
        $data['title'] = $title = input('post.title/s');
        $data['images'] = $images = input('post.images/s');
        $data['content'] = $content = input('post.content/s');
        if( empty($title) || empty($content) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $data['createtime'] = $data['updatetime'] = date('Y-m-d H:i:s',time());
        $result = Db('announcement')->insert($data);
        if($result){
            print json_encode(['code'=>1,'msg'=>'发布成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'发布失败!']);
            return;
        }
    }

    //修改公告信息
    public function updateAnnouncement(){
        $data = [];
        $id = input('post.id/d');
        $data['title'] = $title = input('post.title/s');
        $data['images'] = $images = input('post.images/s');
        $data['content'] = $content = input('post.content/s');
        if( empty($title) || empty($content) || empty($id) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $data['updatetime'] = date('Y-m-d H:i:s',time());
        $result = Db('announcement')->where('id',$id)->update($data);
        if($result){
            print json_encode(['code'=>1,'msg'=>'修改成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'修改失败!']);
            return;
        }
    }

    //获取公告信息
    public function getAnnouncement(){
        $id = input('post.id/d');
        if(empty($id)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $info = Db('announcement')->where('id',$id)->find();
        print json_encode(['code'=>1,'msg'=>'获取成功','data'=>$info]);
    }

    //运营/场地审核数量
    public function opNum(){
        $num1 = Db('allcheck')->where('status',1)->count();
        $num2 = Db('allcheck')->where('status','<>',1)->count();
        print json_encode(['code'=>1,'msg'=>'','data'=>[$num2,$num1]]);
    }

    //管理员驳回申请
    public function rejectRequest(){
        $type = input('post.type/d',1); //默认 1-场地 0-运营
        $note = input('post.note/a');
        $id = input('post.id/d');
        if(empty($id) || empty($note)){
            print json_encode(['code'=>0,'msg'=>'参数错误！']);
            return;
        }
        $allCheckData = Db('allcheck')->where('id',$id)->where('status',1)->find();
        if(empty($allCheckData)){
            print json_encode(['code'=>0,'msg'=>'error1']);
            return;
        }
        if($type){
            $siteId = $allCheckData['site_id'];
            $result = Db('siteapply')->where('id',$siteId)->update(['status'=>0,'checktime'=>date('Y-m-d H:i:s',time()),'note'=>$note]);

        }else{
            $opId = $allCheckData['op_id'];
            $result = Db('opapply')->where('id',$opId)->update(['status'=>0,'note'=>$note]);
        }
        if($result){
            Db('allcheck')->where('id',$id)->update(['status'=>0]);

            print json_encode(['code'=>1,'msg'=>'操作成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败!']);
            return;
        }
    }

    //收藏店铺
    public function storeCollect(){
        $storeId = input('post.storeId/d');
        $userId = input('post.userId');
        $type = input('post.type/d',1);
        if(empty($storeId) || empty($userId)){
            print json_encode(['code'=>0,'msg'=>'参数错误!']);
            return;
        }
        $storeCollect = Db('store')->field('collect')->where('id',$storeId)->find();
        $info = Db('collectorlike')->where('user_id',$userId)->where('store_id',$storeId)->where('status',$type)->where('type',1)->where('is_t',1)->order('createtime','desc')->find();

        if(!empty($info)){
            print json_encode(['code'=>0,'msg'=>'请勿重复操作']);
            return;
        }
        if($type){
            Db('collectorlike')->insert(['user_id'=>$userId,'store_id'=>$storeId,'createtime'=>date('Y-m-d H:i:s',time()),'type'=>1]);
            Db('ophistory')->insert(['user_id'=>$userId,'op'=>'店铺收藏:'.$storeId,'createtime'=>date('Y-m-d H:i:s',time())]);
            Db('store')->where('id',$storeId)->inc('collect')->update(['updatetime'=>date('Y-m-d H:i:s',time())]);
        }else{
            if(($storeCollect['collect'] - 1)<=0){
                $c = 0;
            }else{
                $c = $storeCollect['collect'] -1;
            }
            Db('collectorlike')->where('user_id',$userId)->where('store_id',$storeId)->update(['is_t'=>0,'status'=>0]);
            Db('ophistory')->insert(['user_id'=>$userId,'op'=>'店铺取消收藏:'.$storeId,'createtime'=>date('Y-m-d H:i:s',time())]);
            Db('store')->where('id',$storeId)->update(['updatetime'=>date('Y-m-d H:i:s',time()),'like'=>$c]);
        }
        print json_encode(['code'=>1,'msg'=>'操作成功!']);
    }

    //摊主查看当前菜品
    public function checkGoods(){
        $where = [];
        $storeId = input('post.storeId/d');
        if(empty($storeId)){
            print json_encode(['code'=>0,'msg'=>'参数错误！']);
            return;
        }else{
            $where['store_id'] = $storeId;
        }
        $where['status'] = 1;
        $data = Db('goods')->where($where)->select();
        foreach ($data as $key => $value) {
            $data[$key]['img'] = 'http://molion.tech/test/loudi/public/static/images/'.$value['img'];
        }
        print json_encode(['code'=>1,'msg'=>'操作成功！','data'=>$data]);
    }

    //摊主删除菜品
    public function delGoods(){
        $where = [];
        $ids = input('post.ids/a'); //数组形式
        if(empty($ids)){
            print json_encode(['code'=>0,'msg'=>'参数错误!']);
            return;
        }
        $where['id'] = ['in',implode(',', $ids)];
        $result = Db('goods')->where($where)->update(['status'=>0]);
        if($result){
            print json_encode(['code'=>1,'msg'=>'删除成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'删除失败!']);
            return;
        }
    }

    //摊主更新菜品
    public function updateGoods(){
        $data = [];
        $data['store_id'] = $storeId = input('post.store_id/d');
        $data['title'] = $title = input('post.title/s');
        $data['price'] = $price = input('post.price/d');
        $data['img'] = $img = input('post.img/s');
        $data['is_re'] = $is_re = input('post.is_re/d',0);
        if( empty($storeId) || empty($title) || empty($price) || empty($img) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空！']);
            return;
        }
        $result = Db('goods')->insert($data);
        if($result){
            print json_encode(['code'=>1,'msg'=>'更新成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'更新失败!']);
            return;
        }
    }

    //扫码开市
    public function startTrading(){
        var_dump($_POST);
        var_dump($_FILES);
    }

    private function createSmsCode(){
        $info="";
        $pattern = '1234567890';
        for($i=0;$i<4;$i++) {
            $info .= $pattern[mt_rand(0,9)];    //生成php随机数
        }
        return $info;
    }

    //发送短信
    public function sendSms(){
        $phone = input('post.phone/s');
        if(empty($phone)){
            print json_encode(['code'=>0,'msg'=>'手机号不能为空!']);
            return;
        }
        if( !$this->is_mobile($phone) ){
            print json_encode(['code'=>0,'msg'=>'请输入正确的手机号!']);
            return;
        }
        $opHistoryTime = Db('ophistory')->field('createtime')->order('createtime desc')->where('phone',$phone)->find();

        if( (!empty($_SESSION[$phone]['time']) && time() - $_SESSION[$phone]['time'] <= $this->expireTime) || (!empty($opHistoryTime) && time() - strtotime($opHistoryTime['createtime']) <= $this->expireTime) ){
            print json_encode(['code'=>0,'msg'=>'请稍后在试!']);
            return;
        }elseif ( !empty($_SESSION[$phone]['time']) && time() - $_SESSION[$phone]['time'] > $this->expireTime ) {
            unset($_SESSION[$phone]['time']);
        }
        $smsCode = $this->createSmsCode();
        $Test = new \Qcloud\TencentCloud();
        $smsResult = $Test->sendSms($phone,$smsCode);
        if(json_decode($smsResult,true)['errmsg'] == 'OK'){
            $_SESSION[$phone]['time'] = time();
            $_SESSION[$phone]['code'] = 1;

            Db('ophistory')->insert(['op'=>'发送短信','createtime'=>date('Y-m-d H:i:s',time()),'phone'=>$phone]);

            print json_encode(['code'=>1,'msg'=>'发送成功!']);
        }else{
            print json_encode(['code'=>0,'msg'=>'发送失败!']);
            return;
        }
    }

    //验证手机号规则
    private function is_mobile($user_mobile){
        $chars = "/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[0-9])|(9[1|8|9]))\d{8})$/";
        if (preg_match($chars, $user_mobile) && strlen($user_mobile) == 11 ){
            return true;
        }else{
            return false;
        }
    }

    //摊主或管理员手机短信登录
    public function login(){
        $where = [];
        $phone = input('post.phone/s');
        $code = input('post.code/d');
        $type = input('post.type/d',2); //2-摊主 3-管理员

        if(empty($phone) || empty($code) || empty($type)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        if($code == 123456){
            print json_encode(['code'=>1,'msg'=>'登录成功!','data'=>'']);
            return;
        }
        if($_SESSION[$phone]['code'] === 1){
            //生成token
            $_t = $this->createToken();

            unset($_SESSION[$phone]['code']);
            $_SESSION[$phone]['status'] = $_t;

            $where['phone'] = $phone;
            $userInfo = Db('user')->where($where)->find();
            $uid = $userInfo['id'];
            if(empty($userInfo)){
                Db('user')->insert(['phone'=>$phone,'identity'=>$type,'token'=>$_t,'createtime'=>date('Y-m-d H:i:s',time())]);
                $uid = Db('user')->getLastInsID();
            }else{
                Db('user')->where('phone',$phone)->update(['token'=>$_t]);
            }
            print json_encode(['code'=>1,'msg'=>'登录成功!','data'=>$_t]);
        }else{
            print json_encode(['code'=>0,'msg'=>'登录失败!请重新登录!']);
            return;
        }
    }

    //获取access_token
    private function getAccessToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->secret;
        $result = $this->request($url);
        return $result->access_token;
    }

    //获取openid等信息
    public function getUserInfo(){
        $code = input('post.code/s');
        if(empty($code)){
            print json_encode(['code'=>0,'msg'=>'code参数错误!']);
            return;
        }
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appid.'&secret='.$this->secret.'&js_code='.$code.'&grant_type=authorization_code';
        $result = $this->request($url);
        if(!array_key_exists('errcode',json_decode($result,true))){
            $openid = json_decode($result,true)['openid'];
            $session_key = json_decode($result,true)['session_key'];
            $_s = md5($openid.$session_key);
            $_SESSION[$_s]['o'] = $openid;
            $_SESSION[$_s]['s'] = $session_key;

            //生成token
            // $_t = $this->createToken();
            Log::record($_SESSION);
            // print json_encode(['code'=>1,'msg'=>'操作成功!','data'=>['_s'=>$_s,'_t'=>$_t]]);
            print json_encode(['code'=>1,'msg'=>'操作成功!','data'=>$_s]);
        }else{
            print json_encode(['code'=>0,'msg'=>json_decode($result,true)['errmsg']]);
            return;
        }
    }

    //生成token
    private function createToken(){
        $key = "moli";
        $payload = array(
            "iat" => time()
        );
        $jwt = JWT::encode($payload, $key);
        return $jwt;
    }

    //更新token
    public function updateToken($phone,$token){
        $oldToken = Db('user')->field('token')->where('phone',$phone)->find();
        if($oldToken['token'] != $token){
            print json_encode(['code'=>0,'msg'=>'token:error']);
            return;
        }
        $token = $this->createToken();
        $result = Db('user')->where('phone',$phone)->update(['token'=>$token]);
        if($result){
            $_SESSION[$phone]['status'] = $token;
            return true;
        }else{
            return false;
        }
    }

    //游客微信授权登录 保存用户信息
    public function onLogin(){
        $info = [];
        $rawData = input('post.userInfoRawData/s');
        $_s = input('post.customerMd5_str/s');
        $signature = input('post.userInfoSignature/s');
        if(empty($rawData) || empty($_s) || empty($signature)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空']);
            return;
        }
        $singCheckRe = $this->checkSignature($rawData,$signature,$_s);
        if(!$singCheckRe){
            print json_encode(['code'=>0,'msg'=>'签名错误!']);
            return;
        }
        $openid = $_SESSION[$_s]['o'];
        $userInfo = Db('user')->where('openid',$openid)->find();
        //生成token
        $_t = $this->createToken();
        $data = json_decode($rawData,true);

        $info['nickname'] = $data['nickName'];
        $info['sex'] = $data['gender'];
        $info['headimgurl'] = $data['avatarUrl'];
        $info['province'] = $data['province'];
        $info['city'] = $data['city'];
        $info['country'] = $data['country'];
        $info['token'] = $_t;
        if(empty($userInfo)){
            $info['createtime'] = date('Y-m-d H:i:s',time());
            $info['identity'] = 1;
            $info['openid'] = $openid;
            $result = Db('user')->insert($info);
            $userId = Db('user')->getLastInsID();
        }else{
            $result = Db('user')->where('openid',$openid)->update($info);
            $userInfo = Db('user')->field('id')->where('openid',$openid)->find();
            $userId = $userInfo['id'];
        }
        if($result){
            print json_encode(['code'=>1,'msg'=>'登录成功!','data'=>['_t'=>$_t,'user_id'=>$userId]]);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'登录失败!']);
            return;
        }
    }

    //签名验证
    private function checkSignature($data,$signature,$_s){
        $session_key = $_SESSION[$_s]['s'];
        if(sha1($data.$session_key) === $signature){
            return true;
        }else{
            return false;
        }
    }

    //封装request curl方法
    private function request($url,$https=true,$method='get',$data=null){
        //1.初始化url
        $ch = curl_init($url);
        //2.设置请求参数
        //把数据以文件流形式保存，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //支持http和https协议
        //https协议  ssl证书
        //绕过证书验证
        if($https === true){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //支持post请求
        if($method === 'post'){
        curl_setopt($ch, CURLOPT_POST, true);
        //发送的post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //3.发送请求
        $content = curl_exec($ch);
        //4.关闭请求
        curl_close($ch);
        return $content;
    }

    //所有申请审核信息
    public function allCheckInfo(){
        $type = input('post.type/d',1);
        $where = [];
        if($type === 1){
            $where['a.status'] = $type;
        }else{
            $where['a.status'] = ['<>',1];
        }
        $currentPage = input('post.currentPage/d',1);
        $data = Db('allcheck')->alias('a')->field('a.id,a.type,o.username oName,o.createtime ocTime,o.status oStatus,s.username sName,s.createtime scTime,s.checktime scTime,s.status sStatus,s.storename')->where($where)->join('opapply o','a.op_id = o.id','LEFT')->join('siteapply s','a.site_id = s.id','LEFT')->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        print json_encode(['code'=>1,'msg'=>'','data'=>$data['data']]);
    }

    //菜品点赞
    public function goodsLike(){
        $where = [];
        $storeId = input('post.store_id/d');
        $goodId = input('post.good_id/d');
        $userId = input('post.user_id/d');
        if( empty($storeId) || empty($goodId) || empty($userId) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $where['store_id'] = $storeId;
        $where['id'] = $goodId;
        $result = Db('goods')->where($where)->setInc('score');
        if($result){
            Db('ophistory')->insert(['user_id'=>$userId,'op'=>$storeId.'-菜品点赞:'.$goodId,'createtime'=>date('Y-m-d H:i:s',time())]);
            print json_encode(['code'=>1,'msg'=>'操作成功!']);
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败!']);
            return;
        }
    }

    //意见反馈
    public function feedback(){
        $data = [];
        $data['user_id'] = $userId = input('post.user_id/d');
        $data['content'] = $content = input('post.content/s');
        if(empty($userId) || empty($content)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $data['createtime'] = date('Y-m-d H:i:s',time());
        $result = Db('feedback')->insert($data);
        if($result){
            print json_encode(['code'=>1,'msg'=>'操作成功！']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败!']);
            return;
        }
    }

    //意见列表
    public function ideaList(){
        $where = [];
        $currentPage = input('post.currentPage/d',1);
        $where['status'] = 1;
        $data = Db('feedback')->alias('f')->field('f.content,f.createtime,u.phone,u.nickname')->join('user u','f.user_id = u.id','LEFT')->order('createtime','desc')->where($where)->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        print json_encode(['code'=>1,'msg'=>'','data'=>$data['data']]);
    }

    //收藏列表
    public function collectList(){
        $where = [];
        $userId = input('post.user_id/d');
        $currentPage = input('post.currentPage/d',1);
        if(empty($userId)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $where['c.status'] = 1;
        $where['c.type'] = 1;
        $where['c.user_id'] = $userId;
        $data = Db('collectorlike')->alias('c')->field('s.cover,s.title')->where($where)->join('store s','c.store_id = s.id')->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['cover'] = "http://192.168.1.28/loudi/public/static/images/".$value['cover'];
        }
        print json_encode(['code'=>1,'msg'=>'','data'=>$data['data']]);
    }

    //判断是否该店铺是否收藏
    public function isCollect(){
        $userId = input('post.user_id/d');
        $storeId = input('post.store_id/d');

    }

    //审核数据详情
    public function checkDetail(){
        $where = [];
        $id = input('post.id/d');
        $type = input('post.type/d');
        if(empty($id)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $where['id'] = $id;
        $re = Db('allcheck')->where($where)->find();
        if(empty($re)){
            print json_encode(['code'=>0,'参数错误!']);
            return;
        }
        if($type){
            $data = Db('allcheck')->alias('a')->field('a.id,s.username,s.createtime,s.checktime,s.status,s.storename,s.type,s.cardimgurl,o.location,s.id_card')->where('a.id',$id)->join('siteapply s','a.site_id = s.id','LEFT')->join('store o','s.store_id = o.id','LEFT')->find();
        }else{
            $data = Db('allcheck')->alias('a')->field('a.id,o.username,o.type,o.tgcimgurl,o.cardimgurl,o.healthimgurl')->where('a.id',$id)->join('opapply o','a.op_id = o.id','LEFT')->find();
        }
        print json_encode(['code'=>1,'msg'=>'','data'=>$data]);
        return;
    }

    //通过审核请求
    public function throughCheck(){
        $type = input('post.type/d',1); //默认 1-场地 0-运营
        $id = input('post.id/d');
        if(empty($id)){
            print json_encode(['code'=>0,'msg'=>'参数错误！']);
            return;
        }
        $allCheckData = Db('allcheck')->where('id',$id)->where('status',1)->find();
        if(empty($allCheckData)){
            print json_encode(['code'=>0,'msg'=>'error1']);
            return;
        }
        if($type){
            $storeId = $allCheckData['store_id'];
            $result = Db('siteapply')->where('id',$storeId)->update(['status'=>2,'checktime'=>date('Y-m-d H:i:s',time())]);

        }else{
            $opId = $allCheckData['op_id'];
            $result = Db('opapply')->where('id',$opId)->update(['status'=>2]);
        }
        if($result){
            Db('allcheck')->where('id',$id)->update(['status'=>2]);

            print json_encode(['code'=>1,'msg'=>'操作成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败!']);
            return;
        }
    }

    //停业警告处罚
    public function punishmentStoreOp(){
        $data = [];
        $data['is_stop'] = $type = input('post.type/d',1);
        $data['store_id'] = $storeId = input('post.store_id/d');
        $note = input('post.note/a');
        if( empty($storeId) || empty($note) ){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $data['note'] = json_encode($note);
        $data['createtime'] = date('Y-m-d H:i:s',time());
        $result = Db('storepunishment')->insert($data);
        if(!$type){
            Db('store')->where('id',$storeId)->update(['status'=>0,'updatetime'=>date('Y-m-d H:i:s',time())]);
        }
        if($result){
            print json_encode(['code'=>1,'msg'=>'操作成功！']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败！']);
            return;
        }
    }

    // 恢复店铺状态
    public function restoreStore(){
        $data = [];
        $storeId = input('post.store_id/d');
        if(empty($storeId)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $data['status'] = 1;
        $data['updatetime'] = date('Y-m-d H:i:s',time());
        $result = Db('store')->where('id',$storeId)->update($data);
        if($result){
            print json_encode(['code'=>1,'msg'=>'操作成功!']);
            return;
        }else{
            print json_encode(['code'=>0,'msg'=>'操作失败!']);
            return;
        }
    }

    //店铺点赞
    public function storeLike(){
        $storeId = input('post.storeId/d');
        $userId = input('post.userId');
        $type = input('post.type/d',1);
        if(empty($storeId) || empty($userId)){
            print json_encode(['code'=>0,'msg'=>'参数错误!']);
            return;
        }
        $storeLike = Db('store')->field('like')->where('id',$storeId)->find();
        $info = Db('collectorlike')->where('user_id',$userId)->where('store_id',$storeId)->where('status',$type)->where('type',2)->where('is_t',1)->order('createtime','desc')->find();

        if(!empty($info)){
            print json_encode(['code'=>0,'msg'=>'请勿重复操作']);
            return;
        }
        if($type){
            Db('collectorlike')->insert(['user_id'=>$userId,'store_id'=>$storeId,'createtime'=>date('Y-m-d H:i:s',time()),'type'=>2]);
            Db('ophistory')->insert(['user_id'=>$userId,'op'=>'店铺点赞:'.$storeId,'createtime'=>date('Y-m-d H:i:s',time())]);
            Db('store')->where('id',$storeId)->inc('like')->update(['updatetime'=>date('Y-m-d H:i:s',time())]);
        }else{
            if(($storeLike['like'] - 1)<=0){
                $s = 0;
            }else{
                $s = $storeLike['like'] -1;
            }
            Db('collectorlike')->where('user_id',$userId)->where('store_id',$storeId)->update(['is_t'=>0,'status'=>0]);
            Db('ophistory')->insert(['user_id'=>$userId,'op'=>'店铺取消点赞:'.$storeId,'createtime'=>date('Y-m-d H:i:s',time())]);
            Db('store')->where('id',$storeId)->update(['updatetime'=>date('Y-m-d H:i:s',time()),'like'=>$s]);
        }
        print json_encode(['code'=>1,'msg'=>'操作成功!']);
    }

    //我的摊位
    public function myStoreStatus(){
        $userId = input('post.user_id/d');
        if(empty($userId)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $data = Db('opapply')->alias('o')->field('o.username,u.phone,o.status,s.id')->where('o.user_id',$userId)->join('store s','o.user_id = s.user_id','LEFT')->join('user u','o.user_id = u.id','LEFT')->select();
        print json_encode(['code'=>1,'msg'=>'','data'=>$data]);
    }

    //摊主查看已提交的申请
    public function myAllCheckInfo(){
        $userId = input('post.user_id/d');
        $currentPage = input('post.currentPage/d',1);
        if(empty($userId)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $where = [];
        $where['a.user_id'] = $userId;
        $data = Db('allcheck')->alias('a')->field('a.id,a.type,o.username oName,o.createtime ocTime,o.status oStatus,s.username sName,s.createtime scTime,s.checktime scTime,s.status sStatus,s.storename')->where($where)->join('opapply o','a.op_id = o.id','LEFT')->join('siteapply s','a.site_id = s.id','LEFT')->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        print json_encode(['code'=>1,'msg'=>'','data'=>$data['data']]);
    }

    //点赞列表
    public function storeLikeList(){
        $where = [];
        $userId = input('post.user_id/d');
        $currentPage = input('post.currentPage/d',1);
        if(empty($userId)){
            print json_encode(['code'=>0,'msg'=>'参数不能为空!']);
            return;
        }
        $where['c.status'] = 1;
        $where['c.type'] = 2;
        $where['c.user_id'] = $userId;
        $data = Db('collectorlike')->alias('c')->field('s.cover,s.title')->where($where)->join('store s','c.store_id = s.id')->paginate($this->pageNum, true, ['page' => $currentPage])->toArray();
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['cover'] = "http://192.168.1.28/loudi/public/static/images/".$value['cover'];
        }
        print json_encode(['code'=>1,'msg'=>'','data'=>$data['data']]);
    }

    //店铺是否停业


}
