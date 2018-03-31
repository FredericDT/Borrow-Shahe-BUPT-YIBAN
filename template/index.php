<!--
    BUPT YIBAN D&OM DEPARTMENT
	Build 2018-03-31 14:02:07
    Frontend @dimpurr http://dimpurr.com
    Backend @FredericDT http://me.fdt.onl
    Maintainer @lsdsjy, @dimpurr, @FredericDT
    ===
    「あなた見たのすべて、勤勉と汗の足跡です...」
-->
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
session_start();
date_default_timezone_set("Asia/ShangHai");
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
require("./../logic/availableRoomFunctions.php");

//initialize

if(! isset($_SESSION['id'])) {
    include("./../logic/YBAPI/config.php");
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $cfg['appCallback']);
}
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="HandheldFriendly" content="true">
	<title>北邮沙河校区 易班场地预约系统 | BUPT Yiban</title>
	<link rel="stylesheet" type="text/css" href="style.css">
    <script src="./../logic/jquery-3.2.1.min.js"></script>
    <script src="js/utils.min.js"></script>
    <script>
        $.post("./../logic/backend.php", "function=initialize");
        console.log("your id is <?php echo $_SESSION['id'];?>");
        //console.log("frontend Dimpurr, backend FredericDT, credit Henryzhao96.");
    </script>
</head>
<body><div class="page">
	<nav class="nav">
		<ul>
			<li class="nav_title"><a href="index.php">北邮场地预约 <small>沙河校区</small></a></li>
			<li><a href="index.php?page=my_borrow">我的预约</a></li>
			<?php

                if(RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() < 2) {
                    echo '<li><a href="index.php?page=admin">管理后台</a></li>';
                }

            ?>
		</ul>
	</nav>
	
	<?php
    if (isset($_GET['page'])) {
        switch ($_GET['page']) {
            case "my_borrow":
                include("my_borrow.php");
                break;
            case "admin":
                if (RoomFunctions::getInstance()->getUserById($_SESSION['id'])->getType() < 2) {
                    include("admin.php");
                    break;
                }
            default:
                include("new_borrow.php");
                break;
        }
    } else {
        include("new_borrow.php");
    }
     ?>
	<!-- <script src="//unpkg.com/vue"></script> -->
</div>
</body>
</html>