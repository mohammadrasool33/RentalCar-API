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
            
            // Primary guarantor fields
            'primary_guarantor_name'     => 'required|string',
            'primary_guarantor_phone'    => 'required|string',
            'primary_guarantor_id_type'  => 'sometimes|string',
            'primary_guarantor_id_number' => 'required|string',
            
            // Dedicated passport field
            'passport'         => 'sometimes|string',
            
            // Secondary guarantor fields
            'secondary_guarantor_name'     => 'sometimes|nullable|string',
            'secondary_guarantor_phone'    => 'sometimes|nullable|string',
            'secondary_guarantor_id_type'  => 'sometimes|nullable|string',
            'secondary_guarantor_id_number' => 'sometimes|nullable|string',
            
            // Legacy field support
            'renter_name'      => 'sometimes|string',
            'renter_phone'     => 'sometimes|string',
            'passport_number'  => 'sometimes|string',
            
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
        
        // Handle legacy field mapping
        $primaryGuarantorName = $validated['primary_guarantor_name'] ?? $validated['renter_name'] ?? null;
        $primaryGuarantorPhone = $validated['primary_guarantor_phone'] ?? $validated['renter_phone'] ?? null;
        
        // Handle passport fields - prioritize the dedicated passport field
        $passport = $validated['passport'] ?? $validated['passport_number'] ?? null;
        if ($passport) {
            $primaryGuarantorIdType = 'passport';
            $primaryGuarantorIdNumber = $passport;
        } else {
            $primaryGuarantorIdNumber = $validated['primary_guarantor_id_number'] ?? null;
            $primaryGuarantorIdType = isset($validated['passport_number']) 
                ? 'passport' 
                : ($validated['primary_guarantor_id_type'] ?? 'passport');
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
            
            // Guarantor information
            'primary_guarantor_name'     => $primaryGuarantorName,
            'primary_guarantor_phone'    => $primaryGuarantorPhone,
            'primary_guarantor_id_type'  => $primaryGuarantorIdType,
            'primary_guarantor_id_number' => $primaryGuarantorIdNumber,
            'passport'                   => $passport, // Add dedicated passport field
            'secondary_guarantor_name'   => $validated['secondary_guarantor_name'] ?? null,
            'secondary_guarantor_phone'  => $validated['secondary_guarantor_phone'] ?? null,
            'secondary_guarantor_id_type' => $validated['secondary_guarantor_id_type'] ?? null,
            'secondary_guarantor_id_number' => $validated['secondary_guarantor_id_number'] ?? null,
            
            'pickup_location'   => $validated['pickup_location'],
            'return_location'   => $validated['return_location'] ?? null,
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
            
            if ($rentals->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'message' => 'No rentals found for this car'
                ], 200);
            }
            
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
            'additional_charges' => 'sometimes|numeric|min:0',
            'return_location' => 'sometimes|string',
            'return_service_check' => 'sometimes|array',
            'comments' => 'sometimes|string',
        ]);
        
        try {
            $rental = Rental::findOrFail($validated['id']);
            
            // Check if the rental is already returned
            if (!$rental->is_active) {
                return response()->json(['message' => 'This rental has already been returned'], 400);
            }
            
            // Mark the car as available again
            $rental->car->update(['is_available' => true]);
            
            // Calculate any additional charges
            $additionalCharges = $validated['additional_charges'] ?? 0;
            $finalTotal = $rental->final_price + $additionalCharges;
            
            // Update rental record
            $rental->update([
                'mileage_at_return' => $validated['mileage_at_return'],
                'return_date' => now(),
                'return_location' => $validated['return_location'] ?? $rental->pickup_location,
                'additional_charges' => $additionalCharges,
                'final_total' => $finalTotal,
                'is_active' => false,
                'comments' => $validated['comments'] ?? $rental->comments,
                'return_service_check' => $validated['return_service_check'] ?? null,
            ]);
            
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
                // Primary guarantor fields
                'primary_guarantor_name'     => 'sometimes|string',
                'primary_guarantor_phone'    => 'sometimes|string',
                'primary_guarantor_id_type'  => 'sometimes|string',
                'primary_guarantor_id_number' => 'sometimes|string',
                
                // Dedicated passport field
                'passport'         => 'sometimes|string',
                
                // Secondary guarantor fields
                'secondary_guarantor_name'     => 'sometimes|nullable|string',
                'secondary_guarantor_phone'    => 'sometimes|nullable|string',
                'secondary_guarantor_id_type'  => 'sometimes|nullable|string',
                'secondary_guarantor_id_number' => 'sometimes|nullable|string',
                
                // Legacy field support
                'renter_name'      => 'sometimes|string',
                'renter_phone'     => 'sometimes|string',
                'passport_number'  => 'sometimes|string',
                
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
            
            // Handle legacy field mapping
            if (isset($validated['renter_name'])) {
                $validated['primary_guarantor_name'] = $validated['renter_name'];
                unset($validated['renter_name']);
            }
            
            if (isset($validated['renter_phone'])) {
                $validated['primary_guarantor_phone'] = $validated['renter_phone'];
                unset($validated['renter_phone']);
            }
            
            // Handle passport fields
            if (isset($validated['passport'])) {
                // No need to modify anything as the model will handle this
            } else if (isset($validated['passport_number'])) {
                $validated['passport'] = $validated['passport_number'];
                unset($validated['passport_number']);
            }
            
            // If duration_type or duration_count changes, recalculate end_date and prices
            $updateData = $validated;
            $recalculatePrice = false;
            
            if (isset($validated['duration_type']) || isset($validated['duration_count']) || isset($validated['rental_start_date'])) {
                $recalculatePrice = true;
                
                $durationType = $validated['duration_type'] ?? $rental->duration_type;
                $durationCount = $validated['duration_count'] ?? $rental->duration_count;
                $startDate = isset($validated['rental_start_date']) ? Carbon::parse($validated['rental_start_date']) : Carbon::parse($rental->rental_start_date);
                
                // Calculate new end date
                $endDate = clone $startDate;
                if ($durationType === 'daily') {
                    $endDate->addDays($durationCount);
                    $priceRate = $rental->car->price_per_day;
                } elseif ($durationType === 'weekly') {
                    $endDate->addWeeks($durationCount);
                    $priceRate = $rental->car->price_per_week;
                } else { // monthly
                    $endDate->addMonths($durationCount);
                    $priceRate = $rental->car->price_per_month;
                }
                
                // Calculate prices
                $totalPrice = $priceRate * $durationCount;
                $discountAmount = $validated['discount_amount'] ?? $rental->discount_amount;
                $finalPrice = $totalPrice - $discountAmount;
                
                $updateData['rental_end_date'] = $endDate;
                $updateData['price_rate'] = $priceRate;
                $updateData['total_price'] = $totalPrice;
                $updateData['final_price'] = $finalPrice;
                $updateData['final_total'] = $finalPrice; // Since the rental is active, additional_charges will be 0
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
        // Count of active rentals
        $activeRentals = Rental::where('is_active', true)->count();
        
        // Count of completed rentals
        $completedRentals = Rental::where('is_active', false)->count();
        
        // Total revenue
        $totalRevenue = Rental::sum('final_total');
        
        // Revenue from active rentals
        $activeRevenue = Rental::where('is_active', true)->sum('final_price');
        
        // Revenue from completed rentals
        $completedRevenue = Rental::where('is_active', false)->sum('final_total');
        
        // Average rental duration in days
        $avgDuration = Rental::all()->map(function ($rental) {
            $start = Carbon::parse($rental->rental_start_date);
            $end = $rental->is_active ? Carbon::parse($rental->rental_end_date) : Carbon::parse($rental->return_date);
            return $start->diffInDays($end);
        })->avg();
        
        return response()->json([
            'active_rentals' => $activeRentals,
            'completed_rentals' => $completedRentals,
            'total_revenue' => $totalRevenue,
            'active_revenue' => $activeRevenue,
            'completed_revenue' => $completedRevenue,
            'avg_duration_days' => round($avgDuration, 1)
        ]);
    }
} 