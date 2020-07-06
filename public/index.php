<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;
//定义错误编码
define('SUCCESS',0);//操作成功
define('FAIL',11);//操作失败
define('SYSTEM_ERROR',12);//系统错误
define('URL_NOT_FOUND',13);//请求URL不存在
define('USER_NOT_LOGIN',14);//用户未登录
define('USER_NOT_AUTH',15);//用户无权限
define('ARGUMENT_ERROR',21);//参数错误
define('ARGUMENT_INVAILD',22);//参数无效
define('DATA_ERROR',31);//数据错误
define('CUSTOM_ERROR',999);//自定义错误
// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->run()->send();
