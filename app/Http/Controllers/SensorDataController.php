<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SensorDataController extends Controller
{
    //
public function store(Request $request){
    // Validate the incoming data from the sensor
    
    $request->validate([
        'sensor_id' => 'required|unique:parking_spots',
        'availability' => 'required|boolean',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
    ]);

    if ($request->availability == 1 && !$request->has('user_id')) {
        return response()->json(['error' => 'User ID is required when availability is 1.'], 422);
    }

    // Check if a record with the same location exists in the database
    $existingRecord = ParkingSpots::where('latitude', $request->latitude)
        ->where('longitude', $request->longitude)
        ->first();

    if ($existingRecord) {
        if ($request->availability == 1) {
            $existingRecord->user_id = $request->user_id;
            $existingRecord->availability = $request->availability;
        }
        else{
            $existingRecord->availability = 0;
            $existingRecord->user_id = null;
        }
        $existingRecord->save();
    } else {
        // Create a new ParkingStatus record and save it to the database
        $parkingStatus = new ParkingSpots();
        $parkingStatus->sensor_id = $request->sensor_id;
        $parkingStatus->latitude = $request->latitude;
        $parkingStatus->longitude = $request->longitude;

        if ($request->availability == 1) {
            $parkingStatus->availability = 1;
            $parkingStatus->user_id = $request->user_id;
        } else {
            $parkingStatus->availability = 0;
        }

        $parkingStatus->save();
    }

    return response()->json(['message' => 'Sensor data saved successfully'], 201);
}

public function updateAvailability($sensor_id,$availability){
    $spot = ParkingSpots::where('sensor_id',$sensor_id)->first();
    if($availability == 0){
        $spot->availability = 0;
        $spot->user_id = null;
        $spot->save();
        // return response()->json(['message' => 'Spot is now available'], 201);
        return response()->json(['message' => 'Spot is now available', 'updated' => true], 200);
    }
    else{
        $spot->availability = 1;
        $spot->save();
        return response()->json(['message' => 'Spot is now detected as not available'], 201);
    }

}

public function get (){
    $locations = ParkingSpots::get();

    return response()->json($locations);
}

public function reserve($id,$userid){
    $checkUser = ParkingSpots::where('user_id',$userid)->first();
    if(!isset($checkUser)){
        $parkingSpot = ParkingSpots::where('id',$id)->first();
        $parkingSpot->availability = 1;
        $parkingSpot->user_id = $userid;
        $parkingSpot->save();
    }
    else{
        return response()->json(['error' => 'User already reserved a spot'], 422);
    } 
}

public function cancelReservation($userid){
    $spot = ParkingSpots::where('user_id',$userid)->first();
    if(isset($spot)){
        $spot->availability = 0;
        $spot->user_id = null;
        $spot->save();
        return response()->json(['message' => 'Spot reservation cancelled'], 201);
    }
    else{
        return response()->json(['error' => 'User does not have a spot to cancel'], 422);
    } 
}

}
