<?php

namespace App\Http\Controllers;

use App\Http\Resources\RentalResource;
use App\Models\Car;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RentalController extends Controller
{
    public function index(){
        return RentalResource::collection(Rental::all());
    }
    public function show(string $id){
        return new RentalResource(Rental::with('car')->findOrFail($id));
    }
    public function store(Request $request,string $id){
        $validated = $request->validate([
            'plan'      => 'required|in:daily,weekly,monthly',
            'km_before' => 'required|integer',
            'total_price'=>'required|integer',
            'end_date'    => 'nullable|date',
            'start_date' =>'nullable|date',
        ]);
        $car = Car::findOrFail($id);
        if (!$car->is_available) {
            return response()->json(['error' => 'Car is not available'], 400);
        }
        $rental = Rental::create([
            'car_id'    => $id,
            'plan'      => $validated['plan'],
            'km_before' => $validated['km_before'],
            'total_price' => $validated['total_price'],
            'start_date'  => $validated['start_date']?? now(),
            'end_date'=>$validated['end_date']?? null,
            'status'      => 'rented',
        ]);
        $car->update(['is_available' => false]);
        return new RentalResource($rental);
    }
    public function returnCar(Request $request, string $id){

        $rental =Rental::findOrFail($id);
        if($rental->status !=='rented'){
            return response()->json(['error' => 'Rental is not active'], 400);
        }
        $validated = $request->validate([
            'km_after' => 'required|integer|gte:' . $rental->km_before,
        ]);
        $rental->update([
            'km_after'  => $validated['km_after'],
            'status'    => 'returned',
            'end_date'  => now(),
        ]);
        $rental->car->update(['is_available' => true]);
        return response()->json(['message' => 'Car returned successfully'], 200);
    }
    public function getStatistics()
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to view statistics.'], 403);
        }
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        $todayRentedCount = Rental::whereDate('created_at', $today)->count();
        $thisWeekRentedCount = Rental::whereBetween('created_at', [$startOfWeek, Carbon::now()])->count();
        $thisMonthRentedCount = Rental::whereBetween('created_at', [$startOfMonth, Carbon::now()])->count();
        $thisYearRentedCount = Rental::whereBetween('created_at', [$startOfYear, Carbon::now()])->count();
        $lifetimeRentedCount = Rental::count();

        $todayRevenue = Rental::whereDate('created_at', $today)->sum('total_price');
        $thisWeekRevenue = Rental::whereBetween('created_at', [$startOfWeek, Carbon::now()])->sum('total_price');
        $thisMonthRevenue = Rental::whereBetween('created_at', [$startOfMonth, Carbon::now()])->sum('total_price');
        $thisYearRevenue = Rental::whereBetween('created_at', [$startOfYear, Carbon::now()])->sum('total_price');
        $lifetimeRevenue = Rental::sum('total_price');

        return response()->json([
            'today' => [
                'rented_count' => $todayRentedCount,
                'revenue' => $todayRevenue,
            ],
            'this_week' => [
                'rented_count' => $thisWeekRentedCount,
                'revenue' => $thisWeekRevenue,
            ],
            'this_month' => [
                'rented_count' => $thisMonthRentedCount,
                'revenue' => $thisMonthRevenue,
            ],
            'this_year' => [
                'rented_count' => $thisYearRentedCount,
                'revenue' => $thisYearRevenue,
            ],
            'lifetime' => [
                'rented_count' => $lifetimeRentedCount,
                'revenue' => $lifetimeRevenue,
            ],
        ]);
    }

}
