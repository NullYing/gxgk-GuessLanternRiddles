<?php
namespace Home\Controller;
use Think\Controller;
class RiddleController extends Controller {
    public function index(){
      $this->_checkReceive($openid,$nickname,$msg);
      //录入手机
  		$SqlContact=D("Contact");
  		$result=$SqlContact->where('openid="%s"',$openid)->count();
  		if($result==0){
        $this->_logPhone($openid,$nickname,$msg);
  		}
      $SqlUser=D("User");
      $result=$SqlUser->where('openid="%s"',$openid)->count();
      if($result==0){
        $user['openid']=$openid;
        $user['status']='willstart';
        $SqlUser->data($user)->add();
      }
      $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
      if(!$UserInfo){
        $this->data='小喵在数据库中找不到你，呜呜呜，请回复“取消”，之后召唤客服';
        $this->display();
        exit;
      }
      //进入状态
      $this->_entern_Status($SqlUser,$UserInfo,$openid,$msg);
    }
  protected function _checkFoul($SqlUser,$UserInfo,$openid,$msg){
    if($UserInfo['foultime']>5){
      $user['openid']=$openid;
      $user['status']='Baned';
      $SqlUser->data($user)->save();
      $this->data='喵！坏人，尝试违规刷题，你已被小喵封印，小喵不准你参加本次活动啦！';
      $this->display();
      exit;
    }
  }
  protected function _checkReceive(&$openid,&$nickname,&$msg){
      if(IS_POST){
        $key=I('post.key',"");
        if($key!="gxgkdevelor"){
          $this->data='非法请求';
          $this->display();
          exit;
        }
        $openid=I('post.openid',"");
        if($openid==""){
          $this->data='非法请求';
          $this->display();
          exit;
        }
        $nickname=I('post.nickname',"");
        if($nickname==""){
          $this->data='非法请求';
          $this->display();
          exit;
        }
        $msg=I('post.msg',"");
        if($msg==""){
          $this->data='非法请求';
          $this->display();
          exit;
        }
      }
      else{
        $this->data='非法请求';
        $this->display();
        exit;
      }
  }
  protected function _entern_Status($SqlUser,$UserInfo,$openid,$msg){
    $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
    switch ($UserInfo['status']) {
      case 'willstart':
        if(strstr($msg,'兑换')){
          $this->data='参加今天的活动后，才能兑换明信片';
          $this->display();
          break;
        }
        else if(strstr($msg,'赠送')){
          $this->data='参加今天的活动后，才能赠送喵币';
          $this->display();
          break;
        }
        
        if($msg=='开始'){
          $this->_startActivity($SqlUser,$UserInfo,$openid,$msg);
        }
        else{
          $this->data='请回复“开始”，开始计时';
          $this->display();
          exit;
        }
        break;
      case 'End':
        //进入喵币兑换状态
        if(strstr($msg,'兑换')){
          $this->_enter_ExchangeStatus($SqlUser,$UserInfo,$openid,$msg);
          break;
        }
        //进入赠送喵币状态
        else if(strstr($msg,'赠送')){
          $this->_enter_SendStatus($SqlUser,$UserInfo,$openid,$msg);
          break;
        }
        $this->_endActivity($SqlUser,$UserInfo,$openid,$msg);
        $this->data='本活动一天只能参与一次\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”\n\n回复“取消”回到正常模式';
        $this->display();
        break;
      case 'Starting':
        //刷题封禁
        $this->_checkFoul($SqlUser,$UserInfo,$openid,$msg);
        //活动计时到达
        $this->_checkTimeOut($SqlUser,$UserInfo,$openid,$msg);
        $this->_judge_Answer($openid,$msg);
        $this->_getRiddle($openid);
        break;
      case 'ExchangePostcard':
        $this->_exchange_Postcard($openid,$msg);
        break;
      case 'willsend':
        $this->_send_Grade($SqlUser,$UserInfo,$openid,$msg);
        break;
      case 'Baned':
        $this->data='喵！坏人，尝试违规刷题，你已被小喵封印，小喵不准你参加本次活动啦！';
        $this->display();
        break;
      default:
        $this->data='呜呜呜，处于无效状态，请回复“取消”，之后召唤客服';
        $this->display();
        break;
      }
      exit;
  }
  protected function _logPhone($openid,$nickname,$msg){
    if(!preg_match("/1[3458]{1}\d{9}$/",$msg)){
      $this->data='请输入正确的手机号';
      $this->display();
      exit;
    }
    $SqlContact=D("Contact");
    $result=$SqlContact->where('phone="%s"',$msg)->count();
    if($result!=0){
      $this->data='喵，该手机号已被使用，请重新输入';
      $this->display();
      exit;
    }
    else{
      $contect['openid']=$openid;
      $contect['nickname']=$nickname;
      $contect['phone']=$msg;
      $contect['jointime']=date('Y-m-d H:i:s');
      $SqlContact->data($contect)->add();
      $this->data='成功录入您的手机号，请回复“开始”，开始计时';
      $this->display();
      exit;
    }
  }
  protected function _startActivity($SqlUser,$UserInfo,$openid,$msg){
      $user['openid']=$openid;
      $user['status']='Starting';
      switch ($UserInfo['joinnum']) {
        case '1':
          $user['starttime']=date('Y-m-d H:i:s');
          break;
        case '2':
          $user['secondstart']=date('Y-m-d H:i:s');
          break;
        case '3':
          $user['threestart']=date('Y-m-d H:i:s');
          break;
        default:
          $this->data='呜呜呜，小喵系统出错啦，请回复“取消”，之后召唤客服';
          $this->display();
          exit;
      }
      $SqlUser->data($user)->save();
      $this->_entern_Status($SqlUser,$UserInfo,$openid,$msg);
  }
  protected function _checkTimeOut($SqlUser,$UserInfo,$openid,$msg){
    $user['openid']=$openid;
    $user['status']='End';
    if($UserInfo['joinnum']==1 AND strtotime("now")-strtotime($UserInfo['starttime'])>1200){
      $user['firstend']=date('Y-m-d H:i:s');
      $this->data='时间到！！！欢迎明天继续参加活动，快拉上亲友团送喵币吧！\n\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>';
      $SqlUser->data($user)->save();
      $this->display();
      exit;
    }
    else if($UserInfo['joinnum']==2 AND strtotime("now")-strtotime($UserInfo['secondstart'])>1200 ){
      $user['secondend']=date('Y-m-d H:i:s');
      $this->data='时间到！！！欢迎明天继续参加活动，快拉上亲友团送喵币吧！\n\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>';
      $SqlUser->data($user)->save();
      $this->display();
      exit;
    }
    else if($UserInfo['joinnum']==3 AND strtotime("now")-strtotime($UserInfo['threestart'])>1200){
      $user['threeend']=date('Y-m-d H:i:s');
      $this->data='时间到！！！感谢你参与本次活动，快拉上亲友团送喵币吧！\n\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>';
      $SqlUser->data($user)->save();
      $this->display();
      exit;
    }
  }
  protected function _getRiddle($openid){
    $SqlAnswer=D("Answer");
    $SqlRiddle=D("Riddle");
    $SqlUser=D("User");
    $AnswerInfo=$SqlAnswer->where('openid="%s"',$openid)->select();
    $RiddleInfo=$SqlRiddle->select();
    if(!$RiddleInfo){
      $this->data='抽出题目错误！！！系统异常，请回复“取消”，之后召唤客服';
      $this->display();
      exit;
    }
    //去除重复题目
    for($id=0;$id<sizeof($AnswerInfo);$id++){
      $aid=$AnswerInfo[$id]['riddleid'];
      unset($RiddleInfo[$aid]);
    }
    unset($id);unset($aid);
    if(sizeof($RiddleInfo)===0){
        $this->data='小喵的题库空啦，已经没题目啦！！';
        $this->display();
        exit();
    }
    //打乱数组
    $keys = array_keys($RiddleInfo);   
    shuffle($keys);   
    $random = array();   
    foreach ($keys as $key){  
      $random[$key] = $RiddleInfo[$key];
    } 
    //数组重新索引
    $RiddleInfo=array_merge($random);
    //按难度排序
    usort($RiddleInfo, function($a, $b) {
            $al = $a['difficult'];
            $bl = $b['difficult'];
            if ($al == $bl)
                return 0;
            return ($al < $bl) ? -1 : 1;
    });
    //抽出谜题
    $this->data='谜题：'.$RiddleInfo[0]['question'];
    $this->display();
    $data['openid']=$openid;
    $data['finalquestion']=$RiddleInfo[0]['id'];
    $SqlUser->data($data)->save();
    //记录已答题目
    unset($data);
    $data['openid']=$openid;
    $data['riddleid']=$RiddleInfo[0]['id'];
    $SqlAnswer->data($data)->add();
  }
  protected function _judge_Answer($openid,$msg){
    $SqlRiddle=D("Riddle");
    $SqlUser=D("User");
    //刷题封禁
    $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
    if(strtotime("now")-strtotime($UserInfo['finalanswer'])<3){
      $this->data='刷题太快啦，请休息下吧，注意哦！违规刷题将会被小喵禁止参加本次活动！';
      $data['openid']=$openid;
      $data['foultime']=$UserInfo['foultime']+1;
      $SqlUser->data($data)->save();
      unset($data);
      $this->display();
      exit;
    }
    $FinalRiddle=$UserInfo['finalquestion'];
    if($FinalRiddle==NULL){
      return;
    }
    $RiddleInfo=$SqlRiddle->select();
    if(!$RiddleInfo){
        $this->data='获取谜底失败，请重试';
        $this->display();
        exit();
    }
    $SqlAnswer=D("Answer");
    $AnswerData['openid']=$openid;
    $AnswerData['riddleid']=$FinalRiddle;
    unset($data);
    $data['openid']=$openid;
    $data['finalanswer']=date('Y-m-d H:i:s');
    if(strstr($msg,$RiddleInfo[$FinalRiddle]['answer']) OR strstr($msg,$RiddleInfo[$FinalRiddle]['answer2'])){
      $data['grade']=$UserInfo['grade']+5;
      $AnswerData['YesOrNot']=1;
      $this->data2='恭喜你，回答正确，加5分\n当前喵币为：'.$data['grade'].'\n\n';
    }
    else{
      $AnswerData['YesOrNot']=0;
      $this->data2='很遗憾，回答错误\n不要紧，答错不会扣分\n可以换题哦！\n当前喵币为：'.$UserInfo['grade'].'\n\n';
    }
    $SqlUser->data($data)->save();
    $AnswerData['AnswerTime']=date('Y-m-d H:i:s');
    $SqlAnswer->data($AnswerData)->save();
  }
  protected function _endActivity($SqlUser,$UserInfo,$openid,$msg){
    $nextday=false;
    if($UserInfo['joinnum']==1){
      $nextday=date('Y-m-d',strtotime($UserInfo['starttime'])) !== date('Y-m-d');
    }
    else if($UserInfo['joinnum']==2){
      $nextday=date('Y-m-d',strtotime($UserInfo['secondstart'])) !== date('Y-m-d');
    }
    if($nextday AND $UserInfo['joinnum'] < 3){
      $user['openid']=$openid;
      $user['status']='willstart';
      $user['finalquestion']=NULL;
      $user['joinnum']=$UserInfo['joinnum']+1;
      if($user['joinnum']==2){
        $this->data='欢迎第二次参加小喵灯谜竞猜\n回复“开始”，开始活动';
      }
      else if($user['joinnum']==3){
        $this->data='欢迎第三次参加小喵灯谜竞猜\n回复“开始”，开始活动';
      }
      $SqlUser->data($user)->save();
      $this->display();
      exit;
    }
  }
  protected function _enter_SendStatus($SqlUser,$UserInfo,$openid,$msg){
    if($UserInfo['grade']==0){
      $this->data='对不起，你的喵币为0，无法赠送';
      $this->display();
      exit;
    }
    $user['openid']=$openid;
    $user['status']='willsend';
    $SqlUser->data($user)->save();
    $this->data='请输入要对方的手机号\n注意：赠送喵币将全部赠送\n\n如想退出赠送，请回复“返回”';
    $this->display();
    exit;
  }
  protected function _send_Grade($SqlUser,$SendMan,$openid,$msg){
    $SqlContact=D("Contact");
    if($msg=='返回'){
      $this->_return_End($SqlUser,$openid);
    }
    if(!preg_match("/1[3458]{1}\d{9}$/",$msg)){
      $this->data='请输入正确的手机号';
      $this->display();
      exit;
    }
    $ContactInfo=$SqlContact->where('phone="%s"',$msg)->find();
    if(!$ContactInfo){
      $this->data='该手机号未参加活动\n自动退出赠送模式';
      $this->display();
      $user['openid']=$openid;
      $user['status']='End';
      $SqlUser->data($user)->save();
      exit;
    }
    $ReceiveMan=$SqlUser->where('openid="%s"',$ContactInfo['openid'])->find();
    if(!$SendMan OR !$ReceiveMan){
      $this->data='小喵出错啦，请重试！';
      $this->display();
      exit;
    }
    //判断手机号是否为自己的
    if($ReceiveMan['openid']===$openid){
      $this->data='不可将喵币赠送给自己\n自动退出赠送模式';
      $this->display();
      $user['openid']=$openid;
      $user['status']='End';
      $SqlUser->data($user)->save();
      exit;
    }
    //清空发送者的喵币
    $data['openid']=$openid;
    $data['grade']=0;
    $data['sendtime']=$SendMan['sendtime']+1;
    $data['status']='End';
    $SqlUser->data($data)->save();
    //给予接受者增加喵币
    unset($data);
    $data['openid']=$ReceiveMan['openid'];
    $data['grade']=$ReceiveMan['grade']+$SendMan['grade'];
    $data['receivetime']=$ReceiveMan['receivetime']+1;
    $SqlUser->data($data)->save();
    $this->data='已经将喵币赠送给'.$ContactInfo['nickname'].'啦！';
    $this->display();
    exit;
  }
  protected function _return_End($SqlUser,$openid){
    $user['openid']=$openid;
    $user['status']='End';
    $this->data='喵，已经成功返回啦';
    $SqlUser->data($user)->save();
    $this->display();
    exit;
  }
  protected function _enter_ExchangeStatus($SqlUser,$UserInfo,$openid,$msg){
    $SqlContact=D("Contact");
    if($UserInfo['grade']==0){
      $this->data='对不起，你的喵币为0，无法兑换';
      $this->display();
      exit;
    }
    $SqlPostCard=D("PostCard");
    $TotalPostcard=$SqlPostCard->where('id="0"')->find();
    $ContactInfo=$SqlContact->where('openid="%s"',$openid)->find();
    unset($user);
    $user['openid']=$openid;
    $user['status']='ExchangePostcard';
    $SqlUser->data($user)->save();
    $this->data='请输入要兑换的明信片数量\n注意：兑换明信片将影响影响排名，一张明信片为100喵币\n\n你拥有'.$ContactInfo['postcard'].'张明信片\n你可兑换'.(floor($UserInfo['grade']/100)).'张明信片\n小喵剩余下'.$TotalPostcard['total'].'张明信片\n\n如想退出兑换，请回复“返回”';
    $this->display();
    exit;
  }
  protected function _exchange_Postcard($openid,$msg){
    $SqlContact=D("Contact");
    $SqlUser=D("User");
    $SqlPostCard=D("PostCard");
    if($msg=='返回'){
      $this->_return_End($SqlUser,$openid);
    }
    if(preg_match("/[^\d-., ]/",$num)){
      $this->data='喵！请输入数量，且不带中文';
      $this->display();
      exit;
    }
    else{
      $num=(int)$msg;
      $num=floor($num);
    }
    if(!is_numeric($num) OR $num<=0){
      $this->data='喵！请输入正确的数量';
      $this->display();
      exit;
    }
    $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
    //喵币足够兑换
    if($UserInfo['grade']<100*$num){
      unset($user);
      $user['openid']=$openid;
      $user['status']='End';
      $SqlUser->data($user)->save();
      $this->data='对不起，你的喵币不够兑换\n自动退出兑换模式';
      $this->display();
      exit;
    }
    $TotalPostcard=$SqlPostCard->where('id="0"')->find();
    if(!$TotalPostcard){
      $this->data='小喵出错啦，请重试！';
      $this->display();
      exit;
    }
    //检查明信片总数
    if($TotalPostcard['total']<0){
      unset($user);
      $user['openid']=$openid;
      $user['status']='End';
      $SqlUser->data($user)->save();
      $this->data='喵！对不起，小喵的明信片被抢空啦！呜呜呜！！';
      $this->display();
      exit;
    }
    //检查剩余张数大于兑换数
    if($num>$TotalPostcard['total']){
      $this->data='喵！对不起，小喵的明信片没有剩下这么多！呜呜呜！！\n剩余明信片数量'.$TotalPostcard['total'].'张';
      $this->display();
      exit;
    }
    else{
      $data['id']=0;
      $data['total']=$TotalPostcard['total']-$num;
      $SqlPostCard->data($data)->save();
    }
    //扣除相应喵币
    unset($data);
    $data['openid']=$openid;
    $data['grade']=$UserInfo['grade']-100*$num;
    $SqlUser->data($data)->save();
    //增加明信片数量
    $ContactInfo=$SqlContact->where('openid="%s"',$openid)->find();
    unset($data);
    $data['openid']=$openid;
    $data['postcard']=$ContactInfo['postcard']+$num;
    $SqlContact->data($data)->save();

    $this->data='已经成功兑换'.$num.'张明信片！\n\n你已兑换了'.$data['postcard'].'张明信片\n小喵剩下'.($TotalPostcard['total']-$num).'张明信片可以兑换\n活动结束后，小喵将联系你';
    $this->display();
    unset($user);
    $user['openid']=$openid;
    $user['status']='End';
    $SqlUser->data($user)->save();
    exit;
  }
  public function rank(){
    $mydata=false;
    $funtion=false;
    if(IS_GET){
      $openid=I('get.openid',"");
      if($openid!=""){
        $mydata=true;
      }
    }
    $this->mydata=$mydata;
    $this->funtion=$funtion;

    $SqlUser=D("User");
    $SqlUser->join('Contact ON Contact.openid = User.openid');
    $UserInfo=$SqlUser->order('grade DESC,finalanswer ASC')->field('User.openid,nickname,grade')->select();
    for($myrank=0;$myrank<sizeof($UserInfo);$myrank++){
      if($UserInfo[$myrank]['openid']===$openid){
        $UserMy=$UserInfo[$myrank];
        break;
      }
    }
    if(sizeof($UserMy)===0){
      $this->mygrade='暂无喵币';
      $this->myrank='暂无排名';
    }
    else{
      $this->nickname=$UserMy['nickname'];
      $this->mygrade=$UserMy['grade'];
      $this->myrank=$myrank+1;
    }
    for($id=0;$id<10;$id++){
      $toprank[$id]['id']=$id+1;
      $toprank[$id]['nickname']=$UserInfo[$id]['nickname'];
      $toprank[$id]['grade']=$UserInfo[$id]['grade'];
    }
    $this->toprank=$toprank;
    $this->display();
  }
  public function riddlebegin(){
    $this->_checkReceive($openid,$nickname,$msg);
    $SqlUser=D("User");
    $SqlContact=D("Contact");
    $result=$SqlContact->where('openid="%s"',$openid)->count();
    if($result==0){
      $this->data='欢迎参加小喵灯谜竞猜活动\n\n每个人将有20分钟时间答题，且答题期间无法暂停\n活动结束时间：\n2月23日凌晨0点\n\n注：\n1.喵币为本次活动积分名称\n2.您所输入的手机号仅作为领取活动奖品以及喵币赠送凭据\n\n回复“取消”退出猜灯谜活动\n<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n\n请输入您的手机';
      $this->display();
      exit;
    }
    $result=$SqlUser->where('openid="%s"',$openid)->count();
    if($result==0){
      //对于新参加用户，插入数据
      $user['openid']=$openid;
      $user['status']='willstart';
      $SqlUser->data($user)->add();
      $this->data='欢迎参加小喵灯谜竞猜活动\n\n每个人将有20分钟时间答题，且答题期间无法暂停\n活动结束时间：\n2月23日凌晨0点\n\n注：\n1.喵币为本次活动积分名称\n2.您所输入的手机号仅作为领取活动奖品以及喵币赠送凭据\n\n回复“取消”退出猜灯谜活动\n<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n\n回复“开始”开始计时';
      $this->display();
      exit;
    }
    else{
      $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
      switch ($UserInfo['status']) {
        case 'willstart':
          switch ($UserInfo['joinnum']) {
            case '1':
              $this->data='欢迎参加小喵灯谜竞猜活动\n\n每个人将有20分钟时间答题，且答题期间无法暂停\n活动结束时间：\n2月23日凌晨0点\n\n注：\n1.喵币为本次活动积分名称\n2.您所输入的手机号仅作为领取活动奖品以及喵币赠送凭据\n\n回复“取消”退出猜灯谜活动\n<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”\n\n回复“开始”开始计时';
              break;
            case '2':
              $this->data='欢迎继续参加小喵灯谜竞猜活动\n\n每个人将有20分钟时间答题，且答题期间无法暂停\n活动结束时间：\n2月23日凌晨0点\n\n回复“取消”退出猜灯谜活动\n<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”\n\n回复“开始”开始计时';
              break;
            case '3':
              $this->data='欢迎继续参加小喵灯谜竞猜活动\n\n每个人将有20分钟时间答题，且答题期间无法暂停\n活动结束时间：\n2月23日凌晨0点\n\n回复“取消”退出猜灯谜活动\n<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”\n\n回复“开始”开始计时';
              break;
            default:
              $this->data='喵，系统错误，请回复“取消”，之后召唤客服';
              break;
          }
          break;
        case 'End':
          switch ($UserInfo['joinnum']) {
            case '1':
              $this->_endActivity($SqlUser,$UserInfo,$openid,$msg);
              $this->data='本活动一天只能参与一次\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”\n\n回复“取消”回到正常模式';
              break;
            case '2':
              $this->_endActivity($SqlUser,$UserInfo,$openid,$msg);
              $this->data='本活动一天只能参与一次\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”\n\n回复“取消”回到正常模式';
              break;
            case '3':
              $this->data='感谢您对本次活动的支持\n已经达到最大参加次数\n活动结束后排名前十的同学以及兑换了明信片的同学，小喵将联系您，请耐心等待\n\n回复“取消”退出猜灯谜活动\n<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>\n\n温馨提示：\n赠送喵币，请回复“赠送”\n兑换明信片，请回复“兑换”';
              break;
            default:
              $this->data='喵，系统错误，请回复“取消”，之后召唤客服';
              break;
          }
          break;
        case 'Starting':
          $this->_endActivity($SqlUser,$UserInfo,$openid,$msg);
          $this->_checkTimeOut($SqlUser,$UserInfo,$openid,$msg);
          $this->data='喵，赶紧答题，还有时间，快！快！快！\n\n回复“取消”回到正常模式';
          break;
        case 'ExchangePostcard':
          $this->data='喵，正处于兑换明信片状态\n请回复正确的明信片数量\n注意：兑换明信片将影响影响排名，一张明信片为100喵币\n你可兑换'.(floor($UserInfo['grade']/100)).'张明信片\n\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>';
          break;
        case 'willsend':
          $this->data='喵，正处于喵币赠送状态\n请回复对方的手机号赠送喵币\n注意：赠送喵币将全部赠送\n\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">排行榜点我</a>';
          break;
        case 'Baned':
          $this->order='noenter';
          $this->data='喵！坏人，尝试违规刷题，你已被小喵封印，小喵不准你参加本次活动啦！';
          break;
        default:
          $this->data='喵，系统错误，请回复“取消”，之后召唤客服';
          break;
      }
      $this->display();
      exit;
    }
  }
}