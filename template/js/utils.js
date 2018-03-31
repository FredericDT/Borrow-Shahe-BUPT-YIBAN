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
/* utils.js by FredericDT 2017-2018 */

function getRoomById(id) {
    $.ajax({
        method: "POST",
        url: "./../logic/backend.php",
        data: "function=getRoomById&roomId=" + id,
        async: false
    }).done(function (result) {
        window.temp = JSON.parse(result);
    });
    return window.temp;
}

function getAllRooms() {
    $.ajax({
        method: "POST",
        url: "./../logic/backend.php",
        data: "function=getAllRooms",
        async: false
    }).done(function (result) {
        window.temp = JSON.parse(result);
    });
    return window.temp;
}

function getMe() {
    $.ajax({
        method: "POST",
        url: "./../logic/backend.php",
        data: "function=getMe",
        async: false
    }).done(function (result) {
        window.temp = JSON.parse(result);
    });
    return window.temp;
}

function getUserById(id) {
    $.ajax({
        method: "POST",
        url: "./../logic/backend.php",
        data: "function=getUserById&targetId=" + id,
        async: false
    }).done(function (result) {
        window.temp = JSON.parse(result);
    });
    return window.temp;
}

function getYmd(date) {
    date = new Date(date.replace(/-/g, "/"));
    return date.getFullYear() + "-" + (date.getMonth() + 1 < 10 ? "0" : "") + (date.getMonth() + 1) + "-" + (date.getDate() < 10 ? "0" : "") + date.getDate();
}

function getHis(date) {
    date = new Date(date.replace(/-/g, "/"));
    return date.getHours() + ":" + (date.getMinutes() < 10 ? "0" : "") + date.getMinutes() + ":" + (date.getSeconds() < 10 ? "0" : "") + date.getSeconds();
}
