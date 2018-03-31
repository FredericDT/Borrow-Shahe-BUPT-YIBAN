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
 * Time: 23:05
 */

class Room implements JsonSerializable {
    private $id, $name;
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
    public function getId() {
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
?>