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
 * Date: 27/09/2017
 * Time: 15:31
 */

    include("mysqlConfig.php");
    class mysql{
        public $dbh;
        private static $instance;
        private function __construct() {
            global $db_database, $db_host, $db_user, $db_password, $db_port;
            try {
                $this->dbh = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_database;charset=utf8",
                    $db_user,$db_password,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
            } catch (PDOException $ex) {
                mysql::$instance = null;
                die("Unable Connect To DataBase");
            }
        }
        public static function getInstance() {
            if (mysql::$instance == null) {
                mysql::$instance = new mysql();
            }
            return mysql::$instance;
        }
    }

?>