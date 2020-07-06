<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-03-22
 * Time: 17:29
 */
namespace app\facerecognition\controller;
class Ocr{
    private $config = [
        'openApiAppId' => '',
        'openApiAppVersion' => '1.0.0',
        //'orderNo' => '',
        //'openApiNonce' => '',
        //'openApiUserId' => '',
        //'openApiSign' => '',
    ];
    //允许最大请求次数
    private $max_try_times = 5;

    /**
     * @Notes:
     * @Function signature
     * @param $userId
     * @param bool $refresh
     * @return array|mixed|string
     * @Author: gxk
     * @Date: 2020-03-23
     * @Time: 1:08
     */
    public function signature($userId,$refresh=false){
        $userId_str = unknow_to_str($userId);
        if(false===$userId_str){//系统不支持类型
            $redis_key = 'fr:signature:'.$userId_str;
            $fr_signature = redis_get($redis_key);
            if(false==$refresh&&$fr_signature){//有效
                $result = [SUCCESS,['fr_signature'=>$fr_signature]];
            }else{//重新获取
                $fr_ticket = $this->getNonceTicket($userId_str,$refresh);
                if(0==$fr_ticket['status']){
                    $config = [
                        'wbappid' => '',
                        'userId' => $userId_str,
                        'version' => '1.0.0',
                        'ticket' => $fr_ticket['nonce_ticket'],
                        'nonceStr' => create_nonce_str($userId_str),
                    ];
                    asort($config);
                    $array_value = implode('',array_values($config));
                    $fr_signature = sha1($array_value);
                    redis_set($redis_key,$fr_signature);
                    $result = [SUCCESS,['signature'=>$fr_signature]];
                }else{
                    wlog(['fr_signature 失败：nonce_ticke获取不成功!']);
                    $result = [FAIL];
                }
            }
        }
        return create_result_data(...$result);
    }

