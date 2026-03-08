<?php

namespace App\Console\Commands;

use App\Models\countries;
use App\Models\regions;
use App\Models\districts;
use App\Models\wards;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportTanzaniaLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:import-tanzania {--fresh : Delete existing Tanzania locations before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Tanzania regions, districts, and wards from JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Tanzania locations import...');

        // Path to JSON file
        $jsonPath = public_path('tanzania_regions_districts_wards.json');

        if (!File::exists($jsonPath)) {
            $this->error("JSON file not found at: {$jsonPath}");
            return 1;
        }

        // Read and decode JSON
        $jsonContent = File::get($jsonPath);
        $data = json_decode($jsonContent, true);

        if (!$data || !isset($data['data'])) {
            $this->error('Invalid JSON format');
            return 1;
        }

        DB::beginTransaction();

        try {
            // Get or create Tanzania country
            $country = countries::firstOrCreate(
                ['name' => 'Tanzania'],
                [
                    'code' => 'TZA',
                    'shortcode' => '255'
                ]
            );

            $this->info("Country: {$country->name} (ID: {$country->id})");

            // Option to delete existing Tanzania locations
            if ($this->option('fresh')) {
                $this->warn('Deleting existing Tanzania locations...');

                regions::where('country_id', $country->id)->each(function ($region) {
                    // Delete wards first
                    $region->districts()->each(function ($district) {
                        $district->wards()->delete();
                    });
                    // Delete districts
                    $region->districts()->delete();
                });
                // Delete regions
                regions::where('country_id', $country->id)->delete();

                $this->info('Existing locations deleted.');
            }

            $stats = [
                'regions' => 0,
                'districts' => 0,
                'wards' => 0,
            ];

            // Progress bar
            $progressBar = $this->output->createProgressBar(count($data['data']));
            $progressBar->start();

            foreach ($data['data'] as $regionData) {
                // Create or update region
                $region = regions::firstOrCreate(
                    [
                        'name' => $regionData['region'],
                        'country_id' => $country->id
                    ]
                );
                $stats['regions']++;

                if (isset($regionData['districts']) && is_array($regionData['districts'])) {
                    foreach ($regionData['districts'] as $districtData) {
                        // Create or update district
                        $district = districts::firstOrCreate(
                            [
                                'name' => $districtData['district'],
                                'region_id' => $region->id
                            ]
                        );
                        $stats['districts']++;

                        if (isset($districtData['wards']) && is_array($districtData['wards'])) {
                            foreach ($districtData['wards'] as $wardName) {
                                // Create or update ward
                                wards::firstOrCreate(
                                    [
                                        'name' => ucwords(strtolower($wardName)),
                                        'district_id' => $district->id
                                    ]
                                );
                                $stats['wards']++;
                            }
                        }
                    }
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            DB::commit();

            // Display statistics
            $this->info('Import completed successfully!');
            $this->table(
                ['Type', 'Count'],
                [
                    ['Regions', $stats['regions']],
                    ['Districts', $stats['districts']],
                    ['Wards', $stats['wards']],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
