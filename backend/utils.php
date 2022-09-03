<?php

function validate($data, $type)
{
    switch ($type) {
        case "hkid":
            $regex = "/^(?=.*[0-9])(?=.*[A-Z])\w{8}$/";
            return preg_match($regex, $data);
        case "date":
            $regex = "/^(?:[0-9]|[012][0-9]|3[01])$/";
            return preg_match($regex, $data);
        case "year":
            $regex = "/^19[0-9]{2}|20[0-2]{2}$/";
            return preg_match($regex, $data) && strlen((string)$data) == 4;
        case "email":
            $regex = "/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/";
            return preg_match($regex, $data);
        case "phone":
            $regex = "/^[0-9]{8}$/";
            return preg_match($regex, $data);
        case "selectedDate":
            $regex = "/^0[1-9]|[1-2][0-9]|3[0-1]$/";
            return preg_match($regex, $data);
        case "selectedTime":
            return in_array($data, [800, 900, 1000, 1100, 1200, 1300, 1400, 1500, 1600, 1700, 1800, 1900]);
        case "location":
            return in_array($data, ["Mong Kok", "Sha Tin", "Tsuen Wan"]);

    }
}


?>