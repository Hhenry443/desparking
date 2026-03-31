<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';

class ReadCarparks extends Carparks
{
    private PDO $db;

    public function getCarparks()
    {

        $carparks = array();

        $carparks = $this->selectAllCarparks();

        return $carparks;
    } // function getCarpark

    public function getCarparkById($carparkID)
    {
        $carpark = $this->selectCarparkByID($carparkID);

        return $carpark;
    }

    public function searchAvailableCarparks(
        float $lat,
        float $lng,
        float $radiusKm,
        string $startTime,
        string $endTime
    ) {
        $includesWeekend = $this->windowIncludesWeekend($startTime, $endTime);

        return $this->selectAvailableCarparks(
            $lat,
            $lng,
            $radiusKm,
            $startTime,
            $endTime,
            $includesWeekend
        );
    } // function searchAvailableCarparks

    private function windowIncludesWeekend(string $startTime, string $endTime): bool
    {
        $current = new DateTime($startTime);
        $end     = new DateTime($endTime);

        // Walk day by day (max 7 steps before we're guaranteed to have hit both weekend days)
        $steps = 0;
        while ($current <= $end && $steps <= 7) {
            $dow = (int) $current->format('N'); // 6 = Saturday, 7 = Sunday
            if ($dow >= 6) {
                return true;
            }
            $current->modify('+1 day');
            $steps++;
        }

        return false;
    }

    public function getCarparksByUserId($userId)
    {
        $carparks = array();

        $carparks = $this->selectCarparksByUserId($userId);

        return $carparks;
    } // function getCarparksByUserId

    public function getCarparkPhotosById(int $id): array
    {
        return $this->getCarparkPhotos($id);
    }

    public function getMonthlyCarparks()
    {
        $carparks = array();

        $carparks = $this->getAllMonthlyCarparks();

        return $carparks;
    } // function getCarparksByUserId

    public function getPendingCarparks(): array
    {
        return $this->selectPendingCarparks();
    }

}// class ReadCarparks