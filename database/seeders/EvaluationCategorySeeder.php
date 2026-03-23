<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CriteriaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Identidad Editorial', 
                'weight' => 20, 
                'is_active' => true,
            ],
            [
                'name' => 'Transparencia del Proceso Editorial', 
                'weight' => 25, 
                'is_active' => true,
            ],
            [
                'name' => 'Ética Editorial', 
                'weight' => 20, 
                'is_active' => true,
            ],
            [
                'name' => 'Acceso y Derechos', 
                'weight' => 15, 
                'is_active' => true,
            ],
            [
                'name' => 'Infraestructura Técnica', 
                'weight' => 20, 
                'is_active' => true,
            ],
        ];

        // Try to disable FK checks via DB statement (more direct for MySQL)
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Exception $e) {
            $this->command->warn("Could not disable foreign key checks: " . $e->getMessage());
        }

        $categoryNames = collect($categories)->pluck('name')->toArray();
        
        // Manual cleanup: first things that depend on categories we want to remove
        $categoriesToKeep = Category::whereIn('name', $categoryNames)->pluck('id')->toArray();
        
        // If we are doing a full restructure, it's safer to just clear items first
        // especially if item IDs/codes are changing.
        if (request()->has('full_reset') || true) {
             // CriteriaItemSeeder will call this, and we want it to be clean.
             // We delete items associated with any category that isn't in our new list.
             CriteriaItem::whereNotIn('category_id', $categoriesToKeep)->delete();
             Category::whereNotIn('name', $categoryNames)->delete();
        }

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            // Log it but continue
        }

        $this->command->info("✅ Created/Updated " . count($categories) . " master evaluation categories.");
    }
}
