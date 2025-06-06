<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreCarRequest;
use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Models\Image;
use App\Models\ServiceHistory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CarController extends Controller
{
    public function index()
    {
        return CarResource::collection(Car::with(['images', 'serviceHistory'])->latest()->get());
    }
    
    public function store(StoreCarRequest $request)
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to store.'], 403);
        }
            $validated = $request->validated();
            
            // Build the car data array
            $carData = [
                'name' => $validated['name'],
                'price_per_day' => $validated['daily_price'],
                'price_per_week' => $validated['weekly_price'],
                'price_per_month' => $validated['monthly_price'],
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'year' => $validated['year'],
                'current_mileage' => $validated['current_mileage'],
                'is_available' => true
            ];
            
            // Only add description if it exists
            if (isset($validated['description'])) {
                $carData['description'] = $validated['description'];
            }
            
            $car = Car::create($carData);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('cars', 'public'); // Store in storage/app/public/cars
                Image::create([
                    'car_id' => $car->id,
                    'path' => $imagePath
                ]);
            }
        }
        return new CarResource($car);
    }
    
    public function show(string $id)
    {
        try {
            $car = Car::with(['images', 'serviceHistory'])->findOrFail($id);
            return new CarResource($car);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    }
    
    public function update(Request $request, string $id)
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to update.'], 403);
        }
        
        try {
            $car = Car::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'brand' => 'sometimes|string|max:255',
                'model' => 'sometimes|string|max:255',
                'year' => 'sometimes|string|max:4',
                'description' => 'sometimes|nullable|string',
                'daily_price' => 'sometimes|numeric|min:0',
                'weekly_price' => 'sometimes|numeric|min:0',
                'monthly_price' => 'sometimes|numeric|min:0',
                'current_mileage' => 'sometimes|integer|min:0',
                'is_available' => 'sometimes|boolean',
                'images.*' => 'sometimes|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);
            
            // Map request field names to database field names
            $updateData = [];
            if (isset($validated['name'])) $updateData['name'] = $validated['name'];
            if (isset($validated['brand'])) $updateData['brand'] = $validated['brand'];
            if (isset($validated['model'])) $updateData['model'] = $validated['model'];
            if (isset($validated['year'])) $updateData['year'] = $validated['year'];
            if (isset($validated['description'])) $updateData['description'] = $validated['description'];
            if (isset($validated['daily_price'])) $updateData['price_per_day'] = $validated['daily_price'];
            if (isset($validated['weekly_price'])) $updateData['price_per_week'] = $validated['weekly_price'];
            if (isset($validated['monthly_price'])) $updateData['price_per_month'] = $validated['monthly_price'];
            if (isset($validated['current_mileage'])) $updateData['current_mileage'] = $validated['current_mileage'];
            if (isset($validated['is_available'])) $updateData['is_available'] = $validated['is_available'];
            
            $car->update($updateData);
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('cars', 'public');
                    Image::create([
                        'car_id' => $car->id,
                        'path' => $imagePath
                    ]);
                }
            }
            
            return new CarResource($car->fresh(['images', 'serviceHistory']));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    }
    
    public function destroy(string $id)
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to delete.'], 403);
        }
        try {
            $car = Car::findOrFail($id);
            $car->delete();
            return response()->json(['message' => 'Car removed'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    }
    
    public function getServiceShopNames()
    {
        $shopNames = ServiceHistory::select('shop_name')
            ->distinct()
            ->pluck('shop_name')
            ->toArray();
            
        return response()->json($shopNames);
    }
    
    public function getServiceHistory()
    {
        $serviceHistory = ServiceHistory::with('car:id,name,brand,model')
            ->latest('date')
            ->get()
            ->map(function($record) {
                return [
                    'date' => $record->date,
                    'shopName' => $record->shopName,
                    'services' => $record->services,
                    'notes' => $record->notes,
                    'carDetails' => $record->carDetails
                ];
            });
            
        return response()->json($serviceHistory);
    }
}
