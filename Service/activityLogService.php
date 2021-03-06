<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once ("baseService.php");
require_once ($root . "/DAL/activityLogDAO.php");
require_once ("activityService.php");

class activityLogService extends baseService
{
    public function __construct(){
        $this->db = new activityLogDAO();
    }

    public function insert(activityLog $log){
        return $this->db->insert($log->sqlGetFields());
    }

    public function get(int $limit){
        return $this->db->getArray([
            "order" => "id DESC",
            "limit" => $limit
        ]);
    }

    public function getWithName(int $limit){

        $items = $this->get($limit);
        $ids = [];

        foreach ($items as $i){
            if ($i->isTargetNull()) {
                if ($i->hasActivity())
                    $ids[] = $i->getActivity()->getId();
            }
        }

        if (!empty($ids)){
            $activityService = new activityService();
            $names = $activityService->getNames($ids);

            foreach ($items as $i){
                if ($i->hasActivity()){
                    if ($i->isTargetNull()){
                        $id = $i->getActivity()->getId();
                        if (array_key_exists($id, $names))
                            $i->setTarget($names[$id]);
                        else {
                            $i->setTarget("(broken db entry)");
                        }
                    }
                }
                else {
                    $i->setTarget("(deleted)");
                }

            }
        }

        return $items;
    }
}