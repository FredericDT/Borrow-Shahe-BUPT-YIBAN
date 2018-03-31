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
 * Time: 23:07
 */

class Order implements JsonSerializable {

    private $id, $applicant, $timestamp, $room, $start, $end, $reason, $state, $review;

    const STATE_OK = 0;
    const STATE_REVOKED = 1;
    const STATE_CANCELLED_BY_ADMIN = 2;

    const REVIEW_OK = 0;
    const REVIEW_GUGUGUED = 1;

    public function __construct($id, $applicant, $timestamp, $room, $start, $end, $reason, $state, $review) {
        $this->id = $id;
        $this->applicant = $applicant;
        $this->timestamp = $timestamp;
        $this->room = $room;
        $this->start = $start;
        $this->end = $end;
        $this->reason = $reason;
        $this->state = $state;
        $this->review = $review;
    }
    public function getId() {
        return $this->id;
    }
    public function getApplicant() {
        return $this->applicant;
    }
    public function getTimestamp() {
        return $this->timestamp;
    }
    public function getRoom() {
        return $this->room;
    }
    public function getStart() {
        return $this->start;
    }
    public function getEnd() {
        return $this->end;
    }
    public function getReason() {
        return $this->reason;
    }
    public function getState() {
        return $this->state;
    }
    public function getReview() {
        return $this->review;
    }
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}