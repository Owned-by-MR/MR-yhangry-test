<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SetMenu;
use App\Models\Cuisine;
use Illuminate\Support\Str;

class ImportSetMenusJson extends Command
{
    protected $signature = 'import:set-menus';
    protected $description = 'Import set menus from local JSON file';

    public function handle()
    {
        $this->info('Starting import...');

        try {
            $jsonPath = public_path('data/set-menus.json');
            
            if (!file_exists($jsonPath)) {
                throw new \Exception("JSON file not found at: {$jsonPath}");
            }

            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON: ' . json_last_error_msg());
            }

            // Check if data exists and is array
            if (!isset($data['data']) || !is_array($data['data'])) {
                $data['data'] = is_array($data) ? $data : [$data];
            }

            $this->withProgressBar($data['data'], function ($menuData) {
                try {
                    // Create or update cuisine first
                    $cuisines = [];
                    if (isset($menuData['cuisines']) && is_array($menuData['cuisines'])) {
                        foreach ($menuData['cuisines'] as $cuisineData) {
                            $cuisine = Cuisine::firstOrCreate(
                                ['id' => $cuisineData['id'] ?? null],
                                [
                                    'name' => $cuisineData['name'] ?? 'Unknown Cuisine',
                                    'slug' => Str::slug($cuisineData['name'] ?? 'unknown-cuisine')
                                ]
                            );
                            $cuisines[] = $cuisine->id;
                        }
                    }

                    // Prepare set menu data with default values
                    $setMenuData = [
                        'name' => $menuData['name'] ?? 'Unnamed Menu',
                        'description' => $menuData['description'] ?? 'No description available',
                        'image' => $menuData['image'] ?? null,
                        'thumbnail' => $menuData['thumbnail'] ?? null,
                        'price_per_person' => $menuData['price_per_person'] ?? 0,
                        'min_spend' => $menuData['min_spend'] ?? 0,
                        'status' => $menuData['status'] ?? true,
                        'is_vegan' => $menuData['is_vegan'] ?? false,
                        'is_vegetarian' => $menuData['is_vegetarian'] ?? false,
                        'is_halal' => $menuData['is_halal'] ?? false,
                        'is_kosher' => $menuData['is_kosher'] ?? false,
                        'is_seated' => $menuData['is_seated'] ?? false,
                        'is_standing' => $menuData['is_standing'] ?? false,
                        'is_canape' => $menuData['is_canape'] ?? false,
                        'is_mixed_dietary' => $menuData['is_mixed_dietary'] ?? false,
                        'number_of_orders' => $menuData['number_of_orders'] ?? 0,
                        'display_text' => $menuData['display_text'] ?? false,
                    ];

                    // If created_at exists in JSON, add it to the data
                    if (isset($menuData['created_at'])) {
                        $setMenuData['created_at'] = $menuData['created_at'];
                    }

                    // Create or update set menu
                    $setMenu = SetMenu::updateOrCreate(
                        ['name' => $setMenuData['name']], // Use name as unique identifier
                        $setMenuData
                    );

                    // Attach cuisines to set menu if any exist
                    if (!empty($cuisines)) {
                        $setMenu->cuisines()->sync($cuisines);
                    }

                } catch (\Exception $e) {
                    $this->error(''. $e->getMessage());
                }
            });

            $this->newLine();
            $this->info('Import completed successfully!');

        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
