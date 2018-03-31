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
/* my_borrow.js by FredericDT 2017-2018 */

$.post("./../logic/backend.php", "function=getMyApplicationsIn10Days", function (result) {
    json = JSON.parse(result);
    if (json) {
        for (i = 0; i < json.length; i++) {
            $("ul.borrow_list").append("<li class=\"borrow_item\" id=\"application_" + json[i]['id'] + "\">" +
                "<header>" +
                "<h2>" + window.getRoomById(json[i]['room'])['name'] + "</h2>" +
                "<h3>" + json[i]['start'] + " - " + json[i]['end'] + "</h3>" +
                "</header>" +
                "<p>状态：<span class=\"borrow_item_type\">" + (json[i]['state'] == '0' ? '有效' : (json[i]['state'] == '1' ? '已撤回' : '已取消')) + "</span></p>" +
                "<ul class=\"borrow_item_btn\">" +
                "<li><a href=\"#\" onclick=\"revokeApplication(" + json[i]['id'] + ")\">申请取消</a></li>" +
                "</ul>" +
                "</li>");
        }
    } else {
        alert("getApplicationsError");
    }
});

function revokeApplication(id) {
    $.post("./../logic/backend.php", "function=revokeApplication&applicationId=" + id,
        function (result) {
            if (result == 1) {
                $("li.borrow_item#application_" + id).remove();
            } else {
                alert(result);
            }
        });
}