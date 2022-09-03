<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class UserModel extends Database
{
    public function getUsers($limit): array
    {
        return $this->select("SELECT hkid, date, year FROM hkid ORDER BY hkid ASC LIMIT ?", "i", [$limit]);
    }

    public function getHKID($hkid, $date, $year): array
    {
        return $this->select("SELECT hkid, date, year FROM hkid where hkid= ? and date= ? and year = ? ORDER BY hkid ASC LIMIT 1", "sii", [$hkid, $date, $year]);
    }

    public function getHKIDID($hkid, $date, $year): array
    {
        return $this->select("SELECT id FROM hkid where hkid=? and date=? and year = ? ORDER BY hkid ASC LIMIT 1", "sii", [$hkid, $date, $year]);
    }

    public function insertHKID($hkidRef, $email, $phone, $selectedDate, $selectedTime, $location): bool
    {
        $q = "insert into booking (refHKID, email, phone, date, time, location) values (?, ?, ?, ?,?,?)";
        error_log(print_r($q, true));
        return $this->insert($q, "ssssss", [$hkidRef, $email, $phone, $selectedDate, $selectedTime, $location]);
    }


}