<?php

namespace Database\Factories;

use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Case>
 */
class CaseModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CaseModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $counties = [
            'Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 
            'Malindi', 'Kitale', 'Garissa', 'Isiolo', 'Machakos', 'Meru',
            'Nyeri', 'Kiambu', 'Murang\'a', 'Kirinyaga', 'Nyandarua',
            'Laikipia', 'Samburu', 'Trans Nzoia', 'Uasin Gishu', 'Elgeyo Marakwet',
            'Nandi', 'Baringo', 'Pokot', 'Turkana', 'Kajiado', 'Makueni',
            'Kitui', 'Embu', 'Tharaka Nithi', 'Marsabit', 'Mandera', 'Wajir',
            'Kilifi', 'Taita Taveta', 'Kwale', 'Tana River', 'Lamu'
        ];

        $caseTypes = ['GBV', 'SRH', 'Economic Empowerment'];
        $genders = ['Male', 'Female', 'Others'];
        
        // Generate realistic descriptions based on case type
        $caseType = $this->faker->randomElement($caseTypes);
        $description = $this->generateDescription($caseType);
        
        // Generate incident date within the last 2 years
        $incidentDate = $this->faker->dateTimeBetween('-2 years', 'now');
        
        return [
            'user_id' => User::factory(),
            'county' => $this->faker->randomElement($counties),
            'case_type' => $caseType,
            'gender' => $this->faker->randomElement($genders),
            'description' => $description,
            'incident_date' => $incidentDate,
            'contact_information' => $this->generateContactInfo(),
            'status' => $this->faker->randomElement(['pending', 'verified', 'approved', 'rejected']),
            'verified_by' => null,
            'approved_by' => null,
            'verified_at' => null,
            'approved_at' => null,
            'verification_notes' => null,
            'approval_notes' => null,
        ];
    }

    /**
     * Generate realistic description based on case type
     */
    private function generateDescription(string $caseType): string
    {
        switch ($caseType) {
            case 'GBV':
                $scenarios = [
                    'Domestic violence incident reported involving physical assault by intimate partner. Victim seeking support and protection services.',
                    'Sexual harassment case at workplace. Multiple incidents reported over several months affecting work performance.',
                    'Intimate partner violence case involving emotional and financial abuse. Children also affected by the situation.',
                    'Community-based gender violence incident during public gathering. Witnesses available to provide statements.',
                    'Marital rape case reported. Victim seeking legal advice and counseling services for trauma recovery.',
                    'Forced marriage situation involving minor. Family pressure and threats documented by community members.',
                    'Sexual assault case reported at educational institution. Need for immediate medical and psychological support.',
                    'Online gender-based violence including cyber stalking and harassment through social media platforms.',
                ];
                break;
                
            case 'SRH':
                $scenarios = [
                    'Lack of access to family planning services in rural area. Nearest health facility over 50km away.',
                    'Teenage pregnancy case requiring comprehensive reproductive health education and support services.',
                    'Maternal health complications during delivery. Poor quality services at local health facility reported.',
                    'Denial of contraceptive services based on marital status. Healthcare provider refusing service to unmarried women.',
                    'HIV/AIDS stigma affecting access to treatment and care. Community education needed to address discrimination.',
                    'Unsafe abortion complications requiring emergency medical intervention and follow-up care services.',
                    'Infertility issues affecting couple. Limited access to specialized reproductive health services in the region.',
                    'Menstrual hygiene challenges in schools. Lack of proper facilities and sanitary products affecting attendance.',
                ];
                break;
                
            case 'Economic Empowerment':
                $scenarios = [
                    'Gender-based employment discrimination. Qualified female candidate rejected due to pregnancy status.',
                    'Unequal pay for equal work documented. Male colleagues receiving higher compensation for same position.',
                    'Lack of access to business loans and credit facilities for women entrepreneurs in rural areas.',
                    'Property inheritance disputes. Widow being denied access to deceased husband\'s assets by in-laws.',
                    'Limited participation in community economic development projects. Women excluded from decision-making processes.',
                    'Gender barriers in accessing agricultural extension services and modern farming techniques.',
                    'Workplace harassment affecting career advancement opportunities. Need for supportive work environment policies.',
                    'Limited access to financial literacy training and business development support for women groups.',
                ];
                break;
                
            default:
                $scenarios = ['General case requiring attention and proper investigation by relevant authorities.'];
        }
        
        return $this->faker->randomElement($scenarios);
    }

    /**
     * Generate realistic contact information
     */
    private function generateContactInfo(): string
    {
        $contactTypes = [
            'phone' => $this->faker->randomElement([
                '+254' . $this->faker->numerify('7########'),
                '+254' . $this->faker->numerify('1########'),
                '07' . $this->faker->numerify('########'),
                '01' . $this->faker->numerify('########'),
            ]),
            'email' => $this->faker->safeEmail(),
            'both' => null
        ];
        
        $type = $this->faker->randomElement(array_keys($contactTypes));
        
        if ($type === 'both') {
            return $this->faker->phoneNumber() . ' | ' . $this->faker->safeEmail();
        }
        
        return $contactTypes[$type];
    }

    /**
     * Create a pending case
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'verified_by' => null,
            'approved_by' => null,
            'verified_at' => null,
            'approved_at' => null,
            'verification_notes' => null,
            'approval_notes' => null,
        ]);
    }

    /**
     * Create a verified case
     */
    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            $verifier = User::where('role', 'verifier')->first() 
                     ?? User::where('role', 'admin')->first();
            
            return [
                'status' => 'verified',
                'verified_by' => $verifier?->id,
                'verified_at' => $this->faker->dateTimeBetween($attributes['incident_date'] ?? '-1 year', 'now'),
                'verification_notes' => $this->faker->randomElement([
                    'Case verified after thorough investigation. All documentation complete.',
                    'Verified case with supporting evidence. Ready for approval process.',
                    'Investigation completed. Case meets verification criteria.',
                    'All required documentation reviewed and verified.',
                ]),
                'approved_by' => null,
                'approved_at' => null,
                'approval_notes' => null,
            ];
        });
    }

    /**
     * Create an approved case
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $verifier = User::where('role', 'verifier')->first() 
                     ?? User::where('role', 'admin')->first();
            $approver = User::where('role', 'approver')->first() 
                     ?? User::where('role', 'admin')->first();
            
            $verifiedAt = $this->faker->dateTimeBetween($attributes['incident_date'] ?? '-1 year', 'now');
            $approvedAt = $this->faker->dateTimeBetween($verifiedAt, 'now');
            
            return [
                'status' => 'approved',
                'verified_by' => $verifier?->id,
                'verified_at' => $verifiedAt,
                'verification_notes' => $this->faker->randomElement([
                    'Case verified after thorough investigation. All documentation complete.',
                    'Verified case with supporting evidence. Ready for approval process.',
                    'Investigation completed. Case meets verification criteria.',
                ]),
                'approved_by' => $approver?->id,
                'approved_at' => $approvedAt,
                'approval_notes' => $this->faker->randomElement([
                    'Case approved for public display. All criteria met.',
                    'Approved after review. Case suitable for data visualization.',
                    'Final approval granted. Case ready for publication.',
                    'Approved for inclusion in public statistics and reports.',
                ]),
            ];
        });
    }

    /**
     * Create a rejected case
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            $rejector = User::whereIn('role', ['verifier', 'approver', 'admin'])->first();
            
            return [
                'status' => 'rejected',
                'verified_by' => $rejector?->id,
                'verified_at' => $this->faker->dateTimeBetween($attributes['incident_date'] ?? '-1 year', 'now'),
                'verification_notes' => $this->faker->randomElement([
                    'Case rejected due to insufficient documentation.',
                    'Unable to verify case details. Additional information required.',
                    'Case does not meet verification criteria.',
                    'Rejected due to incomplete or inconsistent information.',
                ]),
                'approved_by' => null,
                'approved_at' => null,
                'approval_notes' => null,
            ];
        });
    }

    /**
     * Create a GBV case
     */
    public function gbv(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_type' => 'GBV',
            'description' => $this->generateDescription('GBV'),
        ]);
    }

    /**
     * Create an SRH case
     */
    public function srh(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_type' => 'SRH',
            'description' => $this->generateDescription('SRH'),
        ]);
    }

    /**
     * Create an Economic Empowerment case
     */
    public function economicEmpowerment(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_type' => 'Economic Empowerment',
            'description' => $this->generateDescription('Economic Empowerment'),
        ]);
    }

    /**
     * Create a recent case (within last 30 days)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'incident_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Create cases for a specific county
     */
    public function forCounty(string $county): static
    {
        return $this->state(fn (array $attributes) => [
            'county' => $county,
        ]);
    }

    /**
     * Create cases for a specific gender
     */
    public function forGender(string $gender): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => $gender,
        ]);
    }
}