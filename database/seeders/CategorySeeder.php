<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'إلكترونيات', 'children' => [
                ['name' => 'هواتف ذكية'],
                ['name' => 'أجهزة لوحية'],
                ['name' => 'إكسسوارات'],
            ]],
            ['name' => 'ملابس', 'children' => [
                ['name' => 'رجالي'],
                ['name' => 'نسائي'],
                ['name' => 'أطفال'],
            ]],
            ['name' => 'أغذية', 'children' => [
                ['name' => 'مشروبات'],
                ['name' => 'مأكولات'],
                ['name' => 'معلبات'],
            ]],
            ['name' => 'مستلزمات مكتبية'],
            ['name' => 'أدوات كهربائية'],
            ['name' => 'مستحضرات تجميل'],
            ['name' => 'ألعاب'],
        ];

        foreach ($categories as $cat) {
            $children = $cat['children'] ?? [];
            unset($cat['children']);
            
            $parent = Category::create($cat);
            
            foreach ($children as $child) {
                Category::create([
                    'name' => $child['name'],
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
