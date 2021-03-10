<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once("activityBaseService.php");
require_once("foodactivityService.php");
require_once($root . "/DAL/restaurantDAO.php");
require_once("restaurantTypeLinkService.php");

class restaurantService extends baseService
{
    public function __construct()
    {
        $this->db = new restaurantDAO();
    }

    public function getAll(): array
    {
        return $this->db->get();
    }

    public function postEditFields($post)
    {
        if (isset($post["restaurantIncomplete"]) || $post["type"] != "Food" || !isset($post["location"]))
            return;

        $update = [
            "id" => (int)$post["restaurantId"],
            "name" => $post["name"],
            "description" => $post["description"],
            "stars" => (int)$post["stars"],
            "seats" => (int)$post["seats"],
            "phonenumber" => (int)$post["phoneNumber"]
        ];

        if (isset($post["locationIncomplete"]))
            $update["locationId"] = (int)$post["location"];

        if (isset($post["restaurantPrice"]))
            $update["price"] = (float)$post["restaurantPrice"];

        (new restaurantTypeLinkService())->updateFieldIds($update["id"], $post["restaurantType"]);

        if (!$this->db->update($update))
            throw new appException("Db update failed...");
    }

    public function getBySearch($searchTerm, $cuisine, $stars3, $stars4)
    {
        $filter = array();

        $filter = array_merge($filter, array("restaurant.name" => new dbContains($searchTerm)));

        $stars = array();
        if ($stars3) {
            $stars[] = "3";
        }

        if ($stars4) {
            $stars[] = "4";
        }
        if (count($stars) > 0) {
            $filter = array_merge($filter, array("restaurant.stars" => $stars));
        }
        return $this->db->get($filter);
    }

    public function getById($id)
    {
        return $this->db->get([
            "restaurant.id" => new dbContains($id)
        ]);
    }

    public function getTimesByRestaurantId($restaurantId)
    {
        $foodActivityService = new foodactivityService();
        $activities = $foodActivityService->getByRestaurantId($restaurantId);
        return $this->getTimes($activities);
    }

    public function getDatesByRestaurantId($restaurantId)
    {
        $foodActivityService = new foodactivityService();
        $activities = $foodActivityService->getByRestaurantId($restaurantId);
        return $this->getDates($activities);
    }

    function getTimes($foodactivities)
    {
        $times = array();

        foreach ($foodactivities as $foodactivity) {
            $startTime = $foodactivity->getActivity()->getStartTime();
            $endTime = $foodactivity->getActivity()->getEndTime();
            $startTimeStr = date_format($startTime, 'H:i');
            $endTimeStr = date_format($endTime, 'H:i');

            $times["$startTimeStr"] = $endTimeStr;
        }
        return $times;
    }

    function getDates($foodactivities)
    {
        $dates = array();

        foreach ($foodactivities as $foodactivity) {
            $date = $foodactivity->getActivity()->getDate();
            $date = date_format($date, "Y-m-d");
            $dates["$date"] = $date;
        }
        return $dates;
    }

}