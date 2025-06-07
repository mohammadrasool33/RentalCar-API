<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    public function index()
    {
        return response()->json([
            "root" => [
                "path" => "/",
                "method" => "GET",
                "description" => "Welcome endpoint",
                "access" => "Public",
                "returns" => [
                    "message" => "Welcome to Car Rental API"
                ],
                "output_example" => [
                    "message" => "Welcome to Car Rental API"
                ]
            ],
            "auth" => [
                "login" => [
                    "path" => "/api/auth/login",
                    "method" => "POST",
                    "description" => "Login admin and get token",
                    "access" => "Public",
                    "returns" => [
                        "token" => "JWT token string",
                        "admin" => [
                            "id" => "admin ID",
                            "email" => "admin email"
                        ]
                    ],
                    "output_example" => [
                        "token" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
                        "admin" => [
                            "id" => "1",
                            "email" => "admin@example.com"
                        ]
                    ]
                ],
                "profile" => [
                    "path" => "/api/auth/profile",
                    "method" => "GET",
                    "description" => "Get admin profile",
                    "access" => "Private",
                    "returns" => [
                        "admin object" => "Admin details excluding password"
                    ],
                    "output_example" => [
                        "_id" => "1",
                        "email" => "admin@example.com",
                        "createdAt" => "2023-06-20T10:23:34.555Z",
                        "updatedAt" => "2023-06-20T10:23:34.555Z"
                    ]
                ]
            ],
            "cars" => [
                "getAllCars" => [
                    "path" => "/api/cars",
                    "method" => "GET",
                    "description" => "Get all cars",
                    "access" => "Private",
                    "returns" => "Array of all car objects sorted by creation date (newest first)",
                    "output_example" => [
                        [
                            "_id" => "1",
                            "name" => "Toyota Camry",
                            "brand" => "Toyota",
                            "model" => "Camry",
                            "description" => "Sedan with good fuel economy",
                            "imageUrl" => "https://example.com/camry.jpg",
                            "pricePerDay" => 50,
                            "pricePerWeek" => 300,
                            "pricePerMonth" => 1200,
                            "currentMileage" => 15000,
                            "isAvailable" => true,
                            "serviceHistory" => [],
                            "createdAt" => "2023-06-20T10:23:34.555Z",
                            "updatedAt" => "2023-06-20T10:23:34.555Z"
                        ]
                    ]
                ],
                "getCarById" => [
                    "path" => "/api/cars/:id",
                    "method" => "GET",
                    "description" => "Get car by ID",
                    "access" => "Private",
                    "returns" => "Single car object",
                    "output_example" => [
                        "_id" => "1",
                        "name" => "Toyota Camry",
                        "brand" => "Toyota",
                        "model" => "Camry",
                        "description" => null,
                        "imageUrl" => "https://example.com/camry.jpg",
                        "pricePerDay" => 50,
                        "pricePerWeek" => 300,
                        "pricePerMonth" => 1200,
                        "currentMileage" => 15000,
                        "isAvailable" => true,
                        "serviceHistory" => [],
                        "createdAt" => "2023-06-20T10:23:34.555Z",
                        "updatedAt" => "2023-06-20T10:23:34.555Z"
                    ]
                ],
                "createCar" => [
                    "path" => "/api/cars",
                    "method" => "POST",
                    "description" => "Create a new car",
                    "access" => "Private",
                    "returns" => "Newly created car object",
                    "output_example" => [
                        "_id" => "1",
                        "name" => "Toyota Camry",
                        "brand" => "Toyota",
                        "model" => "Camry",
                        "description" => "Sedan with good fuel economy",
                        "imageUrl" => "https://example.com/camry.jpg",
                        "pricePerDay" => 50,
                        "pricePerWeek" => 300,
                        "pricePerMonth" => 1200,
                        "currentMileage" => 15000,
                        "isAvailable" => true,
                        "serviceHistory" => [],
                        "createdAt" => "2023-06-20T10:23:34.555Z",
                        "updatedAt" => "2023-06-20T10:23:34.555Z"
                    ]
                ],
                "updateCar" => [
                    "path" => "/api/cars/:id",
                    "method" => "PUT",
                    "description" => "Update a car",
                    "access" => "Private",
                    "returns" => "Updated car object",
                    "output_example" => [
                        "_id" => "1",
                        "name" => "Toyota Camry",
                        "brand" => "Toyota",
                        "model" => "Camry",
                        "description" => "Updated description",
                        "imageUrl" => "https://example.com/camry_new.jpg",
                        "pricePerDay" => 55,
                        "pricePerWeek" => 330,
                        "pricePerMonth" => 1320,
                        "currentMileage" => 15200,
                        "isAvailable" => true,
                        "serviceHistory" => [],
                        "createdAt" => "2023-06-20T10:23:34.555Z",
                        "updatedAt" => "2023-06-21T08:15:22.123Z"
                    ]
                ],
                "deleteCar" => [
                    "path" => "/api/cars/:id",
                    "method" => "DELETE",
                    "description" => "Delete a car",
                    "access" => "Private",
                    "returns" => [
                        "message" => "Car removed"
                    ],
                    "output_example" => [
                        "message" => "Car removed"
                    ]
                ],
                "getServiceShopNames" => [
                    "path" => "/api/cars/service-shops",
                    "method" => "GET",
                    "description" => "Get all unique service shop names",
                    "access" => "Private",
                    "returns" => "Array of unique shop names from service history",
                    "output_example" => [
                        "AutoCare Center",
                        "Quick Service",
                        "Top Mechanics"
                    ]
                ],
                "getServiceHistory" => [
                    "path" => "/api/cars/service-history",
                    "method" => "GET",
                    "description" => "Get detailed service history records",
                    "access" => "Private",
                    "returns" => "Array of service history records with car details",
                    "output_example" => [
                        [
                            "date" => "2023-06-15T14:30:00.000Z",
                            "shopName" => "AutoCare Center",
                            "services" => ["oilChange", "tireRotation", "brakeCheck"],
                            "notes" => "Regular maintenance",
                            "carDetails" => [
                                "id" => "1",
                                "name" => "Toyota Camry",
                                "brand" => "Toyota",
                                "model" => "Camry"
                            ]
                        ]
                    ]
                ]
            ],
            "rentals" => [
                "getAllRentals" => [
                    "path" => "/api/rentals",
                    "method" => "GET",
                    "description" => "Get all rentals",
                    "access" => "Private",
                    "returns" => "Array of all rental objects sorted by creation date (newest first)",
                    "output_example" => [
                        [
                            "_id" => "1",
                            "car" => "1",
                            "carDetails" => [
                                "name" => "Toyota Camry",
                                "brand" => "Toyota",
                                "model" => "Camry"
                            ],
                            "renterName" => "John Doe",
                            "renterPhone" => "555-123-4567",
                            "passportNumber" => "AB123456",
                            "pickupLocation" => "Main Office",
                            "rentalStartDate" => "2023-06-20T10:00:00.000Z",
                            "rentalEndDate" => "2023-06-27T10:00:00.000Z",
                            "durationType" => "week",
                            "durationCount" => 1,
                            "priceRate" => 300,
                            "totalPrice" => 300,
                            "discountAmount" => 0,
                            "finalPrice" => 300,
                            "mileageAtRental" => 15000,
                            "isActive" => true,
                            "isPaid" => true,
                            "createdAt" => "2023-06-20T09:45:00.000Z",
                            "updatedAt" => "2023-06-20T09:45:00.000Z"
                        ]
                    ]
                ],
                "getRentalById" => [
                    "path" => "/api/rentals/:id",
                    "method" => "GET",
                    "description" => "Get rental by ID",
                    "access" => "Private",
                    "returns" => "Single rental object",
                    "output_example" => [
                        "_id" => "1",
                        "car" => "1",
                        "carDetails" => [
                            "name" => "Toyota Camry",
                            "brand" => "Toyota",
                            "model" => "Camry"
                        ],
                        "renterName" => "John Doe",
                        "renterPhone" => "555-123-4567",
                        "passportNumber" => "AB123456",
                        "pickupLocation" => "Main Office",
                        "rentalStartDate" => "2023-06-20T10:00:00.000Z",
                        "rentalEndDate" => "2023-06-27T10:00:00.000Z",
                        "durationType" => "week",
                        "durationCount" => 1,
                        "priceRate" => 300,
                        "totalPrice" => 300,
                        "discountAmount" => 0,
                        "finalPrice" => 300,
                        "mileageAtRental" => 15000,
                        "isActive" => true,
                        "isPaid" => true,
                        "createdAt" => "2023-06-20T09:45:00.000Z",
                        "updatedAt" => "2023-06-20T09:45:00.000Z"
                    ]
                ],
                "getRentalsByCar" => [
                    "path" => "/api/rentals/car/:carId",
                    "method" => "GET",
                    "description" => "Get rentals by car",
                    "access" => "Private",
                    "returns" => "Array of rentals for a specific car",
                    "output_example" => [
                        [
                            "_id" => "1",
                            "car" => "1",
                            "carDetails" => [
                                "name" => "Toyota Camry",
                                "brand" => "Toyota",
                                "model" => "Camry"
                            ],
                            "renterName" => "John Doe",
                            "renterPhone" => "555-123-4567",
                            "passportNumber" => "AB123456",
                            "pickupLocation" => "Main Office",
                            "rentalStartDate" => "2023-06-20T10:00:00.000Z",
                            "rentalEndDate" => "2023-06-27T10:00:00.000Z",
                            "durationType" => "week",
                            "durationCount" => 1,
                            "priceRate" => 300,
                            "totalPrice" => 300,
                            "discountAmount" => 0,
                            "finalPrice" => 300,
                            "mileageAtRental" => 15000,
                            "isActive" => true,
                            "isPaid" => true,
                            "createdAt" => "2023-06-20T09:45:00.000Z",
                            "updatedAt" => "2023-06-20T09:45:00.000Z"
                        ]
                    ]
                ],
                "createRental" => [
                    "path" => "/api/rentals",
                    "method" => "POST",
                    "description" => "Create a new rental",
                    "access" => "Private",
                    "returns" => "Newly created rental object",
                    "output_example" => [
                        "_id" => "1",
                        "car" => "1",
                        "carDetails" => [
                            "name" => "Toyota Camry",
                            "brand" => "Toyota",
                            "model" => "Camry"
                        ],
                        "renterName" => "John Doe",
                        "renterPhone" => "555-123-4567",
                        "passportNumber" => "AB123456",
                        "pickupLocation" => "Main Office",
                        "rentalStartDate" => "2023-06-20T10:00:00.000Z",
                        "rentalEndDate" => "2023-06-27T10:00:00.000Z",
                        "durationType" => "week",
                        "durationCount" => 1,
                        "priceRate" => 300,
                        "totalPrice" => 300,
                        "discountAmount" => 0,
                        "finalPrice" => 300,
                        "mileageAtRental" => 15000,
                        "isActive" => true,
                        "isPaid" => true,
                        "additionalNotes" => "",
                        "pickupServiceCheck" => [
                            "oilCheck" => true,
                            "brakeCheck" => true,
                            "lightCheck" => true,
                            "serviceNotes" => "Services checked: OilCheck, BrakeCheck, LightCheck"
                        ],
                        "createdAt" => "2023-06-20T09:45:00.000Z",
                        "updatedAt" => "2023-06-20T09:45:00.000Z"
                    ]
                ],
                "returnCar" => [
                    "path" => "/api/rentals/return",
                    "method" => "POST",
                    "description" => "Process car return",
                    "access" => "Private",
                    "returns" => [
                        "message" => "Car returned successfully",
                        "rental" => "Updated rental object"
                    ],
                    "output_example" => [
                        "message" => "Car returned successfully",
                        "rental" => [
                            "_id" => "1",
                            "car" => "1",
                            "carDetails" => [
                                "name" => "Toyota Camry",
                                "brand" => "Toyota",
                                "model" => "Camry"
                            ],
                            "renterName" => "John Doe",
                            "renterPhone" => "555-123-4567",
                            "passportNumber" => "AB123456",
                            "pickupLocation" => "Main Office",
                            "returnLocation" => "Main Office",
                            "rentalStartDate" => "2023-06-20T10:00:00.000Z",
                            "rentalEndDate" => "2023-06-27T10:00:00.000Z",
                            "returnDate" => "2023-06-27T09:30:00.000Z",
                            "durationType" => "week",
                            "durationCount" => 1,
                            "priceRate" => 300,
                            "totalPrice" => 300,
                            "discountAmount" => 0,
                            "finalPrice" => 300,
                            "additionalCharges" => 0,
                            "finalTotal" => 300,
                            "mileageAtRental" => 15000,
                            "mileageAtReturn" => 15350,
                            "isActive" => false,
                            "isPaid" => true,
                            "comments" => "",
                            "returnServiceCheck" => [
                                "oilCheck" => true,
                                "brakeCheck" => true,
                                "lightCheck" => true,
                                "serviceNotes" => "Return services checked: OilCheck, BrakeCheck, LightCheck"
                            ],
                            "createdAt" => "2023-06-20T09:45:00.000Z",
                            "updatedAt" => "2023-06-27T09:30:00.000Z"
                        ]
                    ]
                ],
                "updatePaymentStatus" => [
                    "path" => "/api/rentals/payment",
                    "method" => "POST",
                    "description" => "Update payment status",
                    "access" => "Private",
                    "returns" => [
                        "message" => "Payment status updated successfully",
                        "rental" => "Updated rental object"
                    ],
                    "output_example" => [
                        "message" => "Payment status updated successfully",
                        "rental" => [
                            "_id" => "1",
                            "car" => "1",
                            "carDetails" => [
                                "name" => "Toyota Camry",
                                "brand" => "Toyota",
                                "model" => "Camry"
                            ],
                            "renterName" => "John Doe",
                            "renterPhone" => "555-123-4567",
                            "passportNumber" => "AB123456",
                            "pickupLocation" => "Main Office",
                            "rentalStartDate" => "2023-06-20T10:00:00.000Z",
                            "rentalEndDate" => "2023-06-27T10:00:00.000Z",
                            "durationType" => "week",
                            "durationCount" => 1,
                            "priceRate" => 300,
                            "totalPrice" => 300,
                            "discountAmount" => 0,
                            "finalPrice" => 300,
                            "mileageAtRental" => 15000,
                            "isActive" => true,
                            "isPaid" => true,
                            "createdAt" => "2023-06-20T09:45:00.000Z",
                            "updatedAt" => "2023-06-20T15:20:00.000Z"
                        ]
                    ]
                ],
                "deleteRental" => [
                    "path" => "/api/rentals/:id",
                    "method" => "DELETE",
                    "description" => "Delete a rental",
                    "access" => "Private",
                    "returns" => [
                        "message" => "Rental removed"
                    ],
                    "output_example" => [
                        "message" => "Rental removed"
                    ]
                ]
            ]
        ]);
    }
} 