<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/14 0014
 * Time: 13:45
 */

namespace app\index\controller;

use think\Db;
use QL\QueryList;
use tool\Logger;
use tool\Http;

class Janshu{
    public static $deep=6;
    public function __construct(){
        $file = ROOT_PATH.'public/data/cook1.txt';
        if(!file_exists($file)){
            touch($file);
        }
        Http::set_cookie_file($file);
        Http::set_cookie_jar($file);
    }

    public function getuserinfo($uid,$deep=1){
        $userinfo = Db::name('js_users')->where(array('uid'=>$uid))->find();
        if(!$userinfo || $userinfo['status'] == 0){
            $html = QueryList::html(Http::get('https://www.jianshu.com/u/'.$uid));
            $rule = [
                'followings'=>['.info > ul > li:nth-child(1) p','text'],
                'followers'=>['.info > ul > li:nth-child(2) p','text'],
                'articles'=>['.info > ul > li:nth-child(3) p','text'],
                'words'=>['.info > ul > li:nth-child(4) p','text'],
                'getlike'=>['.info > ul > li:nth-child(5) p','text'],
                'username'=>['.title .name','text'],
                'sex1'=>['.title i','class'],
                'avatar'=>['.main-top .avatar img','src']
            ];
            $res = $html->rules($rule)->query()->getData()->all();
            if($res){
                $data = $res[0];
                $data['avatar'] = explode('?',$data['avatar'])[0];
                if(strpos($data['sex1'],'-man')){
                    $data['sex'] = 1;
                }else{
                    $data['sex'] = 0;
                }
                unset($data['sex1']);
                $data['status'] = 1;
                if(!$userinfo){
                    $data['uid'] = $uid;
                    $data['deep'] = 1;
                    $data['time'] =  time();
                    Db::name('js_users')->insert($data);
                }else{
                    $data['deep'] = $userinfo['deep']+1;
                    Db::name('js_users')->where(array('uid'=>$uid))->update($data);
                }
            }
            $html->destruct();
            if($total = intval($data['followers'])){
               for($i=1;$i<=intval($total/10)+1;$i++){
                   $this->getuserlist($uid,'followers',$i);
               }
            }
            if($total = intval($data['following'])){
                for($i=1;$i<=intval($total/10)+1;$i++){
                    $this->getuserlist($uid,'followers',$i);
                }
            }
        }
    }
    public function getuserlist($uid,$type='followers',$page=1){
        $html = QueryList::html(Http::get('https://www.jianshu.com/users/'.$uid.'/'.$type.'?page='.$page));
        $res = $html->find('#list-container   a.avatar')->attrs('href')->all();
        foreach ($res as $item){
            $uid = explode('/',$item)[2];
            if(Db::name('js_users')->where(array('uid'=>$uid))->find()){
                continue;
            }else{
                $data = array(
                    'uid'=>  $uid,
                    'time'=>time()
                );
                Db::name('js_users')->insert($data);
            }
        }
    }

    public function test(){
        $html = QueryList::html(Http::get('https://www.jianshu.com/users/24ddd38310e4/followers?page=1'));
        $res = $html->find('#list-container   a.avatar')->attrs('href')->all();
        foreach ($res as $item){
            $uid = explode('/',$item)[2];
            if(Db::name('js_users')->where(array('uid'=>$uid))->find()){
                continue;
            }else{
                $data = array(
                  'uid'=>  $uid,
                    'time'=>time()
                );
                Db::name('js_users')->insert($data);
            }
        }
        #list-container > ul > li:nth-child(1) > a.avatar
        return "ssssssss";
        $rule = [
            'followings'=>['.info > ul > li:nth-child(1) p','text'],
            'followers'=>['.info > ul > li:nth-child(2) p','text'],
            'articles'=>['.info > ul > li:nth-child(3) p','text'],
            'words'=>['.info > ul > li:nth-child(4) p','text'],
            'getlike'=>['.info > ul > li:nth-child(5) p','text'],
            'username'=>['.title .name','text'],
            'sex1'=>['.title i','class'],
            'avatar'=>['.main-top .avatar img','src']
        ];
        //$res = $html->find('.info p')->texts();
        //$html->rules($rule)->queryData();
        $res = $html->rules($rule)->query()->getData()->all();
        if($res){
            $data = $res[0];
            $data['avatar'] = explode('?',$data['avatar'])[0];
            if(strpos($data['sex1'],'-man')){
                $data['sex'] = 1;
            }else{
                $data['sex'] = 0;
            }
        }
        print_r($data);
        return "aaaaaaa";
    }
}