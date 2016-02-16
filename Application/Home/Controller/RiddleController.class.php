<?php
namespace Home\Controller;
use Think\Controller;
class RiddleController extends Controller {
    public function index(){
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
      //录入手机
  		$SqlContact=D("Contact");
  		$result=$SqlContact->where('openid="%s"',$openid)->count();
  		if($result==0){
  			if(!preg_match("/1[3458]{1}\d{9}$/",$msg)){
  				$this->data='请输入正确的手机号';
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
      //对于新参加用户，插入数据
  		$SqlUser=D("User");
  		$result=$SqlUser->where('openid="%s"',$openid)->count();
  		if($result==0){
  			$user['openid']=$openid;
  			$user['status']='willstart';
  			$SqlUser->data($user)->add();
  		}
      $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
      /*暂时废弃
      if(($UserInfo['status']==='End') AND strstr($msg,'兑换')){
        unset($user);
        $user['openid']=$openid;
        $user['status']='ExchangePostcard';
        $SqlUser->data($user)->save();
        $this->data='请输入要兑换的明信片数量\n注意：兑换明信片将影响影响排名';
        $this->display();
        exit;
      }

      if($UserInfo['status']==='ExchangePostcard'){
        $this->_exchange_Postcard($openid,$msg);
        exit;
      }
      */
      if($UserInfo['foultime']>5){
        $this->data='喵！坏人，尝试违规刷题，你已被小喵封印，小喵不准你参加本次活动啦！';
        $this->display();
        exit;
      }
      //进入赠送积分状态
      if($UserInfo['status']==='End' AND strstr($msg,'赠送') AND $UserInfo['status']!=='ExchangePostcard'){
        if($UserInfo['grade']==0){
          $this->data='对不起，你的积分为0，无法赠送';
          $this->display();
          exit;
        }
        unset($user);
        $user['openid']=$openid;
        $user['status']='willsend';
        $SqlUser->data($user)->save();
        $this->data='请输入要对方的手机号\n注意：赠送积分将全部赠送';
        $this->display();
        exit;
      }
      if($UserInfo['status']==='willsend'){
        $this->_send_Grade($openid,$msg);
        exit;
      }
      //赠送他人积分
      /*
      if($UserInfo['status']==='Sended'){
        $this->data='本活动只能参与一次，不可多次参与\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">个人排名点我</a>\n\n回复“取消”回到正常模式';
        $this->display();
        exit;
      }
      */
      //已结束参加活动
  		if($UserInfo['status']==='End'){
        $nextday=false;
        if($UserInfo['joinnum']==1){
          $nextday=date('Y-m-d',strtotime($UserInfo['starttime'])) !== date('Y-m-d');
        }
        else if($UserInfo['joinnum']==2){
          $nextday=date('Y-m-d',strtotime($UserInfo['secondstart'])) !== date('Y-m-d');
        }
        if($nextday AND $UserInfo['joinnum'] < 3){
          unset($user);
          $user['openid']=$openid;
          $user['status']='Starting';
          $user['finalquestion']=0;
          $user['joinnum']=$UserInfo['joinnum']+1;
          if($user['joinnum']==2){
            $user['secondstart']=date('Y-m-d H:i:s');
            $this->data2='欢迎第二次参加小喵灯谜竞猜\n';
          }
          else if($user['joinnum']==3){
            $user['threestart']=date('Y-m-d H:i:s');
            $this->data2='欢迎第三次参加小喵灯谜竞猜\n';
          }
          $SqlUser->data($user)->save();
          $this->display();
          exit;
        }
        else{
  			 $this->data='本活动一天只能参与一次，不可多次参与\n查看<a href=\"http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'\">个人排名点我</a>\n温馨提示：可将积分赠送给他人，为好友助攻，回复“赠送”，把积分赠送给好友\n\n回复“取消”回到正常模式';
  			 $this->display();
  			 exit;
        }
  		}
      //活动计时到达
      if($UserInfo['status']==='Starting'){
        unset($user);
        $user['openid']=$openid;
        $user['status']='End';
        if(strtotime("now")-strtotime($UserInfo['starttime'])>1200 AND $UserInfo['joinnum']==1){
          $user['finaltime']=date('Y-m-d H:i:s');
          $this->data='时间到！！！欢迎明天继续参加活动，快拉上亲友团送积分吧！\n查看<a href="http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'">个人排名点我</a>';
          $SqlUser->data($user)->save();
          $this->display();
          exit;
        }
        else if(strtotime("now")-strtotime($UserInfo['secondstart'])>1200 AND $UserInfo['joinnum']==2){
          $user['secondend']=date('Y-m-d H:i:s');
          $this->data='时间到！！！欢迎明天继续参加活动，快拉上亲友团送积分吧！\n查看<a href="http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'">个人排名点我</a>';
          $SqlUser->data($user)->save();
          $this->display();
          exit;
        }
        else if(strtotime("now")-strtotime($UserInfo['threestart'])>1200 AND $UserInfo['joinnum']==3){
          $user['threeend']=date('Y-m-d H:i:s');
          $this->data='时间到！！！感谢你参与本次活动，快拉上亲友团送积分吧！\n查看<a href="http://lantern.gxgk.cc/?s=/Home/Riddle/rank/openid/'.$openid.'">个人排名点我</a>';
          $SqlUser->data($user)->save();
          $this->display();
          exit;
        }
      }
      //开始活动
  		if($UserInfo['status']==='willstart' AND $msg=='开始'){
        unset($user);
  			$user['openid']=$openid;
  			$user['status']='Starting';
  			$user['starttime']=date('Y-m-d H:i:s');
  			$SqlUser->data($user)->save();
        $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
  		}
      if($UserInfo['status']==='Starting'){
        $this->_judge_Answer($openid,$msg);
        $this->_getRiddle($openid);
        exit;
      }
  		else{
  			$this->data='请回复“开始”，开始计时';
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
      $this->data='抽出题目错误！！！系统异常';
      $this->display();
      exit;
    }
    for($id=0;$id<sizeof($AnswerInfo);$id++){
      $aid=$AnswerInfo[$id]['riddleid'];
      unset($RiddleInfo[$aid]);
    }
    unset($id);unset($aid);
    if(sizeof($RiddleInfo)===0){
        $this->data='题库空啦，已经没题目啦！！';
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
    if(!$FinalRiddle){
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
    if(strstr($msg,$RiddleInfo[$FinalRiddle]['answer'])){
      $data['grade']=$UserInfo['grade']+5;
      $AnswerData['YesOrNot']=1;
      $this->data2='恭喜你，回答正确，加5分\n当前分数为：'.$data['grade'].'分\n\n';
    }
    else{
      $AnswerData['YesOrNot']=0;
      $this->data2='很遗憾，回答错误\n不要紧，答错不会扣分\n可以换题哦！\n当前分数为：'.$UserInfo['grade'].'分\n\n';
    }
    $SqlUser->data($data)->save();
    $AnswerData['AnswerTime']=date('Y-m-d H:i:s');
    $SqlAnswer->data($AnswerData)->save();
  }
  public function rank(){
    $mydata=false;
    $funtion=false;
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
      $mydata=true;
      $funtion=true;
    }
    else if(IS_GET){
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
      $this->mygrade='暂无积分';
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
  protected function _send_Grade($openid,$msg){
    $SqlUser=D("User");
    $SqlContact=D("Contact");
    if(!preg_match("/1[3458]{1}\d{9}$/",$msg)){
      $this->data='请输入正确的手机号';
      $this->display();
      exit;
    }
    $result=$SqlContact->where('phone="%s"',$msg)->find();
    if(!$result){
      $this->data='该手机号未参加活动，自动退出赠送功能';
      $this->display();
      $user['openid']=$openid;
      $user['status']='End';
      $SqlUser->data($user)->save();
      exit;
    }
    $ReceiveMan=$SqlUser->where('openid="%s"',$result['openid'])->find();
    $SendMan=$SqlUser->where('openid="%s"',$openid)->find();
    //清空发送者的分数
    $data['openid']=$openid;
    $data['grade']=0;
    $data['status']='End';
    $SqlUser->data($data)->save();
    //给予接受者增加分数
    unset($data);
    $data['openid']=$ReceiveMan['openid'];
    $data['grade']=$ReceiveMan['grade']+$SendMan['grade'];
    $SqlUser->data($data)->save();
    $this->data='已经将积分赠送给'.$result['nickname'].'啦！';
    $this->display();
    exit;
  }
  /*废弃函数
  protected function _exchange_Postcard($openid,$msg){
    $SqlContact=D("Contact");
    $SqlUser=D("User");
    if(is_int($msg)){
      $this->data='请输入正确的数量';
      $this->display();
      exit;
    }
    $UserInfo=$SqlUser->where('openid="%s"',$openid)->find();
    //分数足够兑换
    if($UserInfo['grade']<50*$msg){
      $this->data='对不起，你的分数不够兑换';
      $this->display();
      exit;
    }
    //扣除相应分数
    $data['openid']=$openid;
    $data['grade']=$UserInfo['grade']-50*$msg;
    $SqlUser->data($data)->save();
    unset($data);
    //增加明信片数量
    $ContactInfo=$SqlContact->where('openid="%s"',$openid)->find();
    $data['openid']=$openid;
    $data['postcard']=$ContactInfo['postcard']+$msg;
    $SqlContact->data($data)->save();

    $this->data='已经成功兑换'.$msg.'明信片！\n现有'.$data['postcard'].'张明信片，系统剩下 张明信片可以兑换';
    $this->display();
    $user['openid']=$openid;
    $user['status']='End';
    $SqlUser->data($user)->save();
    exit;
  }
  */
}