<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once("editActivityBase.php");
require_once ($root . "/Service/jazzactivityService.php");
require_once ($root . "/Service/jazzBandService.php");

class jazzEditActivity extends editActivityBase
{
    private jazzBandService $jazzBandService;

    public function __construct(account $account)
    {
        parent::__construct(new jazzactivityService(), $account);
        $this->jazzBandService = new jazzBandService();
    }

    public const editType = "Jazz";

    public const htmlEditHeader = [
        "band" => [
            "band" => htmlTypeEnum::list,
            "bandName" => htmlTypeEnum::text,
            "bandDescription" => htmlTypeEnum::textArea,
            "image" => htmlTypeEnum::imgUpload,
        ],
        "performance" => [
            "jazzActivityId" => htmlTypeEnum::hidden,
            "hall" => htmlTypeEnum::text,
            "seats" => htmlTypeEnum::number
        ]
    ];

    public function getHtmlEditFieldsChild(sqlModel $a): array
    {
        $bandsStr = $this->jazzBandService->getAllAsStr();

        $selBand = $a->getJazzband();

        return [
            "band" => [
                "options" => $bandsStr,
                "selected" => $selBand->getId()
            ],
            "jazzActivityId" => $a->getId(),
            "bandName" => $selBand->getName(),
            "bandDescription" => $selBand->getDescription(),
            "hall" => $a->getHall(),
            "seats" => $a->getSeats(),
            "image" => ""
        ];
    }

    public function getHtmlEditFieldsEmpty(): array
    {
        $bandsStr = $this->jazzBandService->getAllAsStr();

        return [
            "band" => [
                "options" => $bandsStr,
                "selected" => "-1"
            ],
            "jazzActivityId" => "new",
            "bandName" => "",
            "bandDescription" => "",
            "hall" => "",
            "seats" => "",
            "image" => ""
        ];
    }

    protected function processEditResponseChild(array $post)
    {
        if (isset($post["performanceIncomplete"]))
            throw new appException("Performance is incomplete");

        $bandId = null;

        if (!isset($post["band"]))
            throw new appException("Band is incomplete");
        else {
            $root = realpath($_SERVER["DOCUMENT_ROOT"]);
            $target_dir = $root . "/img/Bands";
            $target_file = $target_dir . "/jazz" . $post["band"] . ".png";

            $this->handleImage($target_file);
        }

        if ((int)$post["band"] == -1){
            $res = $this->jazzBandService->insertBand($post["bandName"], $post["bandDescription"]);
            if (!$res)
                throw new appException("[JazzBand] Failed to insert...");

            $bandId = $res;
        }
        elseif (isset($post["bandIncomplete"])) {
            $bandId = (int)$post["band"];
        }
        else {
            $this->jazzBandService->updateBand((int)$post["band"], $post["bandName"], $post["bandDescription"]);
        }

        if (!$this->service->updateActivity(
            (int)$post["jazzActivityId"],
            $post["hall"],
            (int)$post["seats"],
            $bandId
        ))
            throw new appException("[Jazz] db update failed...");
    }

    protected function processNewResponseChild(array $post, int $activityId)
    {
        if (isset($post["performanceIncomplete"]))
            throw new appException("Jazz form not filled in");

        $bandId = null;

        if (!isset($post["band"]))
            throw new appException("Invalid POST");
        else {
            $root = realpath($_SERVER["DOCUMENT_ROOT"]);
            $target_dir = $root . "/img/Bands";
            $target_file = $target_dir . "/jazz" . $post["band"] . ".png";

            $this->handleImage($target_file);
        }

        if ((int)$post["band"] == -1){
            $res = $this->jazzBandService->insertBand($post["bandName"], $post["bandDescription"]);
            if (!$res)
                throw new appException("[JazzBand] Failed to insert...");

            $bandId = $res;
        }
        elseif (isset($post["bandIncomplete"])) {
            $bandId = (int)$post["band"];
        }

        if (!$this->service->insertActivity(
            $activityId,
            $post["hall"],
            (int)$post["seats"],
            $bandId
        ))
            throw new appException("[Jazz] db insert failed...");
    }
}