<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceBeautician;
use App\Models\BeauticianAvailability;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Role;
use Carbon\Carbon;

class PerfectBookingTestSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Starting Perfect Booking Test Seeder...');

        // Clear existing test data
        $this->clearTestData();

        // Create test roles
        $this->createRoles();

        // Create test branches
        $this->createBranches();

        // Create test categories and services
        $this->createCategoriesAndServices();

        // Create test beauticians with varied skills
        $this->createBeauticians();

        // Create test customers
        $this->createCustomers();

        // Set up beautician availabilities (realistic schedules)
        $this->createBeauticianAvailabilities();

        // Create some existing appointments to test conflict detection
        $this->createExistingAppointments();

        // Create service-beautician associations
        $this->createServiceBeauticianAssociations();

        $this->command->info('✅ Perfect Booking Test Seeder completed successfully!');
        $this->printTestScenarios();
    }

    private function clearTestData()
    {
        $this->command->info('🧹 Clearing existing test data...');
        
        AppointmentService::truncate();
        Appointment::truncate();
        ServiceBeautician::truncate();
        BeauticianAvailability::truncate();
        Service::truncate();
        Category::truncate();
        User::where('email', 'like', '%test%')->delete();
    }

    private function createRoles()
    {
        $this->command->info('👥 Creating roles...');

        $roles = [
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'beautician'],
            ['id' => 3, 'name' => 'customer'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }
    }

    private function createBranches()
    {
        $this->command->info('🏢 Creating test branches...');

        $branches = [
            [
                'name' => 'Downtown Salon',
                'address' => '123 Main St, City Center',
                'contact' => '+1-555-0101 | downtown@testsalon.com'
            ],
            [
                'name' => 'Uptown Beauty',
                'address' => '456 Oak Ave, Uptown',
                'contact' => '+1-555-0102 | uptown@testsalon.com'
            ],
            [
                'name' => 'Westside Spa',
                'address' => '789 Pine Rd, Westside',
                'contact' => '+1-555-0103 | westside@testsalon.com'
            ]
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }

    private function createCategoriesAndServices()
    {
        $this->command->info('💄 Creating categories and services...');

        // Categories
        $categories = [
            ['name' => 'Hair Services'],
            ['name' => 'Facial Treatments'],
            ['name' => 'Nail Services'],
            ['name' => 'Massage Therapy'],
            ['name' => 'Makeup Services']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Services with realistic durations and prices
        $services = [
            // Hair Services (Category 1)
            ['name' => 'Basic Haircut', 'category_id' => 1, 'duration' => 30, 'price' => 35.00],
            ['name' => 'Hair Wash & Blow Dry', 'category_id' => 1, 'duration' => 45, 'price' => 40.00],
            ['name' => 'Hair Coloring', 'category_id' => 1, 'duration' => 120, 'price' => 85.00],
            ['name' => 'Hair Styling', 'category_id' => 1, 'duration' => 60, 'price' => 50.00],
            ['name' => 'Deep Conditioning', 'category_id' => 1, 'duration' => 30, 'price' => 25.00],

            // Facial Treatments (Category 2)
            ['name' => 'Classic Facial', 'category_id' => 2, 'duration' => 60, 'price' => 70.00],
            ['name' => 'Anti-Aging Facial', 'category_id' => 2, 'duration' => 90, 'price' => 95.00],
            ['name' => 'Express Facial', 'category_id' => 2, 'duration' => 30, 'price' => 45.00],
            ['name' => 'Acne Treatment', 'category_id' => 2, 'duration' => 75, 'price' => 80.00],

            // Nail Services (Category 3)
            ['name' => 'Manicure', 'category_id' => 3, 'duration' => 45, 'price' => 30.00],
            ['name' => 'Pedicure', 'category_id' => 3, 'duration' => 60, 'price' => 40.00],
            ['name' => 'Gel Polish', 'category_id' => 3, 'duration' => 30, 'price' => 25.00],
            ['name' => 'Nail Art', 'category_id' => 3, 'duration' => 45, 'price' => 35.00],

            // Massage Therapy (Category 4)
            ['name' => 'Swedish Massage', 'category_id' => 4, 'duration' => 60, 'price' => 80.00],
            ['name' => 'Deep Tissue Massage', 'category_id' => 4, 'duration' => 90, 'price' => 110.00],
            ['name' => 'Hot Stone Massage', 'category_id' => 4, 'duration' => 75, 'price' => 95.00],

            // Makeup Services (Category 5)
            ['name' => 'Bridal Makeup', 'category_id' => 5, 'duration' => 90, 'price' => 120.00],
            ['name' => 'Event Makeup', 'category_id' => 5, 'duration' => 60, 'price' => 75.00],
            ['name' => 'Makeup Consultation', 'category_id' => 5, 'duration' => 30, 'price' => 40.00],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }

    private function createBeauticians()
    {
        $this->command->info('👩‍💄 Creating beauticians with different specializations...');

        $beauticians = [
            // Multi-skilled senior beautician
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.test@salon.com',
                'password' => bcrypt('password'),
                'role' => 2,
                'branch_id' => 1,
            ],
            // Hair specialist
            [
                'name' => 'Emily Davis',
                'email' => 'emily.test@salon.com',
                'password' => bcrypt('password'),
                'role' => 2,
                'branch_id' => 1,
            ],
            // Facial and makeup specialist
            [
                'name' => 'Jessica Wilson',
                'email' => 'jessica.test@salon.com',
                'password' => bcrypt('password'),
                'role' => 2,
                'branch_id' => 2,
            ],
            // Nail specialist
            [
                'name' => 'Amanda Brown',
                'email' => 'amanda.test@salon.com',
                'password' => bcrypt('password'),
                'role' => 2,
                'branch_id' => 2,
            ],
            // Massage therapist
            [
                'name' => 'Michelle Taylor',
                'email' => 'michelle.test@salon.com',
                'password' => bcrypt('password'),
                'role' => 2,
                'branch_id' => 3,
            ],
            // Junior beautician (limited services)
            [
                'name' => 'Lisa Garcia',
                'email' => 'lisa.test@salon.com',
                'password' => bcrypt('password'),
                'role' => 2,
                'branch_id' => 1,
            ]
        ];

        foreach ($beauticians as $beautician) {
            User::create($beautician);
        }
    }

    private function createCustomers()
    {
        $this->command->info('👤 Creating test customers...');

        $customers = [
            [
                'name' => 'Alice Smith',
                'email' => 'alice.test@customer.com',
                'password' => bcrypt('password'),
                'role' => 3,
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob.test@customer.com',
                'password' => bcrypt('password'),
                'role' => 3,
            ],
            [
                'name' => 'Carol Williams',
                'email' => 'carol.test@customer.com',
                'password' => bcrypt('password'),
                'role' => 3,
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.test@customer.com',
                'password' => bcrypt('password'),
                'role' => 3,
            ],
            [
                'name' => 'Emma Davis',
                'email' => 'emma.test@customer.com',
                'password' => bcrypt('password'),
                'role' => 3,
            ]
        ];

        foreach ($customers as $customer) {
            User::create($customer);
        }
    }

    private function createBeauticianAvailabilities()
    {
        $this->command->info('📅 Setting up beautician schedules...');

        $beauticians = User::where('role', 2)->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($beauticians as $index => $beautician) {
            // Create different schedule patterns based on index
            switch ($index % 3) {
                case 0: // Full-time schedule
                    foreach ($daysOfWeek as $day) {
                        BeauticianAvailability::updateOrCreate(
                            [
                                'beautician_id' => $beautician->id,
                                'day_of_week' => $day,
                            ],
                            [
                                'start_time' => '09:00:00',
                                'end_time' => '17:00:00'
                            ]
                        );
                    }
                    break;

                case 1: // Part-time schedule
                    foreach (['Monday', 'Wednesday', 'Friday', 'Saturday'] as $day) {
                        BeauticianAvailability::updateOrCreate(
                            [
                                'beautician_id' => $beautician->id,
                                'day_of_week' => $day,
                            ],
                            [
                                'start_time' => '10:00:00',
                                'end_time' => '16:00:00'
                            ]
                        );
                    }
                    break;

                case 2: // Split shift schedule
                    foreach (['Tuesday', 'Thursday', 'Friday', 'Saturday'] as $day) {
                        // Morning shift only (afternoon will be separate entry)
                        BeauticianAvailability::updateOrCreate(
                            [
                                'beautician_id' => $beautician->id,
                                'day_of_week' => $day,
                            ],
                            [
                                'start_time' => '09:00:00',
                                'end_time' => '13:00:00'
                            ]
                        );
                    }
                    break;
            }
        }
    }

    private function createServiceBeauticianAssociations()
    {
        $this->command->info('🔗 Creating service-beautician associations...');

        $beauticians = User::where('role', 2)->get();
        $services = Service::all();

        foreach ($beauticians as $index => $beautician) {
            // Assign specializations based on beautician index/id
            switch ($index % 6) {
                case 0: // Sarah - All Services
                    foreach ($services as $service) {
                        ServiceBeautician::create([
                            'service_id' => $service->id,
                            'beautician_id' => $beautician->id
                        ]);
                    }
                    break;

                case 1: // Emily - Hair specialist
                    $hairServices = $services->where('category_id', 1);
                    foreach ($hairServices as $service) {
                        ServiceBeautician::create([
                            'service_id' => $service->id,
                            'beautician_id' => $beautician->id
                        ]);
                    }
                    break;

                case 2: // Jessica - Facial and makeup specialist
                    $facialServices = $services->whereIn('category_id', [2, 5]);
                    foreach ($facialServices as $service) {
                        ServiceBeautician::create([
                            'service_id' => $service->id,
                            'beautician_id' => $beautician->id
                        ]);
                    }
                    break;

                case 3: // Amanda - Nail specialist
                    $nailServices = $services->where('category_id', 3);
                    foreach ($nailServices as $service) {
                        ServiceBeautician::create([
                            'service_id' => $service->id,
                            'beautician_id' => $beautician->id
                        ]);
                    }
                    break;

                case 4: // Michelle - Massage therapist
                    $massageServices = $services->where('category_id', 4);
                    foreach ($massageServices as $service) {
                        ServiceBeautician::create([
                            'service_id' => $service->id,
                            'beautician_id' => $beautician->id
                        ]);
                    }
                    break;

                case 5: // Lisa - Junior beautician (limited services)
                    $basicServices = $services->whereIn('id', [1, 2, 5, 8, 10, 12]); // Basic cuts, washes, express services
                    foreach ($basicServices as $service) {
                        ServiceBeautician::create([
                            'service_id' => $service->id,
                            'beautician_id' => $beautician->id
                        ]);
                    }
                    break;
            }
        }
    }

    private function createExistingAppointments()
    {
        $this->command->info('📋 Creating existing appointments for conflict testing...');

        $beauticians = User::where('role', 2)->take(3)->get();
        $customers = User::where('role', 3)->take(3)->get();
        $testDate = Carbon::tomorrow()->format('Y-m-d');

        // Create some strategic appointments to test conflict detection
        $appointments = [
            // Sarah (beautician 1) - busy morning
            [
                'customer_id' => $customers[0]->id,
                'beautician_id' => $beauticians[0]->id,
                'branch_id' => 1,
                'date' => $testDate,
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
                'status' => 'SCHEDULED',
                'receipt_number' => 'TEST001'
            ],
            [
                'customer_id' => $customers[1]->id,
                'beautician_id' => $beauticians[0]->id,
                'branch_id' => 1,
                'date' => $testDate,
                'start_time' => '11:00:00',
                'end_time' => '12:00:00',
                'status' => 'CONFIRMED',
                'receipt_number' => 'TEST002'
            ],
            // Emily (beautician 2) - afternoon appointment
            [
                'customer_id' => $customers[2]->id,
                'beautician_id' => $beauticians[1]->id,
                'branch_id' => 1,
                'date' => $testDate,
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'status' => 'SCHEDULED',
                'receipt_number' => 'TEST003'
            ]
        ];

        foreach ($appointments as $appointment) {
            $apt = Appointment::create($appointment);
            
            // Add appointment services
            AppointmentService::create([
                'appointment_id' => $apt->id,
                'service_id' => 1, // Basic haircut
                'price' => 35.00,
                'duration' => 30
            ]);
        }
    }

    private function printTestScenarios()
    {
        $this->command->info('');
        $this->command->info('🧪 TEST SCENARIOS CREATED:');
        $this->command->info('================================');
        
        $testDate = Carbon::tomorrow()->format('Y-m-d');
        $dayOfWeek = Carbon::tomorrow()->format('l');
        
        $this->command->info("📅 Test Date: {$testDate} ({$dayOfWeek})");
        $this->command->info('');
        
        $this->command->info('👩‍💄 BEAUTICIANS & SPECIALIZATIONS:');
        $beauticians = User::where('role', 2)->get();
        $specializations = ['All Services', 'Hair Services', 'Facial & Makeup', 'Nail Services', 'Massage Therapy', 'Basic Services'];
        
        foreach ($beauticians as $index => $beautician) {
            $availability = BeauticianAvailability::where('beautician_id', $beautician->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();
            
            $availabilityStr = $availability 
                ? "{$availability->start_time} - {$availability->end_time}"
                : "Not available";
                
            $specialization = $specializations[$index % 6] ?? 'General';
            $this->command->info("  • {$beautician->name} ({$specialization}) - {$availabilityStr}");
        }
        
        $this->command->info('');
        $this->command->info('📋 EXISTING APPOINTMENTS:');
        $appointments = Appointment::where('date', $testDate)->with('beautician', 'customer')->get();
        foreach ($appointments as $appointment) {
            $this->command->info("  • {$appointment->start_time}-{$appointment->end_time}: {$appointment->beautician->name} with {$appointment->customer->name}");
        }
        
        $this->command->info('');
        $this->command->info('🧪 SUGGESTED TEST CASES:');
        $this->command->info('');
        
        $this->command->info('1. SINGLE SERVICE BOOKING:');
        $this->command->info('   POST /api/v1/appointments/smart-booking');
        $this->command->info('   {');
        $this->command->info('     "customer_id": ' . User::where('role', 3)->first()->id . ',');
        $this->command->info('     "service_ids": [1],');
        $this->command->info('     "date": "' . $testDate . '",');
        $this->command->info('     "branch_id": 1');
        $this->command->info('   }');
        $this->command->info('');
        
        $this->command->info('2. MULTI-SERVICE BOOKING (Hair + Facial):');
        $this->command->info('   POST /api/v1/appointments/smart-booking');
        $this->command->info('   {');
        $this->command->info('     "customer_id": ' . User::where('role', 3)->skip(1)->first()->id . ',');
        $this->command->info('     "service_ids": [1, 6],');
        $this->command->info('     "date": "' . $testDate . '",');
        $this->command->info('     "branch_id": 1');
        $this->command->info('   }');
        $this->command->info('');
        
        $this->command->info('3. COMPLEX BOOKING (Multiple services, long duration):');
        $this->command->info('   POST /api/v1/appointments/smart-booking');
        $this->command->info('   {');
        $this->command->info('     "customer_id": ' . User::where('role', 3)->skip(2)->first()->id . ',');
        $this->command->info('     "service_ids": [3, 17],');
        $this->command->info('     "date": "' . $testDate . '",');
        $this->command->info('     "branch_id": 1');
        $this->command->info('   }');
        $this->command->info('');
        
        $this->command->info('4. CHECK AVAILABLE BEAUTICIANS:');
        $this->command->info('   GET /api/v1/appointment-services/available-beauticians?service_ids[]=1&service_ids[]=2&date=' . $testDate);
        $this->command->info('');
        
        $this->command->info('5. FIND BEST BEAUTICIAN:');
        $this->command->info('   GET /api/v1/appointment-services/find-best-beautician?service_ids[]=10&service_ids[]=11&date=' . $testDate);
        $this->command->info('');
        
        $this->command->info('6. VALIDATE BOOKING:');
        $this->command->info('   POST /api/v1/appointment-services/validate-booking');
        $this->command->info('   {');
        $this->command->info('     "service_ids": [1, 2, 5],');
        $this->command->info('     "date": "' . $testDate . '",');
        $this->command->info('     "branch_id": 1');
        $this->command->info('   }');
        $this->command->info('');
        
        $this->command->info('✅ Seeder completed! You can now test the improved booking logic.');
    }
}