    public function resultCheckInBackground($orderNo,$refresh){
        //TODO::未完成，需要设计fr_order表
        $order_validate = ['status'=>0,'data'=>['user_id'=>'']];
        if(0==$orderNo['status']){
            $userId = unknow_to_str($order_validate['data']['user_id']);
            $fr_nonce_ticket = $this->getNonceTicket($userId,$orderNo,$refresh);
            $fr_sign_ticket = $this->getSignTicket($refresh);
            if(0==$fr_nonce_ticket['status']&&0==$fr_sign_ticket['status']){
                $config = [
                    'wbappid' => '',
                    'order_no' => $orderNo,
                    'ticket' => $fr_sign_ticket['data']['sign_ticket'],
                    'nonceStr' => $fr_nonce_ticket['data']['nonce_ticket'],
                ];
                asort($config);
                $array_value = implode('',array_values($config));
                $fr_signature = sha1($array_value);
                $config = [
                    'app_id' => '',
                    'order_no' => $orderNo,
                    'get_file' => 1,//获取识别的图片
                    'nonce' => $fr_nonce_ticket['data']['nonce_ticket'],
                    'version' => '1.0.0',
                    'sign' => $fr_signature,
                ];
                $url = 'https://idasc.webank.com/api/server/getOcrResult';
                $result = [FAIL];
                $times = $this->max_try_times;
                /*{
                        "frontCode": "0",
                        "backCode": "0",
                        "orderNo": "121321313",
                        "name": "张三",
                        "sex": "男",//待测
                        "nation": "汉",//待测
                        "birth": "1990年01月20日",//待测
                        "address": "东莞市",//待测
                        "idcard": "444025854222222222   ",//待测
                        "validDate": "2020年03月10日   ",//待测
                        "authority": "发证机关   ",//待测
                        "frontPhoto": "base64   ",//人像面照片，转换后为 JPG 格式
                        "backPhoto": "base64   ",//国徽面照片，转换后为 JPG 格式
                        "frontCrop": "base64   ",//人像面切边照片
                        "backCrop": "base64   ",//国徽面切边照片
                        "headPhoto": "base64   ",//身份证头像照片
                        "frontWarnCode": "66661003   ",//人像面告警码，在身份证有遮挡、缺失、信息不全时会返回告警码；当 frontCode 为0时才会出现告警码，
                        "backWarnCode": "66661003   ",//国徽面告警码，在身份证有遮挡、缺失、信息不全时会返回告警码；当 backCode 为0时才会出现告警码，
                        "operateTime": "11111111111   ",//待验证
                        "frontMultiWarning": "11111111111   ",//待验证 正面多重告警码，
                        "backMultiWarning": "11111111111   ",//待验证 反面多重告警码，
                        "frontClarity": "11111111111   ",//待验证 正面图片清晰度
                        "backClarity": "11111111111   ",//待验证 反面图片清晰度
                    }*/
                if($times == 0){//无限次
                    while (1){
                        $curl_res = curl($url,$config);
                        if(0==$curl_res['code']&&"0"==$curl_res['code']){
                            $result = [SUCCESS,['nonce_ticket'=>$curl_res['tickets'][0]['value']]];
                            break;
                        }else if(15==$curl_res['code']&&"15"==$curl_res['code']){//access_token失效
                            $access_token_data = $this->getAccessToken($refresh);
                            if(0!=$access_token_data['status']){//获取access_token失败
                                break;
                            }else{
                                $config['access_token'] = $access_token_data['data']['access_token'];
                            }
                        }else{
                            wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                        }
                    }
                }else{
                    while ($times>0){
                        $curl_res = curl($url,$config);
                        if(0==$curl_res['code']&&"0"==$curl_res['code']){
                            $result = [SUCCESS,['nonce_ticket'=>$curl_res['tickets'][0]['value']]];
                            break;
                        }else if(15==$curl_res['code']&&"15"==$curl_res['code']){//access_token失效
                            $access_token_data = $this->getAccessToken($refresh);
                            if(0!=$access_token_data['status']){//获取access_token失败
                                wlog(['msg'=>'getNonceTicket失败，获取access_token失败!','userId'=>$userId]);
                                break;
                            }else{
                                $config['access_token'] = $access_token_data['data']['access_token'];
                            }
                        }else{
                            --$times;
                            wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                        }
                    }
                }

                wlog(['msg'=>'resultCheckInBackground失败，resultCheckSignInBackground!','orderNo'=>$orderNo]);
                $result = [ARGUMENT_ERROR];
            }

        }else{
            $result = [DATA_ERROR];
        }
        return create_result_data(...$result);
    }

    /**
     * @Notes:
     * @Function resultCheckSignInFront
     * 检测返回的检测结果是否是送检[前台]
     * @param $order_no
     * @param $refresh
     * @return array|mixed|string
     * @Author: gxk
     * @Date: 2020-03-27
     * @Time: 1:33
     */
    public function resultCheckSignInFront($orderNo,$refresh){
        $fr_ticket = $this->getSignTicket($refresh);
        if(0==$fr_ticket['status']){
            $config = [
                'wbappid' => '',
                'order_no' => $orderNo,
                'ticket' => $fr_ticket['sign_ticket'],
            ];
            asort($config);
            $array_value = implode('',array_values($config));
            $fr_signature = sha1($array_value);
            $result = [SUCCESS,['signature'=>$fr_signature]];
        }else{
            wlog(['resultCheckSignInFront 失败：sign_ticket获取不成功!','order_no'=>$orderNo]);
            $result = [FAIL];
        }
        return create_result_data(...$result);
    }

