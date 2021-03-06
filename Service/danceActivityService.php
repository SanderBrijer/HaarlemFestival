<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once ("activityBaseService.php");
require_once ($root . "/DAL/artistOnActivityDAO.php");
require_once ($root . "/DAL/danceActivityDAO.php");
require_once ($root . "/DAL/danceArtistDAO.php");
require_once ("artistOnActivityService.php");
require_once ("danceArtistService.php");
require_once ($root . "/DAL/dbContains.php");

class danceActivityService extends activityBaseService
{
    private $activityDAO;

    public function __construct()
    {
        $this->db = new artistOnActivityDAO();
        $this->activityDAO = new danceActivityDAO();
    }

    public function getActivityFromId(int $id){
        return $this->db->getArray([
            "danceactivity.id" => $id
        ]);
    }

    public function getActivityBySessionType($type){
        $ar =  $this->db->getArray([
            "danceactivity.sessionType" => $type
        ]);
        return $this->toDanceActivityArray($ar);
    }

    public function getTablesChild(account $a, array $cssRules, array $dates) : array
    {
        $tables = [];

        foreach ($dates as $k => $v){
            $table = new table();
            $table->setTitle($k);
            $table->setIsCollapsable(true);
            $table->addHeader("Time", "Name", "Location", "Type");
            $table->assignCss($cssRules);
            foreach ($v as $c){
                $startDateStr = $c->getActivity()->getStartTime()->format("H:i");
                $endDateStr = $c->getActivity()->getEndTime()->format("H:i");

                $artists = "";

                foreach ($c->getArtists() as $artist){
                    $artists .= $artist->getName() . " & ";
                }

                $artists = substr($artists, 0, -3);

                $tableRow = new tableRow();
                $table->addTableRows($tableRow);
                $tableRow->addString(
                    "$startDateStr to $endDateStr",
                    $artists,
                    $c->getActivity()->getLocation()->getName(),
                    $c->getType()
                );

                $tableRow->addButton('openBox('. $c->getActivity()->getId() . ')', "Edit", "aid=\"". $c->getActivity()->getId() . "\"");
            }

            $tables[] = $table;
        }

        return $tables;
    }

    public function toDanceActivityArray(array $aoaArray){
        $trackIds = [];

        foreach ($aoaArray as $aoa){
            if (!array_key_exists($aoa->getActivity()->getId(), $trackIds))
                $trackIds[$aoa->getActivity()->getId()] = $aoa->getActivity();

            $trackIds[$aoa->getActivity()->getId()]->addArtist($aoa->getArtist());
        }

        return array_values($trackIds);
    }

    public function getAll(): array
    {
        $res =  $this->db->get([
            "order" => ["activity.date", "activity.starttime", "activity.endtime"]
        ]);

        return $this->toDanceActivityArray($res);
    }

    public function getFromActivityIds(array $ids){
        return $this->toDanceActivityArray(parent::getFromActivityIds($ids));
    }

    // Format Y-m-d. Needs change
    public function getAllWithDate(string $date){
        $res =  $this->db->getArray([
            "activity.date" => $date,
            "order" => ["activity.date", "activity.starttime", "activity.endtime"]
        ]);

        return $this->toDanceActivityArray($res);
    }

    public function updateSessionType(int $id, string $sessionType){
        return $this->activityDAO->update([
            "id" => $id,
            "sessionType" => $sessionType
        ]);
    }

    public function insertDanceActivity(int $activityId, string $sessionType){
        $insert = [
            "activityid" => $activityId,
            "sessionType" => $sessionType
        ];

        return $this->activityDAO->insert($insert);
    }

    public function deleteTypedActivity(array $activityIds)
    {
        // Maybe instead cascade del in sql?
        $danceActivity = $this->activityDAO->get([
            "activityid" => $activityIds
        ]);

        if (is_null($danceActivity))
            throw new appException("No id was found");

        if (gettype($danceActivity) != "array")
            $danceActivity = [$danceActivity];

        $idList = [];
        foreach ($danceActivity as $a){
            $idList[] = $a->getId();
        }

        $this->db->delete([
            "danceactivityid" => $idList
        ]);

        return $this->activityDAO->delete([
            "id" => $idList
        ]);
    }
}