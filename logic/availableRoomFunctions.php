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
 * Time: 15:30
 */

require("database/mysqlBase.php");
require("datastructure/Available.php");
require("datastructure/Order.php");
require("datastructure/Room.php");
require("datastructure/User.php");

class RoomFunctions {
    private static $timeLimitInHours = 2;
    private static $instance;
    private $mysqlBase;

    private function __construct($mysqlBase) {
        date_default_timezone_set("Asia/ShangHai");
        $this->mysqlBase = $mysqlBase;
    }

    /**
     *
     * @param
     * @return RoomFunctions - a instance of this class
     *
     * */
    public static function getInstance() {
        if (RoomFunctions::$instance == null) {
            if (mysql::getInstance() <> null)
                RoomFunctions::$instance = new RoomFunctions(mysql::getInstance());
        }
        return RoomFunctions::$instance;
    }

    /**
     *
     * @param $applicant integer - id of the applicant
     * @return string - error messages
     * @return array of Order - all live applications related to the applicant
     *
     * */
    public function getAllLiveApplicationsByApplicantId($applicant) {
        if ($applicant == null) {
            return "applicantId must be provided";
        }
        $userApplicant = $this->getUserById($applicant);
        if ($userApplicant == null) {
            return "performer not found";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where applicant = :id AND state = :state AND review = :review");
            $query->bindValue(':id', $userApplicant->getId());
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':review', Order::REVIEW_OK, PDO::PARAM_INT);
            if ($query->execute()) {
                $results = $query->fetchAll(PDO::FETCH_ASSOC);
                $applications = array();
                foreach ($results as $result) {
                    array_push($applications, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
                }
                return $applications;
            } else {
                return "execute fail";
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $applicant integer - id of the applicant
     * @param $start timestamp
     * @param $end timestamp
     * @return string - error messages
     * @return array of Order - all future live applications related to the applicant
     *
     * */
    public function getAllFutureLiveApplicationsByApplicantId($applicant, $start) {
        if ($applicant == null || $start == null) {
            return "illegal arguments";
        }
        $userApplicant = $this->getUserById($applicant);
        if ($userApplicant == null) {
            return "performer not found";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where applicant = :id AND start >= :start AND state = :state AND review = :review");
            $query->bindValue(':id', $userApplicant->getId());
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':review', Order::REVIEW_OK, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $applications = array();
            foreach ($results as $result) {
                array_push($applications, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
            }
            return $applications;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $applicant integer - id of the applicant
     * @param $start timestamp
     * @param $end timestamp
     * @return string - error messages
     * @return array of Order - all live applications related to the applicant
     *
     * */
    public function getAllLiveApplicationsByApplicantIdInAPeriod($applicant, $start, $end) {
        if ($applicant == null || $start == null || $end == null) {
            return "illegal arguments";
        }
        $userApplicant = $this->getUserById($applicant);
        if ($userApplicant == null) {
            return "performer not found";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where applicant = :id AND start >= :start AND end <= :end AND state = :state AND review = :review");
            $query->bindValue(':id', $userApplicant->getId());
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':review', Order::REVIEW_OK, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $applications = array();
            foreach ($results as $result) {
                array_push($applications, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
            }
            return $applications;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $applicant integer - id of the applicant
     * @param $start timestamp
     * @param $end timestamp
     * @return string - error messages
     * @return array of Order - all states applications related to the applicant
     *
     * */

    public function getAllStatesApplicationsByApplicantIdInAPeriod($applicant, $start, $end) {
        if ($applicant == null || $start == null || $end == null) {
            return "illegal arguments";
        }
        $userApplicant = $this->getUserById($applicant);
        if ($userApplicant == null) {
            return "performer not found";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where applicant = :id AND start >= :start AND end <= :end");
            $query->bindValue(':id', $userApplicant->getId());
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $applications = array();
            foreach ($results as $result) {
                array_push($applications, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
            }
            return $applications;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $applicant User - the applicant
     * @return boolean - can request more room
     *
     * */
    public function canRequestMoreRoom($applicant) {
        if ($applicant == null) {
            return false;
        }
        $userApplicant = $this->getUserById($applicant);
        if ($userApplicant == null) {
            return false;
        }
        if ($userApplicant->getContact() == "" || $userApplicant->getContact() == null) {
            return false;
        }
        if ($userApplicant->getCredit() < User::DEFAULT_CREDIT) {
            return false;
        }
        if ($userApplicant->getType() < User::TYPE_REVIEWER) {
            return true;
        }
        if (count($this->getAllFutureLiveApplicationsByApplicantId($userApplicant->getId(), strtotime("now"))) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * @param $operator integer - id of the operator
     * @param $applicationId integer - id of the application
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function cancelRoomApplication($operator, $applicationId) {
        if ($operator == null || $applicationId == null) {
            return "illegal arguments";
        }
        $application = $this->getOrderById($applicationId);
        if ($application == null) {
            return "applicationId not found";
        }
        $userOperator = $this->getUserById($operator);
        if ($userOperator == null) {
            return "operatorId not found";
        }
        if ($application->getState() > Order::STATE_OK) {
            return "this application is already cancelled";
        }
        if (!$application->getReview() == null) {
            return "this application is already reviewed";
        }
        if (!($application->getApplicant() == $userOperator->getId())) {
            if ($userOperator->getType() > User::TYPE_COUNSELOR) {
                return "not permitted";
            } else {
                try {
                    $query = $this->mysqlBase->dbh->prepare("update orders set state = :state where id = :id");
                    $query->bindValue(':state', Order::STATE_CANCELLED_BY_ADMIN);
                    $query->bindValue(':id', $application->getId());
                    if ($query->execute()) {
                        return true;
                        //return $this->updateUserCredit($this->getOrderById($applicationId)->getApplicant(), 10);
                    }
                    return false;
                } catch (PDOException $e) {
                    die($e->getMessage());
                }
            }
        } else {
            try {
                $query = $this->mysqlBase->dbh->prepare("update orders set state = :state where id = :id");
                $query->bindValue(':state', Order::STATE_REVOKED, PDO::PARAM_INT);
                $query->bindValue(':id', $application->getId());
                if ($query->execute()) {
                    return true;
                    //return $this->updateUserCredit($this->getOrderById($applicationId)->getApplicant(), 5);
                }
                return false;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     *
     * @param $applicant integer - id of the applicant
     * @param $roomId integer - id of the room
     * @param $start timestamp - timestamp of the start of the period
     * @param $end timestamp - timestamp of the end of the period
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function submitRoomApplication($applicant, $roomId, $start, $end, $reason) {
        if ($applicant == null) {
            return "applicantId must be provided";
        }
        $userApplicant = $this->getUserById($applicant);
        if ($userApplicant == null) {
            return "performer not found";
        }
        if ($roomId == null) {
            return "illegal arguments";
        }
        if ($start == null || $end == null) {
            return "a time must be provided";
        }
        if ($reason == "" || $reason == null) {
            return "reason must be provided";
        }
        if ($end <= $start) {
            return "illegal time";
        }
        if ($end <= date("Y-m-d H:i:s")) {
            return "illegal time";
        }
        if ($end - $start > 3600 * RoomFunctions::$timeLimitInHours) {
            return "time limit reached";
        }
        $room = $this->getRoomById($roomId);
        if ($room == null) {
            return "room not found";
        }
        //try submit
        if (!$this->canRequestMoreRoom($userApplicant->getId())) {
            return "please check your state";
        }
        if (!$this->isThisTimeFreeFromOrders($start, $end, $roomId)) {
            return "blocked by another application";
        }
        if ($userApplicant->getType() > User::TYPE_COUNSELOR) {
            if (!$this->isThisTimeAvailable($start, $end)) {
                return "this time not available";
            }
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("INSERT INTO orders (id, timestamp, applicant, room, start, end, reason, state, review) VALUES
(NULL, CURRENT_TIMESTAMP , :applicant, :room, :start, :end, :reason, :state, :review)");
            $query->bindValue(':applicant', $userApplicant->getId());
            $query->bindValue(':room', $room->getId());
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':reason', $this->mysqlBase->dbh->quote($reason));
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':review', Order::REVIEW_OK, PDO::PARAM_INT);
            if ($query->execute()) {
                return true;
                //return $this->updateUserCredit($userApplicant->getId(), -10);
            }
            return false;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    private function isThisTimeAvailable($start, $end) {
        if ($start == null || $end == null) {
            return false;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from available where start <= :start AND end >= :end AND state <= :state");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Available::STATE_OK, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return false;
    }

    private function isThisTimeFreeFromOrders($start, $end, $roomId) {
        if ($start == null || $end == null) {
            return false;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where ((start >= :start AND start < :end) OR (end > :start AND end <= :end)) AND state <= :state AND room = :room");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':room', $roomId);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return false;
    }

    /**
     *
     * @param $operator integer - id of the operator
     * @param $applicationId integer - id of the target application
     * @param $state integer - 0 for ok, 1 for illegal
     * @return string - error message
     * @return boolean - query state
     *
     * */
    public function reviewRoomApplication($operator, $applicationId, $review) {
        if ($operator == null) {
            return "operatorId must be provided";
        }
        $userOperator = $this->getUserById($operator);
        if ($userOperator == null) {
            return "operator not found";
        }
        if ($userOperator->getType() > User::TYPE_REVIEWER) {
            return "operator not permitted";
        }
        if ($applicationId == null) {
            return "applicationId must be provided";
        }
        if ($review === null) {
            return "state must be provided";
        }
        $creditAmout = $review == 0 ? 0 : -100;
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where id = :id");
            $query->bindValue(':id', $applicationId);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) == 1) {
                $application = new Order($results[0]['id'], $results[0]['applicant'], $results[0]['timestamp'], $results[0]['room'], $results[0]['start'], $results[0]['end'], $results[0]['reason'], $results[0]['state'], $results[0]['review']);
                $results = $this->getRelatedAvailableByTime(strtotime($application->getStart()), strtotime($application->getEnd()));
                if (!(count($results) == 1)) {
                    return "IMPORTANT!! review illegal";
                }
                if (strtotime($application->getEnd()) - strtotime(date("Y-d-m H:i:s")) <= 0) {
                    return "you can not review an application before its end";
                }
                if ($application->getReview()) {
                    return "this application is already reviewed";
                }
                if ($results[0]->getPerformer() == $operator) {
                    $query = $this->mysqlBase->dbh->prepare("update orders set review = :review where id = :id");
                    $query->bindValue(':review', $review);
                    $query->bindValue(':id', $application->getId());
                    if ($query->execute()) {
                        return true;
                        //return $this->updateUserCredit($this->getOrderById($applicationId)->getApplicant(), $creditAmout);
                    }
                    return false;
                } elseif ($userOperator->getType() < 3) {
                    $query = $this->mysqlBase->dbh->prepare("update orders set review = :review where id = :id");
                    $query->bindValue(':review', $review);
                    $query->bindValue(':id', $application->getId());
                    if ($query->execute()) {
                        return true;
                        //return $this->updateUserCredit($this->getOrderById($applicationId)->getApplicant(), $creditAmout);
                    }
                    return false;
                } else {
                    return "you are not permitted";
                }
            } else {
                return "applicationId not found";
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        //reviewRoomState
    }

    public function adminReviewRoomApplication($operator, $applicationId, $review) {
        if ($operator == null) {
            return "operatorId must be provided";
        }
        $userOperator = $this->getUserById($operator);
        if ($userOperator == null) {
            return "operator not found";
        }
        if ($userOperator->getType() > User::TYPE_COUNSELOR) {
            return "operator not permitted";
        }
        if ($applicationId == null) {
            return "applicationId must be provided";
        }
        if ($review === null) {
            return "state must be provided";
        }
        $creditAmout = $review == 0 ? 0 : -100;
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where id = :id");
            $query->bindValue(':id', $applicationId);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) == 1) {
                $application = new Order($results[0]['id'], $results[0]['applicant'], $results[0]['timestamp'], $results[0]['room'], $results[0]['start'], $results[0]['end'], $results[0]['reason'], $results[0]['state'], $results[0]['review']);
                if (strtotime($application->getEnd()) > strtotime("now")) {
                    return "you can not review an application before its end";
                }
                if ($application->getReview()) {
                    return "this application is already reviewed";
                }
                $query = $this->mysqlBase->dbh->prepare("update orders set review = :review where id = :id");
                $query->bindValue(':review', $review);
                $query->bindValue(':id', $application->getId());
                if ($query->execute()) {
                    return true;
                    //return $this->updateUserCredit($this->getOrderById($applicationId)->getApplicant(), $creditAmout);
                }
                return false;
            } else {
                return "applicationId not found";
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        //reviewRoomState
    }

    /**
     *
     * @param $operator integer - id of the operator
     * @param $performer integer - id of the reviewer (who in charge of the room during the period)
     * @param $roomId integer
     * @param $start timestamp
     * @param $end timestamp
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function registerNewAvailableTime($operator, $performer, $roomId, $start, $end) {
        if ($operator == null) {
            return "operatorId must be provided";
        }
        $userOperator = $this->getUserById($operator);
        if ($userOperator == null) {
            return "operator not found";
        }
        if ($userOperator->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "operator not permitted";
        }
        if ($performer == null) {
            return "performerId must be provided";
        }
        $userPerformer = $this->getUserById($performer);
        if ($userPerformer == null) {
            return "performer not found";
        }
        if ($userPerformer->getType() > User::TYPE_REVIEWER) {
            return "performer not permitted";
        }
        $room = $this->getRoomById($roomId);
        if ($room == null) {
            return "room not found";
        }
        if ($start > $end) {
            return "start time > end time";
        }
        //add to table 'available'
        try {
            $results = $this->getAllAvailableInAPeriodRelatedToARoom($start, $end, $roomId);
            if (count($results) == 0) {
                $query = $this->mysqlBase->dbh->prepare("INSERT INTO available (id, timestamp, performer, room, start, end, state) VALUES
(NULL, CURRENT_TIMESTAMP, :performer, :room, :start, :end, :state)");
                //INSERT INTO `available` (`id`, `timestamp`, `performer`, `room`, `start`, `end`, `state`) VALUES (NULL, CURRENT_TIMESTAMP, '1', '1', '2017-09-29 08:00:00', '2017-09-29 23:00:00', '0')
                $query->bindValue(':performer', $userPerformer->getId());
                $query->bindValue(':room', $room->getId());
                $query->bindValue(':start', date("Y-m-d H:i:s", $start));
                $query->bindValue(':end', date("Y-m-d H:i:s", $end));
                $query->bindValue(':state', Available::STATE_OK, PDO::PARAM_INT);
                return $query->execute();
            } else {
                return "available time already existed";
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - id of the application
     * @return Order - the requested order, null for not founded
     *
     * */
    public function getOrderById($id) {
        if ($id == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where id = :id");
            $query->bindValue(':id', $id);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) == 1) {
                return new Order($results[0]['id'], $results[0]['applicant'], $results[0]['timestamp'], $results[0]['room'], $results[0]['start'], $results[0]['end'], $results[0]['reason'], $results[0]['state'], $results[0]['review']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    /**
     *
     * @param $start timestamp - start for a peroid
     * @param $end timestamp
     * @return Order - the requested order, null for not founded
     *
     * */
    /*
    public function getOrderByTime($start, $end) {
        if ($start == null || $end == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where start >= :start AND end <= :end AND state <= :state");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', RoomFunctions::$numZero);
            if (! $query->execute()) {
                return null;
            }
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $orders = array();
            foreach ($results as $result) {
                array_push($orders, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
            }
            return $orders;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    */
    public function getLegalOrderByTime($start, $end) {
        if ($start == null || $end == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where start >= :start AND end <= :end AND state <= :state AND review <= :review ORDER BY start");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':review', Order::REVIEW_OK, PDO::PARAM_INT);
            if (!$query->execute()) {
                return null;
            }
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $orders = array();
            foreach ($results as $result) {
                array_push($orders, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
            }
            return $orders;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $start timestamp - start for a peroid
     * @param $end timestamp
     * @param $roomId integer
     * @return Order - the requested order, null for not founded
     *
     * */
    public function getOrderByTimeRelatedToARoom($start, $end, $roomId) {
        if ($start == null || $end == null || $roomId == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from orders where start >= :start AND end <= :end AND state <= :state AND room = :roomId");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Order::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':roomId', $roomId);
            if (!$query->execute()) {
                return null;
            }
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $orders = array();
            foreach ($results as $result) {
                array_push($orders, new Order($result['id'], $result['applicant'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['reason'], $result['state'], $result['review']));
            }
            return $orders;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $start timestamp - start for a peroid
     * @param $end timestamp
     * @return Available - the requested 'available', null for not founded
     *
     * */
    public function getRelatedAvailableByTime($start, $end) {
        if ($start == null || $end == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from available where start <= :start AND end >= :end AND state <= :state");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Available::STATE_OK, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $availables = array();
            foreach ($results as $result) {
                array_push($availables, new Available($result['id'], $result['performer'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['state']));
            }
            return $availables;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $start timestamp - start for a peroid
     * @param $end timestamp
     * @return Available - the requested 'available', null for not founded
     *
     * */
    public function getAllAvailableInAPeriod($start, $end) {
        if ($start == null || $end == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from available where start >= :start AND end <= :end AND state <= :state");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Available::STATE_OK, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $availables = array();
            foreach ($results as $result) {
                array_push($availables, new Available($result['id'], $result['performer'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['state']));
            }
            return $availables;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $start timestamp - start for a peroid
     * @param $end timestamp
     * @return Available - the requested 'available', null for not founded
     *
     * */
    public function getAllAvailableInAPeriodRelatedToARoom($start, $end, $roomId) {
        if ($start == null || $end == null || $roomId == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from available where start >= :start AND end <= :end AND state <= :state AND room = :roomId");
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            $query->bindValue(':state', Available::STATE_OK, PDO::PARAM_INT);
            $query->bindValue(':roomId', $roomId);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $availables = array();
            foreach ($results as $result) {
                array_push($availables, new Available($result['id'], $result['performer'], $result['timestamp'], $result['room'], $result['start'], $result['end'], $result['state']));
            }
            return $availables;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - available id
     * @return Available - the requested 'available', null for not founded
     *
     * */
    public function getAvailableById($id) {
        if ($id == null) {
            return null;
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from available where id = :id");
            $query->bindValue(':id', $id);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) == 1) {
                return new Available($results[0]['id'], $results[0]['performer'], $results[0]['timestamp'], $results[0]['room'], $results[0]['start'], $results[0]['end'], $results[0]['state']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $operator integer - id of the operator
     * @param $availableId integer
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function cancelAvailableTime($operator, $availableId) {
        if ($operator == null) {
            return "operatorId must be provided";
        }
        $userOperator = $this->getUserById($operator);
        if ($userOperator == null) {
            return "operator not found";
        }
        if ($userOperator->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "operator not permitted";
        }
        try {
            $available = $this->getAvailableById($availableId);
            if ($available == null) {
                return "available not found";
            }
            if ($available->getState() > 0) {
                return "this available is already cancelled";
            }
            $query = $this->mysqlBase->dbh->prepare("update available set state = :state where id = :id");
            $query->bindValue(':state', Available::STATE_CANCELLED, PDO::PARAM_INT);
            $query->bindValue(':id', $availableId);
            if (!$query->execute()) {
                return "execute fail";
            }
            $orders = $this->getOrderByTime(strtotime($available->getStart()), strtotime($available->getEnd()));
            foreach ($orders as $order) {
                $this->cancelRoomApplication($operator, $order->getId());
            }
            return true;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - id of the user
     * @return User - the requested user, null for not founded
     *
     * */
    public function getUserById($id) {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from users where id = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) != 1) {
                return null;
            }
            return new User($results[0]['id'], $results[0]['identifer'], stripslashes($results[0]['name']), $results[0]['contact'], $results[0]['type'], $results[0]['credit']);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - id of the room
     * @return Room - the requested room, null for not founded
     *
     * */
    public function getRoomById($id) {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from rooms where id = :id");
            $query->bindValue(':id', $id);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) != 1) {
                return null;
            }
            return new Room($results[0]['id'], $results[0]['name']);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $name string - name of the room
     * @return Room - the requested room, null for not founded
     *
     * */
    public function getRoomByName($name) {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from rooms where name = :name");
            $query->bindValue(':name', $name);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) != 1) {
                return null;
            }
            return new Room($results[0]['id'], $results[0]['name']);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @return array of Room
     *
     * */
    public function getAllRooms() {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from rooms");
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $rooms = array();
            foreach ($results as $result) {
                array_push($rooms, new Room($result['id'], $result['name']));
            }
            return $rooms;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $identifer string - identifer of the user
     * @return User - the requested user, null for not founded
     *
     * */
    public function getUserByIdentifer($identifer) {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from users where identifer = :identifer");
            $query->bindValue(':identifer', $identifer);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return new User($results[0]['id'], $results[0]['identifer'], stripslashes($results[0]['name']), $results[0]['contact'], $results[0]['type'], $results[0]['credit']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $name string - name of the user
     * @return User - the requested user, null for not founded
     *
     * */
    public function getUserByName($name) {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from users where name = :name");
            $query->bindValue(':name', $name);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 0) {
                return new User($results[0]['id'], $results[0]['identifer'], stripslashes($results[0]['name']), $results[0]['contact'], $results[0]['type'], $results[0]['credit']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }


    /**
     *
     * @param $name string - actual name of the user
     * @return array of User - all records where name LIKE $name
     *
     * */
    public function queryUserByName($name) {
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from users where name like :name");
            $query->bindValue(':name', "%" . $name . "%");
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $users = array();
            if (count($results) > 0) {
                foreach ($results as $result) {
                    array_push($users, new User($result['id'], $result['identifer'], stripslashes($result['name']), $result['contact'], $result['type'], $result['credit']));
                }
                return $users;
                //return new User($results[0]['id'], $results[0]['identifer'], stripslashes($results[0]['name']), $results[0]['contact'], $results[0]['type'], $results[0]['credit']);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $name string - name of the new room
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    //insert room
    public function insertNewRoom($name) {
        if ($name == null) {
            return "illegal arguments";
        }
        if ($this->getRoomByName($name) instanceof Room) {
            return "already exist";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("INSERT INTO rooms (id, name) VALUES (NULL, :name)");
            $query->bindValue(':name', $name);
            return $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    /**
     *
     * @param $identifer string - user identifer
     * @param $name string - user name
     * @param $contact string
     * @param $type integer - see database-readme
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    //insert user
    public function insertNewUser($identifer, $name, $type) {
        if ($identifer == null || $name == null || is_null($type)) {
            return "illegal arguments";
        }
        if ($this->getUserByIdentifer($identifer) instanceof User) {
            return "user already exists";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("INSERT INTO users (id, identifer, name, contact, type, credit) VALUES (NULL, :identifer, :name, NULL, :type, :credit)");
            $query->bindValue(':identifer', $identifer);
            $query->bindValue(':name', $this->mysqlBase->dbh->quote($name));
            //$query->bindValue(':contact', "");
            $query->bindValue(':type', $type);
            $query->bindValue(':credit', User::DEFAULT_CREDIT);
            return $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - id of the target
     * @param $contact string - contact of the target
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function updateUserContact($id, $contact) {
        if ($id == null || $contact == null) {
            return "illegal arguments";
        }
        $user = $this->getUserById($id);
        if ($user instanceof User) {
            try {
                $query = $this->mysqlBase->dbh->prepare("update users set contact = :contact where id = :id");
                $query->bindValue(':contact', $contact);
                $query->bindValue(':id', $user->getId());
                return $query->execute();
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     *
     * @param $id integer - id of the target
     * @param $type integer - see database-readme
     * @param $performaer integer - id of the performer
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function updateUserType($performer, $id, $type) {
        if ($performer == null || $id == null || is_null($type)) {
            return "illegal arguments";
        }
        $performer = $this->getUserById($performer);
        if ($performer->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "not authorized";
        }
        $user = $this->getUserById($id);
        if ($user instanceof User) {
            try {
                $query = $this->mysqlBase->dbh->prepare("update users set type = :type where id = :id");
                $query->bindValue(':type', $type);
                $query->bindValue(':id', $user->getId());
                return $query->execute();
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     *
     * @param $id integer - target user id
     * @param $amount integer - amount of credit to add
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function updateUserCredit($id, $amount) {
        if ($id == null || $amount == null) {
            return "illegal arguments";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("update users set credit = credit + :amount where id = :id");
            $query->bindValue(':amount', $amount);
            $query->bindValue(':id', $id);
            return $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - target user id
     * @param $amount integer - amount of credit to set
     * @return string - error messages
     * @return boolean - query state
     *
     * */
    public function setUserCredit($id, $amount) {
        if ($id == null || $amount == null) {
            return "illegal arguments";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("update users set credit = :amount where id = :id");
            $query->bindValue(':amount', $amount);
            $query->bindValue(':id', $id);
            return $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $operatorId integer
     * @param $targetId integer
     * @return
     *
     * */
    public function banUser($operatorId, $targetId) {
        $operator = $this->getUserById($operatorId);
        $target = $this->getUserById($targetId);
        if ($operator == null || $target == null) {
            return "user not found";
        }
        if ($operator->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "not permitted";
        }

        return $this->setUserCredit($target->getId(), '0');
    }

    /**
     *
     * @param $operatorId integer
     * @param $targetId integer
     * @return
     *
     * */
    public function pardonUser($operatorId, $targetId) {
        $operator = $this->getUserById($operatorId);
        $target = $this->getUserById($targetId);
        if ($operator == null || $target == null) {
            return "user not found";
        }
        if ($operator->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "not permitted";
        }
        return $this->setUserCredit($target->getId(), User::DEFAULT_CREDIT);
    }

    /**
     *
     * @param $operatorId integer
     * @return array of User
     *
     * */
    public function getAllBlackListedUsers($operatorId) {
        $operator = $this->getUserById($operatorId);
        if ($operator == null) {
            return "user not found";
        }
        if ($operator->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "not permitted";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("select * from users where credit < 100"); // currently not using credit system
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $users = array();
            foreach ($results as $result) {
                array_push($users, new User($result['id'], $result['identifer'], stripslashes($result['name']), $result['contact'], $result['type'], $result['credit']));
            }
            return $users;
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $day string - date of the day
     * @param $roomId integer
     * @return array of integer - free hours
     *
     * */
    public function getFreeTimeInAPeriodRelatedToARoom($day, $roomId) {
        if ($day == null || $roomId == null) {
            return null;
        }
        $dayHours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
        $availables = $this->getAllAvailableInAPeriodRelatedToARoom(strtotime(date("Y-m-d 00:00:00", strtotime($day))), strtotime(date("Y-m-d 24:00:00", strtotime($day))), $roomId);
        $orders = $this->getOrderByTimeRelatedToARoom(strtotime(date("Y-m-d 00:00:00", strtotime($day))), strtotime(date("Y-m-d 24:00:00", strtotime($day))), $roomId);
        $freeHours = array();
        foreach ($dayHours as $hour) {
            if (strtotime(date("Y-m-d", strtotime($day)) . " " . $hour . ":00:00") <= strtotime("now")) {
                continue;
            }
            $isFind = false;
            foreach ($availables as $available) {
                if ($hour >= (int)date("G", strtotime($available->getStart())) && $hour < (int)date("G", strtotime($available->getEnd()))) {
                    $isFind = true;
                    foreach ($orders as $order) {
                        if ($hour >= (int)date("G", strtotime($order->getStart())) && $hour < (int)date("G", strtotime($order->getEnd()))) {
                            $isFind = false;
                            break;
                        }
                    }
                }
                if ($isFind) {
                    array_push($freeHours, $hour);
                    break;
                }
            }
        }
        return $freeHours;
    }

    public function getAdminFreeTimeInAPeriodRelatedToARoom($day, $roomId) {
        if ($day == null || $roomId == null) {
            return null;
        }
        $dayHours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
        //$availables = $this->getAllAvailableInAPeriodRelatedToARoom(strtotime(date("Y-m-d 00:00:00", strtotime($day))), strtotime(date("Y-m-d 24:00:00", strtotime($day))), $roomId);
        $orders = $this->getOrderByTimeRelatedToARoom(strtotime(date("Y-m-d 00:00:00", strtotime($day))), strtotime(date("Y-m-d 24:00:00", strtotime($day))), $roomId);
        $freeHours = array();
        foreach ($dayHours as $hour) {
            if (strtotime(date("Y-m-d", strtotime($day)) . " " . $hour . ":00:00") <= strtotime("now")) {
                continue;
            }
            $isFind = true;
            foreach ($orders as $order) {
                if ($hour >= (int)date("G", strtotime($order->getStart())) && $hour < (int)date("G", strtotime($order->getEnd()))) {
                    $isFind = false;
                    break;
                }
            }
            if ($isFind) {
                array_push($freeHours, $hour);
            }
        }
        return $freeHours;
    }

    public function editAvailableTime($operator, $availableId, $start, $end) {
        if ($operator == null) {
            return "operatorId must be provided";
        }
        $userOperator = $this->getUserById($operator);
        if ($userOperator == null) {
            return "operator not found";
        }
        if ($userOperator->getType() > User::TYPE_SYSTEM_ADMINISTRATOR) {
            return "operator not permitted";
        }
        $available = $this->getAvailableById($availableId);
        if ($available == null) {
            return "available not found";
        }
        //add to table 'available'
        try {
            $query = $this->mysqlBase->dbh->prepare("update available set start = :start, end = :end where id = :id");
            //INSERT INTO `available` (`id`, `timestamp`, `performer`, `room`, `start`, `end`, `state`) VALUES (NULL, CURRENT_TIMESTAMP, '1', '1', '2017-09-29 08:00:00', '2017-09-29 23:00:00', '0')
            $query->bindValue(':id', $available->getId());
            $query->bindValue(':start', date("Y-m-d H:i:s", $start));
            $query->bindValue(':end', date("Y-m-d H:i:s", $end));
            return $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     * @param $id integer - id of the target
     * @param $name - name of the target
     * @param $type integer - 0, 1, 2, 3, 4
     * @return string - error messages
     * @return boolean - query state
     *
     * */

    public function updateUserProfile($id, $name, $type) {
        if ($id == null || $name == null || $type == null) {
            return "illegal arguments";
        }
        try {
            $query = $this->mysqlBase->dbh->prepare("update users set name = :name, type = :type where id = :id");
            $query->bindValue(':name', $this->mysqlBase->dbh->quote($name));
            $query->bindValue(':type', $type);
            $query->bindValue(':id', $id);
            return $query->execute();
        } catch (PDOException $e) {
            die($e->getMessage());
        }

    }

}

?>
