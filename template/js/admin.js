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
/* admin.js by FredericDT 2017-2018 */

me = getMe();

function editAvailable(availableId) {
    date = $("li#" + availableId)[0].children[0].innerText;
    start = date + " " + $("li#" + availableId + " input[type=number]")[0].value + ":00:00";
    end = date + " " + $("li#" + availableId + " input[type=number]")[1].value + ":00:00";
    $.post("./../logic/backend.php", "function=editAvailable&availableId=" + availableId + "&start=" + start + "&end=" + end,
        function (result) {
            if (result == 1) {
                alert("编辑成功");
            } else {
                alert("目标不存在");
            }
        }
    );
}

roomsList = getAllRooms();
rooms = {};
for (i = 0; i < roomsList.length; i++) {
    rooms[roomsList[i]['id']] = roomsList[i];
}
//room list initialize
for (i = 0; i < roomsList.length; i++) {
    $("ul.admin_ul#rooms").append("<li>" + roomsList[i]['name'] + "</li>");
    $("select#roomFilter").append('<option value="' + roomsList[i]['id'] + '">' + roomsList[i]['name'] + '</option>');
}
//available list initialize
if (me['type'] < 1) {
    $.post("./../logic/backend.php", "function=getAllAvailablesInAPeriod", function (result) {
        json = JSON.parse(result);
        if (json) {
            for (i = 0; i < json.length; i++) {
                if (!(rooms[json[i]['room']])) {
                    continue;
                }
                $("ul.admin_ul.work_time").append("<li id=\"" + json[i]['id'] + "\">" +
                    "<h5>" + getYmd(json[i]['start']) + "</h5>" +
                    "<span >" + rooms[json[i]['room']]['name'] + " </span>" +
                    "<span class=\"time_label\">开始：</span>" +
                    "<input type=\"number\" name=\"start\" min=\"0\" max=\"23\" value=\"" + new Date(json[i]['start'].replace(/-/g, "/")).getHours() + "\" step=\"1\" />" +
                    "<span class=\"time_label\">结束：</span>" +
                    "<input type=\"number\" name=\"end\" min=\"0\" max=\"23\" value=\"" + new Date(json[i]['end'].replace(/-/g, "/")).getHours() + "\" step=\"1\" />" +
                    "<input type=\"button\" value=\"修改\" onclick=\"editAvailable(" + json[i]['id'] + ");\"/>" +
                    "</li>");
            }
        } else {
            alert("update availables error");
        }
    });
}

//blacklist initialize
$.post("./../logic/backend.php", "function=getAllBlacklistedUsers", function (result) {
    json = JSON.parse(result);
    if (json) {
        for (i = 0; i < json.length; i++) {
            $("ul.admin_ul.blacklist").append("<li id='" + json[i]['id'] + "'>" + json[i]['identifer'] + " " + json[i]['name'] +
                "<ul class=\"borrow_item_btn\">" +
                "<li><a href=\"#\" onclick=\"pardonUser(" + json[i]['id'] + ");\">移出</a></li>" +
                "</ul>" +
                "</li>");
        }
    } else {
        alert("getBlacklistError");
    }
});

//orders managements
all_orders = []
$.post("./../logic/backend.php", "function=getAllLiveOrders", function (result) {
    all_orders = json = JSON.parse(result);
    if (json) {
        for (i = 0; i < json.length; i++) {
            $("ul.borrow_list").append(
                '<li class="borrow_item" id="' + json[i]['id'] + '">' +
                '<header id="' + json[i]['room'] + '">' +
                '<h2>' + rooms[json[i]['room'].toString()]['name'] + '</h2>' +
                '<h3>' + getYmd(json[i]['start']) + ' ' + getHis(json[i]['start']) + " - " + getHis(json[i]['end']) + '</h3>' +
                '</header>' +
                '<p>' +
                '申请人：<span class="borrow_item_type">' + getUser(json[i]['applicant'])['name'] + ' 电话 ' + getUser(json[i]['applicant'])['contact'] + '</span>' +
                '理由：<span class="borrow_item_type">' + json[i]['reason'] + '</span>' +
                '状态：<span class="borrow_item_type">' + (json[i]['state'] == '0' ? '有效' : (json[i]['state'] == '1' ? '已撤回' : '已取消')) + '</span>' +
                '</p>' +
                '<ul class="borrow_item_btn">' +
                (new Date() > new Date(json[i]['end'].replace(/-/g, "/")) ? '<li><a href="#" onclick="reviewApplication(' + json[i]['applicant'] + ', ' + json[i]['id'] + ');">移到黑名单</a></li>' :
                    '<li><a href="#" onclick="cancelApplication(' + json[i]['id'] + ')">取消预定</a></li>') +
                '</ul>' +
                '</li>'
            );
        }
    } else {
        alert("getOrdersError");
    }
});

function pardonUser(id) {
    $.post("./../logic/backend.php",
        "function=pardonUser&targetId=" + id,
        function (result) {
            if (result == 1) {
                $("ul.admin_ul.blacklist li#" + id).remove();
            } else {
                alert(result);
            }
        }
    );
}

function reviewApplication(applicantId, orderId) {
    $.post("./../logic/backend.php",
        "function=reviewApplication&applicantId=" + applicantId + "&applicationId=" + orderId,
        function (result) {
            if (result == 1) {
                $("ul.borrow_list li#" + orderId).remove();
            } else {
                alert(result);
            }
        }
    );
}

function cancelApplication(id) {
    $.post("./../logic/backend.php",
        "function=revokeApplication&applicationId=" + id,
        function (result) {
            if (result == 1) {
                $("ul.borrow_list li#" + id).remove();
            } else {
                alert(result);
            }
        }
    );
}

window.users = {};

function getUser(id) {
    if (!window.users[id]) {
        user = getUserById(id);
        window.users[id] = user;
    }
    return window.users[id];
}

function updateUserType(id) {
    $.post("./../logic/backend.php",
        "function=updateUserType&targetId=" + id + "&type=2",
        function (result) {
            if (result == 1) {
                alert('已成功将该用户提升为管理员');
            } else {
                alert(result);
            }
        }
    );
}

function queryUserByName() {
    $.post("./../logic/backend.php",
        "function=queryUserByName&name=" + $('#query_name').val(),
        function (result) {
            if ((users = JSON.parse(result))) {
                users = JSON.parse(result);
                $("#user_queries").empty();
                for (var i = 0; i < users.length; i++) {
                    $("#user_queries").append(
                        '<li>' +
                        users[i].name +
                        '<a href="#user_queries" onclick="updateUserType(' + users[i].id + ');">设为管理员</a></li>'
                    );
                }
            } else {
                alert('不存在相关用户');
            }
        }
    );
}

function filtAndHide(roomId, show_expired, show_unexpired) {

    /*
        roomId: int
        show_expired, show_unexpired: boolean
    */
    function filt() {
        var orders = [];
        for (i = 0; i < all_orders.length; i++) {
            var order = all_orders[i], expired = false;
            if (new Date() > new Date(order.end.replace(/-/g, "/"))) {
                expired = true;
            }
            if ((roomId == 0 || order.room == roomId) && ((show_expired && expired) || (show_unexpired && !expired))) {
                orders.push(i);
            }
        }
        return orders;
    }

    orders = filt();
    $("li.borrow_item").hide();
    orders.forEach(function (i) {
        $("li.borrow_item:eq(" + i + ")").show();
    });
}

function updateFilter() {
    showExpired = $('input#expire')[0].checked;
    showFuture = $('input#future')[0].checked;
    roomId = $('select#roomFilter')[0].value;
    filtAndHide(roomId, showExpired, showFuture);
}
