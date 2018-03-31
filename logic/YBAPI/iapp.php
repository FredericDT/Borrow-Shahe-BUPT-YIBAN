<?php
/*

Borrow-Shahe BUPT-YIBAN
Copyright (C) 2017-2018  BUPT-YIBAN DEV-GROUP

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

require("classes/yb-globals.inc.php");

session_start();
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
/**
 * 配置文件
 */
include('config.php');
include('./../availableRoomFunctions.php');

$api = YBOpenApi::getInstance()->init($cfg['appID'], $cfg['appSecret'], $cfg['appCallback']);
$iapp  = $api->getIApp();

if(!isset($_GET['verify_request']) and !isset($_SESSION['id']))
{
    header("Location: " . $cfg['appCallback']);
    exit;
}

if (empty($_SESSION['token'])) {
    //$api = YBOpenApi::getInstance()->init($cfg['appID'], $cfg['appSecret'], $cfg['callback']);

    //$info = $api->getFrameUtil()->perform();
    # print_r($info);	// 可以输出info数组查看
    // 访问令牌[visit_oauth][access_token]

    //$_SESSION['token'] = $info['visit_oauth']['access_token'];
    try {
        //轻应用获取access_token，未授权则跳转至授权页面
        $info = $iapp->perform();
        $_SESSION['token'] = $info['visit_oauth']['access_token'];
        //print_r($info);
    } catch (YBException $ex) {
        echo $ex->getMessage();
    }

}
/*
		$apit = YBOpenApi::getInstance()->bind($_SESSION['token']);
		
		$au = $apit->init($cfg['appID'], $cfg['appSecret'], $cfg['callback'])->getAuthorize();
		
		$judge = $au->query();
		
		if($judge['status']==404){
			$au->revoke();
			unset($_SESSION['token']);
			unset($_SESSION['name']);
			unset($_SESSION['student_id']);
			unset($_SESSION['usrid']);
			unset($_SESSION['assess_token']);
			header("Location:http://f.yiban.cn/" . $iapp_id);
			echo ("权限申请有误，请从易班网页进入页面。");
			exit;
		}
*/
	
	$api->bind($_SESSION['token']);
	//$user = $api->getUser();
	$real_me = $api->request('user/verify_me');
//print_r($real_me);
	
	if ($real_me['info']['yb_schoolid'] == 1005) {
	    $yb_userid = $real_me['info']['yb_userid'];
        //print_r($yb_userid);
	    $user = RoomFunctions::getInstance()->getUserByIdentifer($yb_userid);
        //print_r($user);
	    if ($user == null) {
            $yb_type = User::TYPE_STUDENT;
            //print_r($real_me['info']);
	        if ($real_me['info']['yb_employid'] != null) {
	            $yb_type = User::TYPE_COUNSELOR;
            }
            //print_r($yb_type);
            $realname = $real_me['info']['yb_realname'];
            //print_r($realname);
            $student_id = $real_me['info']['yb_studentid'] == null ? $real_me['info']['yb_employid'] : $real_me['info']['yb_studentid'];
            //print_r($student_id);
            $database_username = $student_id . " " . $realname;
            //print_r($database_username);
            echo RoomFunctions::getInstance()->insertNewUser($yb_userid, $database_username, $yb_type);
            $user = RoomFunctions::getInstance()->getUserByIdentifer($yb_userid);
            //print_r($user);
        } else {
            updateUserProfile($real_me);
        }
        $_SESSION['id'] = $user->getId();
        header("Location: ./../../template/index.php");
    } else {
	    echo '<h1>你并不是一个我邮人</h1>';
    }

	/**
	 * adaptive()生成页面自适合代码，是否需要调用由开发者自行决定
	 */
	//$adaptive = $api->getFrameUtil()->adaptive();
	
function updateUserProfile($real_me) {
    //print_r($real_me);
	$yb_userid = $real_me['info']['yb_userid'];
    $user = RoomFunctions::getInstance()->getUserByIdentifer($yb_userid);
    $realname = $real_me['info']['yb_realname'];
    $student_id = $real_me['info']['yb_studentid'] == null ? $real_me['info']['yb_employid'] : $real_me['info']['yb_studentid'];
    $database_username = $student_id . " " . $realname;
    $type = $user->getType();
    if ($type > User::TYPE_REVIEWER) {
    	if ($real_me['info']['yb_employid'] != null) {
	        $type = User::TYPE_COUNSELOR;
        }
    }
    RoomFunctions::getInstance()->updateUserProfile($user->getId(), $database_username, $type);
}