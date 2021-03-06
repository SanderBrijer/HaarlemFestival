<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);

require_once ("baseService.php");
require_once ($root . "/DAL/ticketDAO.php");


class ticketService extends  baseService
{
    public function __construct()
    {
        $this->db = new ticketDAO();
    }

    public function getTicketsByOrder(string $orderId){
        return $this->db->getArray([
            "ticket.orderId" => new dbContains("$orderId")
        ]);
    }

    public function getTicketsFromCustomer(int $customerId){
        return $this->db->getArray([
            "customerId" => $customerId
        ]);
    }

    public function insertTicket(int $activityId, int $customerId, int $orderId, int $amount){
        return $this->db->insert([
            "activityId" => $activityId,
            "customerId" => $customerId,
            "orderId" => $orderId,
            "amount" => $amount
        ]);
    }
}