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
/**
 * Created by PhpStorm.
 * User: Frederic_DT
 * Date: 01/10/2017
 * Time: 19:14
 */

error_reporting(E_ALL);
require('YBAPI/classes/yb-globals.inc.php');

session_start();
if ($_SESSION['id'] == null) {
    header("HTTP/1.1 401 Unauthorized");
    echo "unauthorized";
    exit;
}
include('YBAPI/config.php');
include("availableRoomFunctions.php");
date_default_timezone_set("Asia/ShangHai");
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
//header('Content-Type: application/json; charset=utf-8');
function isNumber($input) {
    return preg_match('/^[0-9]+$/', $input) > 0;
}
function isYmdHis($input) {
    return preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)\ ([0-9]+)\:([0-9]+)\:([0-9]+)$/', $input) > 0;
}
switch($_POST['function']) {
    /**
     * ban a user
     *
     * @param $_POST['function'] string "banUser"
     * @param $_POST['targetId'] integer - target user id
     * @return 1 - execute successful
     * @return string - error message
     *
     * e.g. POST backend.php "function=banUser&targetId=1"
     *
     * */
    case "banUser":
    	if (! isNumber($_POST['targetId'])) {
        	http_response_code(400);
            exit;
        }
        echo RoomFunctions::getInstance()->banUser($_SESSION['id'], $_POST['targetId']);
        exit;
    /**
     * pardon a banned user
     *
     * @param $_POST['function'] string "pardonUser"
     * @param $_POST['targetId'] integer - target user id
     * @return 1 - execute successful
     * @return string - error message
     *
     * e.g. POST backend.php "function=pardonUser&targetId=1"
     *
     * */
    case "pardonUser";
    	if (! isNumber($_POST['targetId'])) {
        	http_response_code(400);
            exit;
        }
        echo RoomFunctions::getInstance()->pardonUser($_SESSION['id'], $_POST['targetId']);
        exit;
    /**
     * revoke a legal state application
     * can be performed by normal user to revoke its application
     * also can be performed by administrator to cancel an application
     *
     * @param $_POST['function'] string "revokeApplication"
     * @param $_POST['applicationId'] integer - target application's id
     * @return 1 - execute successful
     * @return string - error message
     *
     * e.g. POST backend.php "function=revokeApplication&applicationId=1"
     *
     * */
    case "revokeApplication":
    	if (! isNumber($_POST['applicationId'])) {
        	http_response_code(400);
            exit;
        }
        $roomapi = RoomFunctions::getInstance();
        $ret = $roomapi->cancelRoomApplication($_SESSION['id'], $_POST['applicationId']);

        if ($ret == 1) {
            $ybapi = YBOpenApi::getInstance()->init($cfg['appID'], $cfg['appSecret'], $cfg['appCallback']);
            $ybapi->bind($_SESSION['token']);
            $application = $roomapi->getOrderById($_POST['applicationId']);
            $msg = sprintf('您 %s 在 %s 的预定已被取消，详情咨询管理员。',
                $application->getStart(), $roomapi->getRoomById($application->getRoom())->getName());
            //$msg = json_encode(['start'=> $application->getStart(), 'room' => $roomapi->getRoomById($application->getRoom())]);
            $result = $ybapi->request('msg/letter',
                ['to_yb_uid' => $roomapi->getUserById($application->getApplicant())->getIdentifer(),
                'content' => $msg,
                'template' => 'user'], true);
            if ($result['status'] == 'error') {
                $ret = 'failed to send message to inform the applicant, code:' . $result['info']['code'];
            }
        }

        echo $ret;

        exit;
    /**
     * review a legal state application, turn it to 'gugugu' review state
     * only can be performed by user who level lower than 3
     *
     * @param $_POST['function'] string "reviewApplication"
     * @param $_POST['applicationId'] integer - target application's id
     * @return 1 - execute successful
     * @return string - error message
     *
     * e.g. POST backend.php "function=reviewApplication&applicationId=1"
     *
     * */
    case "reviewApplication":
    	if (! isNumber($_POST['applicationId'])) {
        	http_response_code(400);
            exit;
        }
        $review = RoomFunctions::getInstance()->adminReviewRoomApplication($_SESSION['id'], $_POST['applicationId'], 1);
        $ban = RoomFunctions::getInstance()->banUser($_SESSION['id'], $_POST['applicantId']);
        if ($ban == 1) {
            if ($review == 1) {
                echo true;
            } else {
                echo $review;
            }
        } else {
            echo $ban;
        }
        exit;
    /**
     * aim to handle ajax request from the frontend
     * to update the user's contact information
     *
     * @param $_POST['function'] string "updateContact"
     * @param $_POST['contact'] string - user's contact info
     * @return 1 - execute successful
     * @return string - error message
     *
     * e.g. POST backend.php "function=updateContact&contact=13900000000"
     *
     * */
    case "updateContact":
        if(preg_match("/^[0-9]{11}$/", $_POST['contact'])) {
            echo RoomFunctions::getInstance()->updateUserContact($_SESSION['id'], $_POST['contact']);
        } else {
            echo "invalid phone number";
        }
        exit;

    /**
     * a function intend to handle update user type request
     * only available to user <= USER::TYPE_SYSTEM_ADMINISTRATOR
     *
     * @param $_POST['function'] string "updateUserType"
     * @param $_POST['targetId'] integer - target user id
     * @param $_POST['type'] integer - target type to update
     *
     * e.g. POST backend.php "function=updateUserType&targetId=1&type=0"
     *
     * */
   case "updateUserType":

      if (!isNumber($_POST['targetId'])) {
          http_response_code(400);
          exit;
      }
      echo json_encode(RoomFunctions::getInstance()->updateUserType($_SESSION['id'], $_POST['targetId'], $_POST['type']));
      exit;

    /**
     * aim to handle ajax request from the frontend
     * to submit an application
     *
     * @param $_POST['function'] string "submitApplication"
     * @param $_POST['time_mark'] string - string of time "2017-07-07"
     * @param $_POST['long'] integer - period length in hours
     * @param $_POST['room'] integer - room id
     * @param $_POST['borrow_reason'] string - borrow reason
     * @return 1 - execute successful
     * @return string - error message
     *
     * e.g. POST backend.php "function=submitApplication&time_mark=2017-07-07&long=1&room=1&reason=thisIsThePlaceHolderOfBorrowingReason"
     *
     * */
    case "submitApplication":
    	if (! (isNumber($_POST['room']) && isNumber($_POST['long']))) {
        	http_response_code(400);
            exit;
        }
    	if (! isYmdHis($_POST['time_mark'])) {
        	http_response_code(400);
            exit;
        }
        $start = strtotime($_POST['time_mark']);
        $end = strtotime($_POST['time_mark'] . " + " . $_POST['long'] . "hours");
        echo RoomFunctions::getInstance()->submitRoomApplication($_SESSION['id'], $_POST['room'], $start, $end, $_POST['borrow_reason']);
        //print_r($_POST);
        exit;
    /**
     * aim to handle ajax request from the frontend
     * to edit an available's time
     *
     * @param $_POST['function'] string "editAvailable"
     * @param $_POST['availableId'] integer - available's id
     * @param $_POST['start'] string - start time of the period "2017-07-07 07:00:00"
     * @param $_POST['end'] string - end of the period "2017-07-07 09:00:00"
     * */
    case "editAvailable":
    	if (! isNumber($_POST['availableId'])) {
        	http_response_code(400);
            exit;
        }
    	if (! (isYmdHis($_POST['start']) && isYmdHis($_POST['end']))) {
        	http_response_code(400);
            exit;
        }
        echo RoomFunctions::getInstance()->editAvailableTime($_SESSION['id'], $_POST['availableId'], strtotime($_POST['start']), strtotime($_POST['end']));
        exit;
    /**
     * return a json serialized available time
     * can be different for different level users
     * e.g.
     * {
     *  "2017-07-07":
     *      [
     *          7, 8, 9, 10, 11, 12, 13, 14, 15
     *      ],
     *  "2017-07-08":
     *      [
     *          7, 8, 9, 10
     *      ]
     * }
     *
     * @param $_POST['function'] string "getAvailableTimeRelatedToARoomInDefaultPeriod"
     * @param $_POST['room'] integer - room id
     * @return json
     *
     * e.g. POST backend.php "function=getAvailableTimeRelatedToARoomInDefaultPeriod&room=1"
     * */
    case "getAvailableTimeRelatedToARoomInDefaultPeriod":
    	if (! isNumber($_POST['room'])) {
        	http_response_code(400);
            exit;
        }
        $room = RoomFunctions::getInstance()->getRoomById($_POST['room']);
        if ($room == null) {
            echo 'room id not found';
            exit;
        }
    //print_r($room);
    $startDay = date("Y-m-d", strtotime("+".(isset($_POST['startDay']) ? $_POST['startDay'] : "0")." days"));
        $endDay = date("Y-m-d", strtotime("+".(isset($_POST['endDay']) ? $_POST['endDay'] : "6")." days"));
        $currentDay = $startDay;
        $response = [];
        while ($currentDay <= $endDay) {
            //print_r($currentDay);
            if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() > 2) {
                $freeTime = RoomFunctions::getInstance()->getFreeTimeInAPeriodRelatedToARoom($currentDay, $room->getId());
            } else {
                $freeTime = RoomFunctions::getInstance()->getAdminFreeTimeInAPeriodRelatedToARoom($currentDay, $room->getId());
            }
            //print_r($freeTime);
            $response[$currentDay] = $freeTime;
            $currentDay = date("Y-m-d", strtotime($currentDay . " + 1 day"));
        }
    //print_r($response);
        echo json_encode($response);
        exit;
    /**
     * get current user's profile
     * return a json serialized class object User
     * @see application/datastructure/User.php
     *
     * @param $_POST['function'] string "getMe"
     * @return json
     *
     * e.g. POST backend.php "function=getMe"
     *
     * */
    case "getMe":
        echo json_encode(RoomFunctions::getInstance()->getUserById($_SESSION['id']));
        exit;
    /**
     * returns a json serialized
     * array of class Order
     * @see application/datastructure/Order.php
     *
     * @param $_POST['function'] string "getMyApplicationsIn10Days"
     * @return json
     *
     * e.g. POST backend.php "function=getMyApplicationsIn10Days"
     *
     * */
    case "getMyApplicationsIn10Days":
        echo json_encode(RoomFunctions::getInstance()->getAllLiveApplicationsByApplicantIdInAPeriod($_SESSION['id'],
            strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 24:00:00", strtotime("+10 days")))));
        exit;
    /**
     * return a json serialized
     * array of class Room
     * @see application/datastructure/Room.php
     *
     * @param $_POST['function'] string "getAllRooms"
     * @return json
     *
     * e.g. POST backend.php "function=getAllRooms"
     *
     * */
    case "getAllRooms":
        echo json_encode(RoomFunctions::getInstance()->getAllRooms());
        exit;
    /**
     * return a json serialized
     * array of class Available
     * to administrator
     * @see application/datastructure/Available.php
     *
     * @param $_POST['function'] string "getAllAvailablesInAPeriod"
     * @return json
     *
     * e.g. POST backend.php "function=getAllAvailablesInAPeriod"
     *
     * */
    case "getAllAvailablesInAPeriod":
        if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            echo "You are not permitted.";
            exit;
        }
        echo json_encode(RoomFunctions::getInstance()->getAllAvailableInAPeriod(strtotime(date("Y-m-d 00:00:00") . "+1 days"),
            strtotime(date("Y-m-d 23:59:00", strtotime("+7 days")))));
        exit;
    /**
     * return a json serialized
     * array of class User
     * @see application/datastructure/User.php
     *
     * @param $_POST['function'] string "getAllBlacklistedUsers"
     * @return json
     *
     * e.g. POST backend.php "function=getAllBlacklistedUsers"
     *
     * */
    case "getAllBlacklistedUsers":
        echo json_encode(RoomFunctions::getInstance()->getAllBlackListedUsers($_SESSION['id']));
        exit;
    /**
     * return a json serialized class User
     * @see application/datastructure/User.php
     *
     * @param $_POST['function'] string "getUserById"
     * @param $_POST['targetId'] integer - target id
     * @return json
     *
     * e.g. POST backend.php "funcion=getUserById&targetId=1"
     *
     * */
    case "getUserById":
    	if (! isNumber($_POST['targetId'])) {
        	http_response_code(400);
            exit;
        }
        if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            echo "You are not permitted.";
            exit;
        }
        echo json_encode(RoomFunctions::getInstance()->getUserById($_POST['targetId']));
        exit;
    /**
     * return a json serialized class Room
     * @see application/datastructure/Room.php
     *
     * @param $_POST['function'] string "getRoomById"
     * @param $_POST['roomId'] integer - target room id
     * @return json
     *
     * e.g. POST backend.php "function=getRoomById&roomId=1"
     *
     * */
    case "getRoomById":
    	if (! isNumber($_POST['roomId'])) {
        	http_response_code(400);
            exit;
        }
        echo json_encode(RoomFunctions::getInstance()->getRoomById($_POST['roomId']));
        exit;
    /**
     * return json serialized
     * array of all live orders
     * to an administrator
     * @see application/datastructure/User.php
     *
     * @param $_POST['function'] string "getAllLiveOrders"
     * @return json
     *
     * e.g. POST backend.php "function=getAllLiveOrders"
     *
     * */
    case "getAllLiveOrders":
        if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            echo "You are not permitted.";
            exit;
        }
        echo json_encode(RoomFunctions::getInstance()->getLegalOrderByTime(strtotime(date("Y-m-d 00:00:00",
            strtotime("-10 days"))), strtotime(date("Y-m-d 24:00:00", strtotime("+10 days")))));
        exit;
    /**
     *  return json resialized
     *  array of users
     *  to an administrator
     *  @see application/datastructure/User.php
     *
     *  @param $_POST['function'] string "queryUserByName"
     *  @param $_POST['name'] string "张三"
     *  @return json
     *
     *  e.g. POST backend.php "function=queryUserByName&name=张三"
     * */
    case "queryUserByName":
        if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            echo "You are not permitted.";
            exit;
        }
        echo json_encode(RoomFunctions::getInstance()->queryUserByName($_POST['name']));
        exit;
    /**
     * aim to initialize the program at the begining of the day
     *
     * @param $_POST['function'] string "initialize"
     * @void
     *
     * e.g. POST backend.php "function=initialize"
     * */
    case "initialize":
        $last = "0";

        $fp = fopen("/tmp/shahe-borrowroom-last.txt", "r");
        if ($fp) {
            $last = fread($fp, 1024);
            fclose($fp);
        }


        if ($last == null) {
            $last = date("d");
        } else {
            if ($last <> date("d")) {
                $last = date("d");
                $start = date("Y-m-d 00:00:00", strtotime("+3 days"));
                $end = date("Y-m-d 00:00:00", strtotime("+4 days"));
                foreach (RoomFunctions::getInstance()->getAllRooms() as $room) {
                    $available = RoomFunctions::getInstance()->getAllAvailableInAPeriodRelatedToARoom(strtotime($start), strtotime($end), $room->getId());
                    if (is_array($available) && count($available) > 0) {
                        $available = $available[0];
                        RoomFunctions::getInstance()->registerNewAvailableTime($available->getPerformer(), $available->getPerformer(), $available->getRoom(), strtotime(date("Y-m-d H:i:s", strtotime($available->getStart() . "+7 days"))), strtotime(date("Y-m-d H:i:s", strtotime($available->getEnd() . "+7 days"))));
                    } else {
                        RoomFunctions::getInstance()->registerNewAvailableTime(1, 1, $room->getId(), strtotime(date("Y-m-d 00:00:00", strtotime("+10 days"))), strtotime(date("Y-m-d 00:00:00", strtotime("+10 days"))));
                    }
                }
            }
        }

        $fp = fopen("/tmp/shahe-borrowroom-last.txt", "w");
        fwrite($fp, $last);
        fclose($fp);
        echo $last;
        exit;
    /**
     * default return
     *
     * */
    default:
        echo "not a vaild function";
        exit;
}
?>
