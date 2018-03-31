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
/* admin.php by Dimpurr, LsdsJy, FredericDT 2017-2018*/
?>
<div class="admin">
	<h3>可租借场所</h3>
	<ul class="admin_ul" id="rooms">
		<?php
        /*
            $rooms = RoomFunctions::getInstance()->getAllRooms();
            foreach($rooms as $room) {
                echo '<li>' . $room->getName() . '</li>';
            }
        */
        ?>
	</ul>
	<?php
if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() < 1) {
	echo '<h3>勤工助学时间段 <small style="font-weight: normal">如果当日没有值班时间，请设置为 0 到 0; 本设置自动按 7 天重复</small></h3>
	<form action="submitTime.php" method="post" class="work_time_f">
		<ul class="admin_ul work_time">';

            /*
                $availables = RoomFunctions::getInstance()->getAllAvailableInAPeriod(strtotime(date("Y-m-d 00:00:00") . "+1 days"), strtotime(date("Y-m-d 24:00:00", strtotime("+6 days"))));
                foreach ($availables as $available) {

                        <li>
                            负责人：<input type="text" name="wechat" placeholder="' . RoomFunctions::getInstance()->getUserById($available->getPerformer())->getName() . '"/>
			            </li>

                    echo '
	                    <li id="' . $available->getId() . '">
				            <h5>' . date("Y-m-d", strtotime($available->getStart())) . '</h5>
				            <span >' . RoomFunctions::getInstance()->getRoomById($available->getRoom())->getName(). '</span>
				            <span class="time_label">开始：</span>
				            <input type="number" name="start" min="0" max="23" value="' . date("G", strtotime($available->getStart())) . '" step="1" />
			            	<span class="time_label">结束：</span>
			            	<input type="number" name="end" min="0" max="23" value="' . date("G", strtotime($available->getEnd())) . '" step="1" />
			            	<input type="button" value="修改" onclick="editAvailable(' . $available->getId() . ');"/>
			            </li>
			        ';
                }
            */

	echo	'</ul>
	</form>';
}
	?>
	<h3>用户黑名单</h3>
	<ul class="admin_ul blacklist">
        <?php
        /*
            $banList = RoomFunctions::getInstance()->getAllBlackListedUsers($_SESSION['id']);
            foreach ($banList as $bannedUser) {
                echo '
                    <li>' . $bannedUser->getIdentifer() . ' ' . $bannedUser->getName() . '
			            <ul class="borrow_item_btn">
				            <li><a href="#" onclick=\'$.post("./../logic/backend.php", "function=pardonUser&targetId=' . $bannedUser->getId() . '", function(result) {if(result == 1) { location.reload();} else { alert(result);}})\'>移出</a></li>
			            </ul>
		            </li>
                ';
            }
        */
        ?>
	</ul>
	<h3>管理申请记录</h3>

	<div class="admin_tab">
		<label><input id="future" type="checkbox" value="future" checked onchange="updateFilter();"><span>显示预约</span></label> 
		<label><input id="expire" type="checkbox" value="expire" checked onchange="updateFilter();"><span>显示已完成记录</span></label> 
		<span class="admin_tab_title">&nbsp;&nbsp;按场地筛选</span>
		<select id="roomFilter" onchange="updateFilter();">
        	<option value="0" selected>全部</option>
    	</select>
	</div>

	<ul class="borrow_list">
        <?php
        /*
            $orders = RoomFunctions::getInstance()->getLegalOrderByTime(strtotime(date("Y-m-d 00:00:00", strtotime("-10 days"))), strtotime(date("Y-m-d 24:00:00", strtotime("+10 days"))));
            foreach ($orders as $order) {
                $user = RoomFunctions::getInstance()->getUserById($order->getApplicant());
                $room = RoomFunctions::getInstance()->getRoomById($order->getRoom());
                echo '
                    <li class="borrow_item">
			            <header>
				            <h2>' . $room->getName() . '</h2>
				            <h3>' . date("Y-m-d H:i - ", strtotime($order->getStart())) . date("H:i", strtotime($order->getEnd())) . '</h3>
			            </header>
			            <p>
				            申请人：<span class="borrow_item_type">' . $user->getName() . ' 电话 ' . $user->getContact() . '</span>
				            状态：<span class="borrow_item_type">' . ($order->getState() == '0' ? '有效' : ($order->getState() == '1' ? '已撤回' : '已取消')) . '</span>
			            </p>
			            <ul class="borrow_item_btn">
			    ';
                if (strtotime("now") > strtotime($order->getEnd())){
                    echo '
					        <li><a href="#" onclick=\'$.post("./../logic/backend.php", "function=reviewApplication&applicantId=' . $user->getId() . '&applicationId=' . $order->getId() . '", function(result) {if(result == 1) { location.reload();} else { alert(result);}})\'>移到黑名单</a></li>
			        ';
                } else {
                	echo '
					        <li><a href="#" onclick=\'$.post("./../logic/backend.php", "function=revokeApplication&applicantId=' . $user->getId() . '&applicationId=' . $order->getId() . '", function(result) {if(result == 1) { location.reload();} else { alert(result);}})\'>取消预定</a></li>
			        ';
                }
			    echo '
			            </ul>
		            </li>
                ';
            }
        */
        ?>
	</ul>

	<h3>管理用户权限</h3>
	输入姓名以查询相关用户：<input id="query_name" type="text" placeholder="姓名" />
	<a href="#user_queries" onclick='queryUserByName();'>查询</a>
	<ul id="user_queries">

	</ul>
</div>
<script src="js/admin.min.js"></script>
