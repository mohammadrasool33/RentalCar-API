<?php

namespace App\Http\Controllers;

use App\Http\Resources\RentalResource;
use App\Models\Car;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RentalController extends Controller
{
    public function index()
    {
        return RentalResource::collection(Rental::with('car')->latest()->get());
    }
    
    public function show(string $id)
    {
        try {
            return new RentalResource(Rental::with('car')->findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id'           => 'required|exists:cars,id',
            'duration_type'    => 'required|in:daily,weekly,monthly',
            'duration_count'   => 'required|integer|min:1',
            'renter_name'      => 'required|string',
            'renter_phone'     => 'required|string',
            'passport_number'  => 'required|string',
            'pickup_location'  => 'required|string',
            'rental_start_date' => 'sometimes|date',
            'rental_end_date'  => 'sometimes|date|after_or_equal:rental_start_date',
            'mileage_at_rental' => 'required|integer',
            'discount_amount'   => 'sometimes|numeric|min:0',
            'is_paid'          => 'sometimes|boolean',
            'pickup_service_check' => 'sometimes|array',
        ]);
        
        $car = Car::findOrFail($validated['car_id']);
        
        if (!$car->is_available) {
            return response()->json(['error' => 'Car is not available'], 400);
        }
        
        $startDate = $validated['rental_start_date'] ?? now();
        $durationCount = (int)$validated['duration_count'];
        $durationType = $validated['duration_type'];
        
        // Calculate end date based on duration
        $endDate = Carbon::parse($startDate);
        if ($durationType === 'daily') {
            $endDate->addDays($durationCount);
            $priceRate = $car->price_per_day;
        } elseif ($durationType === 'weekly') {
            $endDate->addWeeks($durationCount);
            $priceRate = $car->price_per_week;
        } else { // monthly
            $endDate->addMonths($durationCount);
            $priceRate = $car->price_per_month;
        }
        
        // Calculate prices
        $totalPrice = $priceRate * $durationCount;
        $discountAmount = $validated['discount_amount'] ?? 0;
        $finalPrice = $totalPrice - $discountAmount;
        
        $rental = Rental::create([
            'car_id'            => $validated['car_id'],
            'duration_type'     => $validated['duration_type'],
            'duration_count'    => $durationCount,
            'renter_name'       => $validated['renter_name'],
            'renter_phone'      => $validated['renter_phone'],
            'passport_number'   => $validated['passport_number'],
            'pickup_location'   => $validated['pickup_location'],
            'rental_start_date' => $startDate,
            'rental_end_date'   => $endDate,
            'mileage_at_rental' => $validated['mileage_at_rental'],
            'price_rate'        => $priceRate,
            'total_price'       => $totalPrice,
            'discount_amount'   => $discountAmount,
            'final_price'       => $finalPrice,
            'final_total'       => $finalPrice,
            'is_active'         => true,
            'is_paid'           => $validated['is_paid'] ?? false,
            'pickup_service_check' => $validated['pickup_service_check'] ?? null,
        ]);
        
        $car->update(['is_available' => false]);
        
        return new RentalResource($rental);
    }
    
    public function getRentalsByCar(string $carId)
    {
        try {
            $car = Car::findOrFail($carId);
            $rentals = $car->rentals()->with('car')->latest()->get();
            return RentalResource::collection($rentals);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    }
    
    public function returnCar(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:rentals,id',
            'mileage_at_return' => 'required|integer',
            'return_location' => 'required|string',
            'additional_charges' => 'sometimes|numeric|min:0',
            'comments' => 'sometimes|nullable|string',
            'return_service_check' => 'sometimes|array',
        ]);
        
        try {
            $rental = Rental::with('car')->findOrFail($validated['id']);
            
            if (!$rental->is_active) {
                return response()->json(['message' => 'This rental is already completed'], 400);
            }
            
            // Validate mileage
            if ($validated['mileage_at_return'] < $rental->mileage_at_rental) {
                return response()->json(['message' => 'Return mileage cannot be less than rental mileage'], 400);
            }
            
            $additionalCharges = $validated['additional_charges'] ?? 0;
            $finalTotal = $rental->final_price + $additionalCharges;
            
            $rental->update([
                'mileage_at_return'    => $validated['mileage_at_return'],
                'return_location'      => $validated['return_location'],
                'return_date'          => now(),
                'is_active'            => false,
                'additional_charges'   => $additionalCharges,
                'final_total'          => $finalTotal,
                'comments'             => $validated['comments'] ?? null,
                'return_service_check' => $validated['return_service_check'] ?? null,
            ]);
            
            $rental->car->update(['is_available' => true]);
            
            return response()->json([
                'message' => 'Car returned successfully',
                'rental' => new RentalResource($rental->fresh())
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
    }
    
    public function updatePaymentStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:rentals,id',
        ]);
        
        try {
            $rental = Rental::findOrFail($validated['id']);
            
            $rental->update([
                'is_paid' => true
            ]);
            
            return response()->json([
                'message' => 'Payment status updated successfully',
                'rental' => new RentalResource($rental->fresh())
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
    }
    
    public function update(Request $request, string $id)
    {
        try {
            $rental = Rental::findOrFail($id);
            
            // Only allow updates for active rentals
            if (!$rental->is_active) {
                return response()->json(['message' => 'Cannot update a completed rental'], 400);
            }
            
            $validated = $request->validate([
                'renter_name' => 'sometimes|string',
                'renter_phone' => 'sometimes|string',
                'passport_number' => 'sometimes|string',
                'pickup_location' => 'sometimes|string',
                'rental_start_date' => 'sometimes|date',
                'rental_end_date' => 'sometimes|date|after_or_equal:rental_start_date',
                'duration_type' => 'sometimes|in:daily,weekly,monthly',
                'duration_count' => 'sometimes|integer|min:1',
                'mileage_at_rental' => 'sometimes|integer',
                'discount_amount' => 'sometimes|numeric|min:0',
                'is_paid' => 'sometimes|boolean',
                'pickup_service_check' => 'sometimes|array',
            ]);
            
            // If duration_type or duration_count changes, recalculate end_date and prices
            $updateData = $validated;
            $recalculatePrice = false;
            
            if (isset($validated['duration_type']) || isset($validated['duration_count'])) {
                $durationType = $validated['duration_type'] ?? $rental->duration_type;
                $durationCount = (int)($validated['duration_count'] ?? $rental->duration_count);
                $startDate = isset($validated['rental_start_date']) 
                    ? Carbon::parse($validated['rental_start_date']) 
                    : Carbon::parse($rental->rental_start_date);
                
                // Get car for price calculation
                $car = $rental->car;
                
                // Calculate end date based on duration
                $endDate = clone $startDate;
                if ($durationType === 'daily') {
                    $endDate->addDays($durationCount);
                    $priceRate = $car->price_per_day;
                } elseif ($durationType === 'weekly') {
                    $endDate->addWeeks($durationCount);
                    $priceRate = $car->price_per_week;
                } else { // monthly
                    $endDate->addMonths($durationCount);
                    $priceRate = $car->price_per_month;
                }
                
                // Calculate prices
                $totalPrice = $priceRate * $durationCount;
                $discountAmount = $validated['discount_amount'] ?? $rental->discount_amount;
                $finalPrice = $totalPrice - $discountAmount;
                
                // Update data with new calculations
                $updateData['rental_end_date'] = $endDate;
                $updateData['price_rate'] = $priceRate;
                $updateData['total_price'] = $totalPrice;
                $updateData['final_price'] = $finalPrice;
                $updateData['final_total'] = $finalPrice; // No additional charges yet
            }
            
            $rental->update($updateData);
            
            return response()->json([
                'message' => 'Rental updated successfully',
                'rental' => new RentalResource($rental->fresh())
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
    }
    
    public function destroy(string $id)
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to delete rentals.'], 403);
        }
        
        try {
            $rental = Rental::findOrFail($id);
            
            // If the rental is active, make the car available again
            if ($rental->is_active) {
                $rental->car->update(['is_available' => true]);
            }
            
            $rental->delete();
            
            return response()->json(['message' => 'Rental removed']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rental not found'], 404);
        }
    }
    
    public function getStatistics()
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to view statistics.'], 403);
        }
        
        $totalRentals = Rental::count();
        $activeRentals = Rental::where('is_active', true)->count();
        $completedRentals = Rental::where('is_active', false)->count();
        $totalRevenue = Rental::sum('final_total');
        
        // Get most rented cars
        $mostRentedCars = Car::withCount('rentals')
            ->orderByDesc('rentals_count')
            ->limit(5)
            ->get()
            ->map(function($car) {
                return [
                    'id' => $car->id,
                    'brand' => $car->brand,
                    'model' => $car->model,
                    'rental_count' => $car->rentals_count
                ];
            });
        
        return response()->json([
            'total_rentals' => $totalRentals,
            'active_rentals' => $activeRentals,
            'completed_rentals' => $completedRentals,
            'total_revenue' => $totalRevenue,
            'most_rented_cars' => $mostRentedCars
        ]);
    }
} 