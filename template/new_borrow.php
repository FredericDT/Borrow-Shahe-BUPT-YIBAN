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
/* index.php by Dimpurr, FredericDT 2017-2018*/
?>
<?php
function getDayOfWeek($i) {
    return '星期' . ['日','一','二','三','四','五','六']
        [
            (int) $i
        ]
        ;
}
?>
<pre style="display: none; background: white; width: 100%; border-color: grey;">
临时公告：目前选择时间下午13-23点时段错后半小时
例如：'13' 对应时段为13:30-14:30时段
</pre>
<div class="new_borrow">
	<ul class="borrow_list">
        <?php
        /*
            $rooms = RoomFunctions::getInstance()->getAllRooms();
            foreach ($rooms as $room) {
                echo '
                        <form class="new_form" method="post" id="' . $room->getId() . '">
                            <table class="available_t">
                    ';
                $startDay = date("Y-m-d", strtotime("+5 days"));
                $endDay = date("Y-m-d", strtotime("+10 days"));
                $currentDay = $startDay;
                while ($currentDay <= $endDay) {
                    if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() > 2) {
                        $freeTime = RoomFunctions::getInstance()->getFreeTimeInAPeriodRelatedToARoom($currentDay, $room->getId());
                    } else {
                        $freeTime = RoomFunctions::getInstance()->getAdminFreeTimeInAPeriodRelatedToARoom($currentDay, $room->getId());
                    }
                    echo '
                                <tr>
                                    <td class="weekday" name="' . $currentDay . '">' . $currentDay . '</td>
                    ';
                    for ($i = 7; $i < 24; $i++) {
                        echo '<td';
                            if (! in_array($i, $freeTime)) {
                                echo ' class="disable"';
                            }
                        echo '>' . $i .'</td>';
                    }
                    echo '
                                </tr>
                    ';
                    $currentDay = date("Y-m-d", strtotime($currentDay . " + 1 day"));
                }
        */
        echo '
                        <form class="new_form" method="post" id="1" >
                            <table class="available_t">
                    ';
        for ($i = 0 ; $i < 7; $i++) {
            echo '
                                <tr>
<td class="weekday" name="' . date("Y-m-d", strtotime("+" . $i . " days")) . '">' .
                getDayOfWeek(date("w", strtotime('+' . $i . ' days'))) . ' ' . 
                date("Y-m-d", strtotime("+" . $i . " days")) . '</td>
                    ';
            for ($j = 7; $j < 24; $j++) {
                echo '<td';
                echo ' class="disable"';
                echo '>' . $j. ($j >= 12 ? ':30' : ':00') .'</td>';
            }
            echo '
                                </tr>
                    ';
        }

                echo '
                            </table>
                            <input class="time_mark" name="time_mark" type="hidden" value="" />
                            <p class="note">备注：灰色为无人值守或已占用时间，无法预约</p>
                            <li class="borrow_item">
                                <header>
                                    <h2>申请预约场地</h2>
                                    <h3>点击上方时间表 选择预约时间</h3>
                                </header>
                                <p id="roomPool">地点
                                ';/*
        $rooms = RoomFunctions::getInstance()->getAllRooms();
                                foreach ($rooms as $room) {
                                    echo'<label ><input name = "room" type = "radio" value = "' . $room->getId() . '" checked /><span > ' . $room->getName() . ' </span ></label >';
                                }*/
                                echo '
                                </p>
                                <p>时长
                                    <label><input name="long" type="radio" value="1" checked/><span>一小时</span></label>
                                    <label><input name="long" type="radio" value="2" /><span>二小时</span></label>
                                </p>
                                <!-- <p>开始时间
                                    <input type="number" name="start_time" min="6" max="23" value="10" step="1" /> 点
                                </p> -->
                                <p class="my_info" style="text-align: left">申请理由
                                    <input type="text" name="borrow_reason" placeholder="填写申请说明" />
                                </p>
                            </li>
                            <div class="my_info">
                                <input type="button" value="提交申请" onclick="submitApplication();"/>
                            </div>
                        </form>
                ';
            //}
        ?>
	</ul>
	<form class="my_info my_info_white" method="post" id="my-info" autocomplete >
		<h3>申请前请确保个人信息非空</h3>
		<input type="number" name="phone" placeholder="电话" value="" required/>
		<!--
        <input type="email" name="email" placeholder="邮箱" />
		<input type="text" name="wechat" placeholder="微信" />
		<input type="number" name="qq" placeholder="QQ" />
		-->
		<input type="submit" value="保存个人信息" onclick="updateContactPhone();"/>
	</form>
    <script src="js/new_borrow.min.js"></script>
</div>