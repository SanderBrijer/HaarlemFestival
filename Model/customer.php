<?php

require_once ("sqlModel.php");

class customer extends sqlModel
{
    private int $id;
    private string $firstName;
    private string $lastname;
    private location $location;
    private int $phoneNumber;
    private int $accountId;

    protected const sqlTableName = "customer";
    protected const sqlFields = ["id", "firstName", "lastname", "locationId", "phoneNumber", "accountId"];
    protected const sqlLinks = ["locationId" => location::class];

    public function __construct()
    {
        $this->id = -1;
        $this->firstName = "firstName";
        $this->lastname = "lastname";
        $this->location = null;
        $this->phoneNumber = 0;
        $this->accountId = -1;
    }

    public function constructFull(int $id, string $firstName, int $lastname, location $location, int $phoneNumber, int $accountId)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastname = $lastname;
        $this->location = $location;
        $this->phoneNumber = $phoneNumber;
        $this->accountId = $accountId;
        return $this;
    }

    public function sqlGetFields()
    {
        return [
            "id" => $this->id,
            "firstName" => $this->firstName,
            "lastname" => $this->lastname,
            "locationId" => $this->location->getId(),
            "phoneNumber" => $this->phoneNumber,
            "accountId" => $this->accountId
        ];
    }

    public static function sqlParse(array $sqlRes): self
    {
        return (new self())->constructFull(
            $sqlRes[self::sqlTableName . "id"],
            $sqlRes[self::sqlTableName . "firstName"],
            $sqlRes[self::sqlTableName . "lastname"],
            location::sqlParse($sqlRes),
            $sqlRes[self::sqlTableName . "phoneNumber"],
            $sqlRes[self::sqlTableName . "accountId"],
        );
    }

  
    public function getId()
    {
        return $this->id;
    }

 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    public function getFirstName()
    {
        return $this->firstName;
    }


    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }


    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }


    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }
}