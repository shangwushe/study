<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-11-15
 * Time: 15:00
 */
namespace  app\Index\controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {
    protected $config = [
        'Host'=>'smtp.163.com',
        'Port'=>465,//163邮箱的ssl协议方式端口号是465/994
        'SMTPAuth'=>true,
        'SMTPSecure'=>'ssl',
        'CharSet'=>'UTF-8',
        'Encoding'=>'base64',
        'Username'=>'test@163.com',//我的邮箱
        'Password'=>'password',
        'From'=>'test@163.com',//发件人地址
        'FromName'=>'智通教育',//发件人名
        'Subject'=>'智通教育提醒您',//邮件标题
    ];
    protected $mail = null;

    /**
     * Mail constructor.
     * @param array $config
     */
    public function __construct($config=[]){
        $system_config = get_system_config('ztjy163_mail_config');
        $this->config = array_merge($this->config,$system_config,$config);
        $this->mail = new PHPMailer(); //实例化
        $this->mail->IsSMTP(); // 启用SMTP
        $this->mail->Host = $this->config['Host']??''; //SMTP服务器 以126邮箱为例子
        $this->mail->Port = $this->config['Port']??'';  //邮件发送端口
        $this->mail->SMTPAuth = $this->config['SMTPAuth']??'';  //启用SMTP认证
        $this->mail->SMTPSecure = $this->config['SMTPSecure']??'';   // 设置安全验证方式为ssl
        $this->mail->CharSet = $this->config['CharSet']??''; //字符集
        $this->mail->Encoding = $this->config['Encoding']??''; //编码方式
        $this->mail->Username = $this->config['Username']??'';  //你的邮箱
        $this->mail->Password = $this->config['Password']??'';  //你的授权码
        $this->mail->From = $this->config['From']??'';  //发件人地址（也就是你的邮箱）
        $this->mail->FromName = $this->config['FromName']??'';  //发件人姓名
        $this->mail->Subject = $this->config['Subject']??''; //邮件标题
    }

    /**
     * @Notes:
     * @Function sendEmail
     * @param $data
     * @Author: gxk
     * @Date: 2019-11-16
     * @Time: 15:59
     */
    public function sendEmail($data){
        if($data && is_array($data)){
            $this->mail->AddAddress($data['user_email'], $data['user_name']??"亲"); //添加收件人（地址，昵称）
            $this->mail->IsHTML(true); //支持html格式内容
            $this->mail->Body = $data['content']; //邮件主体内容
            //发送成功就删除
            $res = $this->mail->Send();
            if ($res) {
                echo "发送成功";
            }else{
                echo "Mailer Error: ".$this->mail->ErrorInfo;// 输出错误信息
            }
        }
    }
}