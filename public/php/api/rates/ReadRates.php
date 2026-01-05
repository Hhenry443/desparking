<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Rates.php';

class ReadRates extends Rates
{

    public function getCarparkRatesJSON()
    {
        header('Content-Type: application/json');
        
        $carparkID = $_GET['carpark_id'] ?? null;
        
        if (!$carparkID) {
            echo json_encode(['success' => false, 'message' => 'Missing carpark_id']);
            exit;
        }
        
        $rates = $this->getCarparkRates((int)$carparkID);
        
        echo json_encode([
            'success' => true,
            'rates' => $rates
        ]);
        exit;
    }
    
    public function getCarparkRates($carparkID)
    {
        return $this->selectRatesByCarpark($carparkID);
    }

    /**
     * Finds the cheapest combination of rates for a given duration.
     */
    public function calculateOptimalPrice($carparkID, $totalMinutes)
    {
        $rates = $this->getCarparkRates($carparkID);
        if (empty($rates)) return 0;

        $totalCents = 0;
        $remainingMinutes = $totalMinutes;

        // Greedy algorithm: Try to fit the largest time blocks first
        foreach ($rates as $rate) {
            if ($remainingMinutes <= 0) break;

            $count = floor($remainingMinutes / $rate['duration_minutes']);
            if ($count > 0) {
                $totalCents += ($count * $rate['price']);
                $remainingMinutes -= ($count * $rate['duration_minutes']);
            }
        }

        // If there is leftover time (e.g., stay was 70 mins and we only had a 60 min rate)
        // apply the smallest available rate once to cover the remainder.
        if ($remainingMinutes > 0) {
            $smallestRate = end($rates);
            $totalCents += $smallestRate['price'];
        }

        return $totalCents;
    }
}
