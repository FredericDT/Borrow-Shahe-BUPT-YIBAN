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
<div class="my_borrow">
	<ul class="borrow_list">
		<?php
        /*
            $allApplications = RoomFunctions::getInstance()->getAllLiveApplicationsByApplicantIdInAPeriod($_SESSION['id'], strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 24:00:00", strtotime("+10 days"))));
            //print_r($allApplications);
            foreach ($allApplications as $app) {
                echo '
                    <li class="borrow_item">
			            <header>
				            <h2>' . RoomFunctions::getInstance()->getRoomById($app->getRoom())->getName() . '</h2>
				            <h3>' . date("Y年m月d日 H:i", strtotime($app->getStart())) . ' - ' . date("Y年m月d日 H:i", strtotime($app->getEnd())) . '</h3>
			            </header>
			            <p>状态：<span class="borrow_item_type">' . ($app->getState() == '0' ? '有效' : ($app->getState() == '1' ? '已撤回' : '已取消')) . '</span></p>
			            <ul class="borrow_item_btn">
					        <li><a href="#" onclick=\'$.post("./../logic/backend.php", "function=revokeApplication&applicationId=' . $app->getId() . '", function(result) {if(result == 1) { location.reload();} else { alert(result);}})\'>申请取消</a></li>
			            </ul>
		            </li>
		        ';
            }
        */
        ?>
	</ul>
    <script src="js/my_borrow.min.js"></script>
</div>