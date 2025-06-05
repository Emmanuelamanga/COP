<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CaseModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create verifier users
        $verifier1 = User::create([
            'name' => 'Jane Verifier',
            'email' => 'verifier@example.com',
            'password' => Hash::make('password'),
            'role' => 'verifier',
            'is_active' => true,
        ]);

        $verifier2 = User::create([
            'name' => 'John Verification',
            'email' => 'verifier2@example.com',
            'password' => Hash::make('password'),
            'role' => 'verifier',
            'is_active' => true,
        ]);

        // Create approver users
        $approver1 = User::create([
            'name' => 'Mary Approver',
            'email' => 'approver@example.com',
            'password' => Hash::make('password'),
            'role' => 'approver',
            'is_active' => true,
        ]);

        $approver2 = User::create([
            'name' => 'Peter Approval',
            'email' => 'approver2@example.com',
            'password' => Hash::make('password'),
            'role' => 'approver',
            'is_active' => true,
        ]);

        // Create regular users who will report cases
        $regularUsers = User::factory(15)->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        // Add admin to regular users for case creation
        $allReporters = $regularUsers->push($admin);

        // Create cases with different statuses and realistic distribution

        // Create pending cases (30% of total)
        foreach ($allReporters->random(8) as $user) {
            CaseModel::factory(rand(1, 3))
                ->pending()
                ->for($user)
                ->create();
        }

        // Create verified cases (25% of total)
        foreach ($allReporters->random(6) as $user) {
            CaseModel::factory(rand(1, 2))
                ->verified()
                ->for($user)
                ->state([
                    'verified_by' => collect([$verifier1->id, $verifier2->id, $admin->id])->random(),
                ])
                ->create();
        }

        // Create approved cases (40% of total) - these will show in public visualization
        foreach ($allReporters->random(12) as $user) {
            CaseModel::factory(rand(1, 4))
                ->approved()
                ->for($user)
                ->state([
                    'verified_by' => collect([$verifier1->id, $verifier2->id, $admin->id])->random(),
                    'approved_by' => collect([$approver1->id, $approver2->id, $admin->id])->random(),
                ])
                ->create();
        }

        // Create rejected cases (5% of total)
        foreach ($allReporters->random(3) as $user) {
            CaseModel::factory(rand(1, 2))
                ->rejected()
                ->for($user)
                ->state([
                    'verified_by' => collect([$verifier1->id, $verifier2->id, $admin->id])->random(),
                ])
                ->create();
        }

        // Create specific case type distributions for better visualization
        $this->createSpecificCaseDistributions($allReporters, $verifier1, $approver1);

        // Create recent cases for timeline visualization
        $this->createRecentCases($allReporters, $verifier1, $approver1);

        // Create county-specific cases for geographic distribution
        $this->createCountySpecificCases($allReporters, $verifier1, $approver1);
    }

    /**
     * Create specific distributions of case types for better visualization
     */
    private function createSpecificCaseDistributions($users, $verifier, $approver): void
    {
        // Create more GBV cases (higher priority)
        CaseModel::factory(15)
            ->gbv()
            ->approved()
            ->for($users->random())
            ->state([
                'verified_by' => $verifier->id,
                'approved_by' => $approver->id,
            ])
            ->create();

        // Create SRH cases
        CaseModel::factory(12)
            ->srh()
            ->approved()
            ->for($users->random())
            ->state([
                'verified_by' => $verifier->id,
                'approved_by' => $approver->id,
            ])
            ->create();

        // Create Economic Empowerment cases
        CaseModel::factory(10)
            ->economicEmpowerment()
            ->approved()
            ->for($users->random())
            ->state([
                'verified_by' => $verifier->id,
                'approved_by' => $approver->id,
            ])
            ->create();
    }

    /**
     * Create recent cases for timeline visualization
     */
    private function createRecentCases($users, $verifier, $approver): void
    {
        // Create cases from the last 6 months for trend analysis
        for ($i = 0; $i < 6; $i++) {
            $startDate = now()->subMonths($i + 1)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            // Simulate increasing trend in recent months
            $caseCount = $i < 3 ? rand(8, 15) : rand(3, 8);

            CaseModel::factory($caseCount)
                ->approved()
                ->for($users->random())
                ->state([
                    'incident_date' => fake()->dateTimeBetween($startDate, $endDate),
                    'verified_by' => $verifier->id,
                    'approved_by' => $approver->id,
                ])
                ->create();
        }
    }

    /**
     * Create county-specific cases for geographic distribution
     */
    private function createCountySpecificCases($users, $verifier, $approver): void
    {
        $majorCounties = [
            'Nairobi' => 25,
            'Mombasa' => 15,
            'Kisumu' => 12,
            'Nakuru' => 10,
            'Eldoret' => 8,
        ];

        foreach ($majorCounties as $county => $count) {
            CaseModel::factory($count)
                ->approved()
                ->forCounty($county)
                ->for($users->random())
                ->state([
                    'verified_by' => $verifier->id,
                    'approved_by' => $approver->id,
                ])
                ->create();
        }

        // Create cases for smaller counties
        $smallerCounties = [
            'Thika', 'Malindi', 'Kitale', 'Garissa', 'Isiolo', 
            'Machakos', 'Meru', 'Nyeri', 'Kiambu', 'Murang\'a'
        ];

        foreach ($smallerCounties as $county) {
            CaseModel::factory(rand(2, 6))
                ->approved()
                ->forCounty($county)
                ->for($users->random())
                ->state([
                    'verified_by' => $verifier->id,
                    'approved_by' => $approver->id,
                ])
                ->create();
        }
    }
}