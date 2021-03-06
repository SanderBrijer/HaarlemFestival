<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once($root . "/Model/tableInterface.php");
require_once($root . "/Model/account.php");
require_once ($root . "/Service/baseService.php");
require_once ($root . "/Utils/appException.php");
require_once ($root . "/DAL/locationDAO.php");
require_once ("editInterface.php");
require_once ($root . "/Service/activityLogService.php");
require_once ("editRequest.php");
require_once ("editUpdate.php");

// Editbase: how it works
/*
 * A htmleditheader is a list of strings which are the sections of the edit menu.
 * The value of a section is a list with the keys of which being the name of the field,
 * and the value either being a htmlTypeEnum or a list with a htmlTypeEnum, and an account permission
 *
 * getHtmlEditFields is a list of the keys of the sections of the htmleditheader, with their equivelant proper values
 */

abstract class editBase implements editRequest
{
    protected account $account;

    public function __construct(account $account){
        $this->account = $account;
    }

    protected function stripHtmlChars($input, $htmlType){
        switch (gettype($input)){
            case "string":
                if (ctype_space($input))
                    throw new appException("Empty string provided!");

                switch($htmlType){
                    case htmlTypeEnum::number:
                    case htmlTypeEnum::float:
                        if (!is_numeric($input))
                            throw new appException("Value is not a number");

                        if ((int)$input < 0)
                            throw new appException("Negative values cannot be used");

                        break;
                    case htmlTypeEnum::list:
                        if (!is_numeric($input))
                            throw new appException("Value is not a number");

                        if ((int)$input < -1)
                            throw new appException("Negative values cannot be used");

                        break;
                }

                return trim(htmlspecialchars($input, ENT_QUOTES));
            case "array":
                $new = [];
                foreach ($input as $a){
                    $new[] = $this->stripHtmlChars($a, $htmlType);
                }
                return $new;
            default:
                throw new appException("Can't strip type " . gettype($input));
        }
    }

    protected const htmlEditHeader = [];

    protected abstract function getHtmlEditFields($entry);

    protected function getAllHtmlEditFields($entry) {
        return $this->getHtmlEditFields($entry);
    }

    protected function getHtmlEditHeader(){
        return static::htmlEditHeader;
    }

    public function filterHtmlEditResponse(array $postResonse){
        $header = $this->getHtmlEditHeader();
        $correctedPostResponse = [];

        foreach ($header as $hk => $hv){
            foreach ($hv as $k => $v){
                if (gettype($v) == "array"){
                    if (($this->account->getCombinedRole() & $v[1]))
                        if (array_key_exists($k, $postResonse))
                            $correctedPostResponse[$k] = $this->stripHtmlChars($postResonse[$k], $v);
                        else
                            $correctedPostResponse[$hk . "Incomplete"] = true;
                }
                elseif (array_key_exists($k, $postResonse))
                    $correctedPostResponse[$k] = $this->stripHtmlChars($postResonse[$k], $v);
                elseif ($v == htmlTypeEnum::imgUpload || $v == htmlTypeEnum::tableView || $v == htmlTypeEnum::checkBox){
                    continue;
                }
                else {
                    $correctedPostResponse[$hk . "Incomplete"] = true;
                }

            }
        }

        return $correctedPostResponse;
    }

    protected function packHtmlEditContent($fields){
        $header = $this->getHtmlEditHeader();
        $res = [];
        foreach ($header as $hk => $hv){
            $classField = [];
            foreach ($hv as $k => $v){
                if (gettype($v) == "array"){
                    if (($v[1] >= 0x10) ? (($this->account->getCombinedRole() & $v[1]) == $v[1]) : ($this->account->getRole() >= $v[1]))
                        $classField[$k] = ["type" => $v[0], "value" => $fields[$k]];
                }
                else
                    $classField[$k] = ["type" => $v, "value" => $fields[$k]];
            }
            $res[$hk] = $classField;
        }

        return $res;
    }

    // TODO: This is a hack. i'm lazy. i blame php
    // TODO: implement actual error messages for this
    protected function handleImage($target_file){
        if (!isset($_FILES) || !isset($_FILES["image"]) || empty($_FILES["image"]["tmp_name"]))
            return;

        if (!getimagesize($_FILES["image"]["tmp_name"])) // is this a valid image?
            throw new appException("File uploaded is not an image");

        if ($_FILES["image"]["size"] > 0x100000) // Is the file over 1MB?
            throw new appException("Uploaded file is too large");

        $imageFileType = strtolower(pathinfo(basename($_FILES["image"]["name"]),PATHINFO_EXTENSION));

        if ($imageFileType != "png") // We only support png's
            throw new appException("Only png's are supported");

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    public abstract function getHtmlEditContent(int $id);
}