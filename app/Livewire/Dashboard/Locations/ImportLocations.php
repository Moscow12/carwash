<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\countries;
use App\Models\regions;
use App\Models\districts;
use App\Models\wards;
use App\Models\street;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLocations extends Component
{
    public $showModal = false;
    public $importing = false;
    public $progress = 0;
    public $includeStreets = true;
    public $stats = [
        'regions' => 0,
        'districts' => 0,
        'wards' => 0,
        'streets' => 0,
    ];

    public function openModal()
    {
        $this->showModal = true;
        $this->resetStats();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->importing = false;
        $this->resetStats();
    }

    public function resetStats()
    {
        $this->progress = 0;
        $this->stats = [
            'regions' => 0,
            'districts' => 0,
            'wards' => 0,
            'streets' => 0,
        ];
    }

    public function importTanzaniaLocations()
    {
        $this->importing = true;
        $this->resetStats();

        try {
            // Choose JSON file based on whether to include streets
            $jsonPath = $this->includeStreets
                ? public_path('tanzania_full_geodata.json')
                : public_path('tanzania_regions_districts_wards.json');

            if (!File::exists($jsonPath)) {
                session()->flash('error', 'Tanzania locations JSON file not found.');
                $this->importing = false;
                return;
            }

            // Read and decode JSON
            $jsonContent = File::get($jsonPath);
            $data = json_decode($jsonContent, true);

            if (!$data || !isset($data['data'])) {
                session()->flash('error', 'Invalid JSON file format.');
                $this->importing = false;
                return;
            }

            DB::beginTransaction();

            // Get or create Tanzania country
            $country = countries::firstOrCreate(
                ['name' => 'Tanzania'],
                [
                    'code' => 'TZA',
                    'shortcode' => '255'
                ]
            );

            $totalRegions = count($data['data']);
            $processedRegions = 0;

            foreach ($data['data'] as $regionData) {
                // Create or update region
                $region = regions::firstOrCreate(
                    [
                        'name' => ucwords(strtolower($regionData['region'])),
                        'country_id' => $country->id
                    ]
                );
                $this->stats['regions']++;

                if (isset($regionData['districts']) && is_array($regionData['districts'])) {
                    foreach ($regionData['districts'] as $districtData) {
                        // Create or update district
                        $district = districts::firstOrCreate(
                            [
                                'name' => ucwords(strtolower($districtData['district'])),
                                'region_id' => $region->id
                            ]
                        );
                        $this->stats['districts']++;

                        // Handle wards (could be array of strings or array of objects)
                        $wardsData = $districtData['wards'] ?? [];

                        if (is_array($wardsData)) {
                            foreach ($wardsData as $wardItem) {
                                // If includeStreets, wardItem is an object; otherwise it's a string
                                if ($this->includeStreets && is_array($wardItem)) {
                                    $wardName = $wardItem['ward'] ?? null;
                                    $streets = $wardItem['streets'] ?? [];
                                } else {
                                    $wardName = $wardItem;
                                    $streets = [];
                                }

                                if ($wardName) {
                                    // Create or update ward
                                    $ward = wards::firstOrCreate(
                                        [
                                            'name' => ucwords(strtolower($wardName)),
                                            'district_id' => $district->id
                                        ]
                                    );
                                    $this->stats['wards']++;

                                    // Import streets if enabled
                                    if ($this->includeStreets && is_array($streets)) {
                                        foreach ($streets as $streetName) {
                                            if (!empty($streetName)) {
                                                street::firstOrCreate(
                                                    [
                                                        'name' => ucwords(strtolower($streetName)),
                                                        'ward_id' => $ward->id
                                                    ]
                                                );
                                                $this->stats['streets']++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $processedRegions++;
                $this->progress = round(($processedRegions / $totalRegions) * 100);
            }

            DB::commit();

            $message = "Successfully imported {$this->stats['regions']} regions, {$this->stats['districts']} districts, {$this->stats['wards']} wards";
            if ($this->includeStreets) {
                $message .= ", and {$this->stats['streets']} streets";
            }
            $message .= "!";

            session()->flash('success', $message);
            $this->importing = false;
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Import failed: ' . $e->getMessage());
            $this->importing = false;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.locations.import-locations');
    }
}
