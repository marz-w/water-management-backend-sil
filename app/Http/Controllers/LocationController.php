<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Exception;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function createLocation(Request $request){
        $request->validate([
            'name' =>'required|string',
            'description' => 'nullable|string|max:1000'
        ]);

        $location = new Location();
        $location->name = $request->name;
        $location->description = $request->description;

        try{
           $location->save();
           return response([
               'message'=>'Location saved successfully',
               'saved-location'=>$location
           ]) ;
        }
        catch(\Exception $e){
            return response([
                'message'=>'Failed to save',
                'error'=>$e->getMessage()
            ]);
        }
    }
    public function readAllLocations()
    {
        try{
            $locations = Location::all();
            return response()->json($locations);
        }catch(\Exception $e){
            return response([
                'message'=>'Failed to fetch',
                'error'=>$e->getMessage()
            ]);
        }
    }
    public function readLocation($id){
        try{
            $location = Location::where('id',$id)->first();
            return response()->json($location);
        }catch(\Exception $e){
            return response([
                'message'=>'Failed to save',
                'error'=>$e->getMessage()
            ]);
        }
    }
}