<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/3/19
 * Time: 1:58 PM
 */
namespace app\index\controller;

use think\facade\Cache;

class Test extends \think\Controller{
    public function index(){
        return $this->fetch();
    }
    public function test(){
        dump(request());
        return json('success');
    }
    public function tag(){
        Cache::tag('personal')->set('name','gxk');
    }
    public function getTagValue(){
        echo Cache::tag('personal')->get('name');
    }
    public function testWsWorker(){
        return $this->fetch();
    }
    public function testFileUpload(){
        return $this->fetch();
    }
    public function saveFile(){
        upload_file();
    }

    /**
     * @Notes:
     * @Function faceRecognition
     * 人脸识别
     * @return array|mixed|null|string|string[]|void
     * @Author: gxk
     * @Date: 2020-03-22
     * @Time: 19:20
     */
    public function faceRecognition(){
        //return dump(get_access_token());
        return dump(get_nonce_ticket('1'));
    }
}