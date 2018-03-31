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
/* new_borrow.js by Dimpurr, LsdsJy, FredericDT 2017-2018 */

at_tds = document.querySelectorAll(".available_t td");
radio_longs = document.querySelectorAll("input[name='long']");
time_mark = document.getElementsByClassName("time_mark")[0];
time_mark_value = "";
room_radios = document.querySelectorAll(".new_form input[name='room']");

me = getMe();
rooms = getAllRooms();

function clear_mark() {
    for (i = 0; i < at_tds.length; i++) {
        if (at_tds[i].innerHTML && (at_tds[i].className != "disable") && (at_tds[i].className != "weekday")) {
            at_tds[i].className = "";
        }
    }
}

function radio_onclick() {
    for (i = 0; i < radio_longs.length; i++) {
        radio_longs[i].onclick = function () {
            marked_time = $(".time_selected")[0];
            mark_change(marked_time, marked_time.parentNode.childNodes[1].attributes["name"].value, marked_time.innerHTML, this.innerHTML);
        }
    }
}

function mark_change(td, weekday, time) {
    clear_mark();
    if (radio_longs[1].checked) {
        if (td.nextElementSibling.className == "disable") {
            alert("此时间无效！");
        } else {
            td.className = "time_selected";
            td.nextElementSibling.className = "time_selected";
            time_mark_value = weekday + ", " + time + ", " + "2";
            time_mark.value = weekday + " " + time + ":00";
        }
    } else {
        td.className = "time_selected";
        time_mark_value = weekday + ", " + time + ", " + "1";
        time_mark.value = weekday + " " + time + ":00";
    }
    time_mark = document.getElementsByClassName("time_mark")[0];
    time_mark_value = time_mark_value.split(",");
    radio_onclick();
}

var r = -1;

function updateContactPhone() {
    if ($("input[type=number]")[0].value == me['contact']) {
        return 1;
    }
    $.post("./../logic/backend.php", "function=updateContact&contact=" + $("input[type=number]")[0].value,
        function (result) {
            if (result == 1) {
                console.log("个人信息保存success");
                r = 1;
                // location.reload();
            } else {
                console.log("个人信息保存failed");
                alert("联系信息保存失败");
                r = 0;
            }
        }
    );
    return r;
}

function submitApplication() {
    updateContactPhoneResult = updateContactPhone();
    if (updateContactPhoneResult == 1) {
        roomId = $("form")[0].id;
        $.post("./../logic/backend.php", "function=submitApplication&time_mark=" +
            $("form#" + roomId + " input")[0].value + "&long=" + $("form#" + roomId + " input:checked")[1].value +
            "&room=" + roomId + "&borrow_reason=" + $("input[name=borrow_reason]")[0].value,
            function (result) {
                if (result == 1) {
                    node = $("td.time_selected");
                    clear_mark();
                    node.addClass("disable");
                    updateSelectableButton();
                    alert("预约成功！请按照预约时间前往预定场所\n您的预约可能由于办公需要被取消，请关注预约状态！\n为方便大家使用场地，请在使用场地完毕后，将场地布置恢复为借用前格局");
                } else {
                    alert("借用失败！无效的时间段申请，未填写借用理由或者用户状态非法");
                }
            }
        );
    } else {
        alert('请检查下方个人信息和网络连接并重试');
    }
}

function updateAvailableTime(roomId) {
    $.post("./../logic/backend.php", "function=getAvailableTimeRelatedToARoomInDefaultPeriod&room=" + roomId,
        function (result) {
            if (JSON.parse(result)) {
                json = JSON.parse(result);
                clear_mark();
                $("form.new_form")[0].id = roomId;
                for (i = 0; i < $('tr td.weekday').length; i++) {
                    day = $('tr td.weekday')[i].attributes["name"].value;
                    for (j = 0; j < $('tr:eq(' + i + ') td:gt(0)').length; j++) {
                        node = $('tr:eq(' + i + ') td:eq(' + (j + 1) + ')');
                        //console.log(day);
                        if (json[day].indexOf(parseInt(node[0].innerHTML)) > -1) {
                            node.removeClass("disable");
                        } else {
                            node.addClass("disable");
                        }
                    }
                }
                updateSelectableButton();
                for (i = 0; i < room_radios.length; i++) {
                    room_radio = room_radios[i];
                    room_radio.prop("checked", false);
                }
                $('p#roomPool input[value=' + roomId + ']').prop("checked", true);
            } else {
                console.log("刷新可用时间失败，请刷新页面");
            }
        }
    );
}

all_orders = [];
$.post("./../logic/backend.php", "function=getAllLiveOrders", function (result) {
    all_orders = json = JSON.parse(result);
});

function findBorrower(time, room) {
    var info = {};
    for (var i = 0; i < all_orders.length; i++) {
        var order = all_orders[i];
        if (order.room.toString() == room && time >= new Date(order.start.replace(/-/g, "/")) && time < new Date(order.end.replace(/-/g, "/"))) {
            info.username = getUserById(order.applicant).name;
            info.start = getHis(order.start);
            info.end = getHis(order.end);
            return info;
        }
    }
}

function updateSelectableButton() {
    for (i = 0; i < at_tds.length; i++) {
        at_td = at_tds[i];
        if (at_td.innerHTML && (at_td.className != "disable") && (at_td.className != "weekday")) {
            at_td.removeAttribute("tooltip");
            at_td.onclick = function () {
                mark_change(this, this.parentNode.childNodes[1].attributes["name"].value, this.innerHTML);
            };
        } else {
            at_td.onclick = null;
            if (at_td.className == "disable") {
                var date = at_td.parentNode.childNodes[1].attributes["name"].value, time = at_td.innerHTML;
                var borrower = findBorrower(new Date((date + ' ' + time).replace(/-/g, "/")), $("form.new_form")[0].id);
                if (borrower) {
                    at_td.setAttribute("tooltip", borrower.username + " " + borrower.start + "-" + borrower.end);
                }
            }
        }
    }
}


//update available room list
for (i = 0; i < rooms.length; i++) {
    $("p#roomPool").append('<label ><input name = "room" type = "radio" value = "' +
        rooms[i]['id'] + '" ' + (i == 0 ? "checked" : "") + ' /><span onclick="updateAvailableTime(' + rooms[i]['id'] + ');" id="' + rooms[i]['id'] + '" > ' + rooms[i]['name'] + ' </span ></label >');
}

$("p#roomPool > label:first > span").click();
//updateAvailableTime(1);
updateSelectableButton();

//initialize phone value
$("input[name=phone]")[0].value = me['contact'];
/*
for (i = 0; i < room_radios.length; i++) {
room_radio = room_radios[i];
room_radio.onclick = function() {
/*
window.location.href = window.location.href.split("?")[0] + "?room=" + room_radio.value;
*
updateAvailableTime(room_radio.value);
}
}
*/