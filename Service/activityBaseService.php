<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once($root . "/Model/tableInterface.php");
require_once ("baseService.php");

abstract class activityBaseService extends baseService implements tableInterface
{
    public abstract function getFields() : array;

    public abstract function getAll() : array;

    public function getTableContent(): array
    {
        $table = [];
        $table["header"] = ["Time"];

        foreach ($this->getFields() as $f => $_) {
            $table["header"][] = $f;
        }

        $content = $this->getAll();
        if (is_null($content))
            return $table;

        if (gettype($content) != "array")
            $content = [$content];

        $dates = [];

        foreach ($content as $c){
            $dateStr = $c->getActivity()->getDate()->format("Y-m-d");

            if (!isset($dates[$dateStr])){
                $dates[$dateStr] = [];
            }

            $startDateStr = $c->getActivity()->getStartTime()->format("H:i");
            $endDateStr = $c->getActivity()->getEndTime()->format("H:i");

            $local = [
                "$startDateStr to $endDateStr"
            ];

            foreach ($this->getFields() as $f) {
                $local[] = $f($c);
            }

            $dates[$dateStr][] = $local;
        }

        $table["sections"] = $dates;

        return $table;
    }
}