    /**
     * @Notes:
     * @Function getSignTicket
     * 前置条件：请合作方确保 Access Token 已经正常获取，获取方式请参见 获取 Access Token。
     * SIGN ticket 是合作方后台服务端业务请求生成签名鉴权参数之一，用于后台查询验证结果、调用其他业务服务等。
     * API ticket 的 SIGN 类型，其有效期最长为3600秒，此处 API ticket 必须缓存在磁盘，并定时刷新，刷新的机制如下：
     * 因为 API ticket 依赖于 Access Token，所以生命周期最长为 3600秒。为了简单方便，建议将 API ticket 与 Access Token 绑定，每20分钟定时刷新，原 API ticket 1 小时（3600秒）失效。
     * 获取新的之后请立即使用最新的，旧的有一分钟的并存期。
     * @param bool $refresh
     * @return array|mixed|string
     * @Author: gxk
     * @Date: 2020-03-27
     * @Time: 1:23
     */
    public function getSignTicket($refresh=false){
        $redis_key = 'fr:sign_ticket';
        $fr_sign_ticket = redis_get($redis_key);
        if(false==$refresh&&$fr_sign_ticket){//有效
            $result = [SUCCESS,['sign_ticket'=>$fr_sign_ticket]];
        }else{//重新获取
            $access_token_data = $this->getAccessToken($refresh);;
            //参数有效检测
            if(0==$access_token_data['status']){
                $config = [
                    'app_id' => '',
                    'access_token' => $access_token_data['access_token'],
                    'type' => 'SIGN',
                    'version' => '1.0.0',
                ];
                $url = 'https://idasc.webank.com/api/oauth2/api_ticket';
                $result = [FAIL];
                $times = $this->max_try_times;
                /*{
                    "code": "0",
                    "msg": "请求成功",
                    "transactionTime": "20151022044027",
                    "tickets": [{
                        "value": "ticket_string",
                        "expire_in": "3600",
                        "expire_time": "20151022044027"
                    }]
                }*/
                if($times == 0){//无限次
                    while (1){
                        $curl_res = curl($url,$config);
                        if(0==$curl_res['code']&&"0"==$curl_res['code']){
                            //设置缓存
                            //由于服务器时间和可能与腾讯云服务器存在偏差不使用绝对时间，而使用最大生存时间
                            redis_set($redis_key,$curl_res['tickets'][0]['value']);
                            redis_expire($redis_key,$curl_res['tickets'][0]['expire_in']);
                            $result = [SUCCESS,['access_token'=>$curl_res['tickets'][0]['value']]];
                            break;
                        }else{
                            wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                        }
                    }
                }else{
                    while ($times>0){
                        $curl_res = curl($url,$config);
                        if(0==$curl_res['code']&&"0"==$curl_res['code']){
                            redis_set($redis_key,$curl_res['tickets'][0]['value']);
                            redis_expire($redis_key,$curl_res['tickets'][0]['expire_in']);
                            $result = [SUCCESS,['access_token'=>$curl_res['tickets'][0]['value']]];
                            break;
                        }else{
                            --$times;
                            wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                        }
                    }
                }
            }else{
                wlog(['msg'=>'getSignTicket失败，获取access_token失败!']);
                $result = [ARGUMENT_ERROR];
            }
        }
        return create_result_data(...$result);
    }

    /**
     * @Notes:
     * @Function getAccessToken
     * 所有场景默认采用 UTF-8 编码。
     * Access Token 必须缓存在磁盘并定时刷新，建议每20分钟请求新的 Access Token，原 Access Token 2小时（7200秒）失效，获取之后请立即使用最新的 Access Token。旧的 Access Token 只有一分钟的并存期 。
     * 每次用户登录时必须重新获取 ticket。
     * @param bool $refresh 是否强制刷新【本地服务器和腾讯云存在时差】
     * @return array|mixed|string
     * @Author: gxk
     * @Date: 2020-03-22
     * @Time: 18:48
     */
    public function getAccessToken($refresh=false){
        $redis_key = 'fr:access_token';
        $fr_access_token = redis_get($redis_key);
        if(false==$refresh&&$fr_access_token){//有效
            $result = [SUCCESS,['access_token'=>$fr_access_token]];
        }else{//重新获取
            $config = [
                'app_id' => '',
                'secret' => '',
                'grant_type' => 'client_credential',
                'version' => '1.0.0',
            ];
            $url = 'https://idasc.webank.com/api/oauth2/access_token';
            $result = [FAIL];
            $times = $this->max_try_times;
            /*{
                "code":"0","msg":"请求成功",
                "transactionTime":"20151022043831",
                "access_token":"accessToken_string",
                "expire_time":"20151022043831",
                "expire_in":"7200"
                }*/
            if($times == 0){//无限次
                while (1){
                    $curl_res = curl($url,$config);
                    if(0==$curl_res['code']&&"0"==$curl_res['code']){
                        //设置缓存
                        //由于服务器时间和可能与腾讯云服务器存在偏差不使用绝对时间，而使用最大生存时间
                        redis_set($redis_key,$curl_res['access_token']);
                        redis_expire($redis_key,$curl_res['expire_in']);
                        $result = [SUCCESS,['access_token'=>$curl_res['access_token']]];
                        break;
                    }else{
                        wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                    }
                }
            }else{
                while ($times>0){
                    $curl_res = curl($url,$config);
                    if(0==$curl_res['code']&&"0"==$curl_res['code']){
                        redis_set($redis_key,$curl_res['access_token']);
                        redis_expire($redis_key,$curl_res['expire_in']);
                        $result = [SUCCESS,['access_token'=>$curl_res['access_token']]];
                        break;
                    }else{
                        --$times;
                        wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                    }
                }
            }
        };
        return create_result_data(...$result);
    }


