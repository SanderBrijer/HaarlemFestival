<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once("activityBaseService.php");
require_once($root . "/DAL/foodactivityDAO.php");
require_once($root . "/Model/account.php");
require_once($root . "/DAL/dbContains.php");
require_once("restaurantTypeLinkService.php");
require_once($root . "/UI/table.php");

class foodactivityService extends activityBaseService
{
    private restaurantTypeLinkService $types;

    public function __construct()
    {
        $this->db = new foodactivityDAO();
        $this->types = new restaurantTypeLinkService();
    }

    public function getTablesChild(account $a, array $cssRules, array $dates): array
    {
        $tables = [];

        foreach ($dates as $k => $v) {
            $table = new table();
            $table->setTitle($k);
            $table->setIsCollapsable(true);
            $table->addHeader("Time", "Name", "Location", "Type");
            $table->assignCss($cssRules);
            foreach ($v as $c) {

                $startDateStr = $c->getActivity()->getStartTime()->format("H:i");
                $endDateStr = $c->getActivity()->getEndTime()->format("H:i");

                $tableRow = new tableRow();
                $table->addTableRows($tableRow);
                $tableRow->addString(
                    "$startDateStr to $endDateStr",
                    $c->getRestaurant()->getName(),
                    $c->getActivity()->getLocation()->getAddress(),
                    join('/', $this->types->getRestaurantTypes($c->getRestaurant()->getId()))
                );

                $tableRow->addButton('openBox(' . $c->getActivity()->getId() . ')', "Edit", "aid=\"" . $c->getActivity()->getId() . "\"");
            }

            $tables[] = $table;
        }

        return $tables;
    }

    public function getAll(): array
    {
        return $this->db->getArray([
            "order" => ["activity.date", "activity.starttime", "activity.endtime"]
        ]);
    }

    public function getByRestaurantId(int $restaurantId)
    {
        return $this->db->getArray([
            "restaurant.id" => $restaurantId
        ]);
    }

    public function getBySessionDate(string $date, array $times, int $restaurantId)
    {
        return $this->db->get([
            "activity.date" => $date,
            "activity.startTime" => "$times[0]",
            "activity.endTime" => "$times[1]",
            "restaurant.id" => $restaurantId
        ]);
    }

    public function updateRestaurantId(int $id, int $restaurantId)
    {
        try {
            $this->db->update([
                "id" => $id,
                "restaurantId" => $restaurantId
            ]);
        }
        catch (appException $e) {
            throw new appException("Invalid restaurant Id");
        }
    }

    public function insertFoodActivity(int $activityId, int $restaurantId)
    {
        return $this->db->insert([
            "restaurantId" => $restaurantId,
            "activityId" => $activityId
        ]);
    }
}