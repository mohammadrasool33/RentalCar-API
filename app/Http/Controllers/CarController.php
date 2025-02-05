<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreCarRequest;
use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Models\Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
class CarController extends Controller
{
    public function index()
    {

        return CarResource::collection(Car::with('images')->get());
    }
    public function store(StoreCarRequest $request)
    {
        if(Gate::denies('delete')){
            return response()->json(['message' => 'You are not authorized to store.'], 403);
        }
            $validated = $request->validated();
            $car = Car::create(
                [
                    'name' => $validated['name'],
                    'daily_price' => $validated['daily_price'],
                    'weekly_price' => $validated['weekly_price'],
                    'monthly_price' => $validated['monthly_price'],
                    'brand' => $validated['brand'],
                    'year' => $validated['year'],
                    'is_available'=>true
                ]
            );
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
            $car = Car::findOrFail($id);
            return new CarResource($car);
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
            return response()->json(['message' => 'Car deleted'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Car not found'], 404);
        }
    }
}