    /**
     * @Notes:
     * @Function getNonceTicket
     * 前置条件：请合作方确保 Access Token 已经正常获取，获取方式请参见 Access Token 获取。
     * NONCE ticket 是合作方前端包含 App 和 H5 等生成签名鉴权参数之一，启动 H5 或 SDK 人脸核身。
     * API ticket 的 NONCE 类型，其有效期为120秒，且一次性有效，即每次启动 SDK 刷脸都要重新请求 NONCE ticket。
     * @param $userId
     * @param bool $refresh
     * @return array|mixed|string
     * @Author: gxk
     * @Date: 2020-03-22
     * @Time: 19:15
     */
    public function getNonceTicket($userId,$refresh=false){
        $access_token_data = $this->getAccessToken($refresh);;
        //参数有效检测
        if(0==$access_token_data['status']){
            $config = [
                'app_id' => 'TIDAE0fg',
                'access_token' => $access_token_data['data']['access_token'],
                'type' => 'client_credential',
                'version' => '1.0.0',
                'user_id' => $userId,
            ];
            $url = 'https://idasc.webank.com/api/oauth2/api_ticket';
            $result = [FAIL];
            $times = $this->max_try_times;
            /*{
                    "code": "0",
                    "msg": "请求成功",
                    "transactionTime": "20151022044027",
                    "tickets": [{
                        "value": "ticket_string",
                        "expire_in": "120",
                        "expire_time": "20151022044027"
                    }]
                }*/
            if($times == 0){//无限次
                while (1){
                    $curl_res = curl($url,$config);
                    if(0==$curl_res['code']&&"0"==$curl_res['code']){
                        $result = [SUCCESS,['nonce_ticket'=>$curl_res['tickets'][0]['value']]];
                        break;
                    }else if(15==$curl_res['code']&&"15"==$curl_res['code']){//access_token失效
                        $access_token_data = $this->getAccessToken($refresh);
                        if(0!=$access_token_data['status']){//获取access_token失败
                            break;
                        }else{
                            $config['access_token'] = $access_token_data['data']['access_token'];
                        }
                    }else{
                        wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                    }
                }
            }else{
                while ($times>0){
                    $curl_res = curl($url,$config);
                    if(0==$curl_res['code']&&"0"==$curl_res['code']){
                        $result = [SUCCESS,['nonce_ticket'=>$curl_res['tickets'][0]['value']]];
                        break;
                    }else if(15==$curl_res['code']&&"15"==$curl_res['code']){//access_token失效
                        $access_token_data = $this->getAccessToken($refresh);
                        if(0!=$access_token_data['status']){//获取access_token失败
                            wlog(['msg'=>'getNonceTicket失败，获取access_token失败!','userId'=>$userId]);
                            break;
                        }else{
                            $config['access_token'] = $access_token_data['data']['access_token'];
                        }
                    }else{
                        --$times;
                        wlog(['curl_res'=>$curl_res,'times'=>$times]);//记录失败日志
                    }
                }
            }
        }else{
            wlog(['msg'=>'getNonceTicket失败，获取access_token失败!','userId'=>$userId]);
            $result = [ARGUMENT_ERROR];
        }
        return create_result_data(...$result);
    }
}