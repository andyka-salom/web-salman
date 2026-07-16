<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\EntityFunction;

class EntityFunctionSeeder extends Seeder
{
    public function run(): void
    {
        // Fungsi untuk membuat entity function sudah lengkap dengan path & level
        $createEntity = function ($name, $parent = null) {
            $id = Str::uuid();

            $level = $parent ? $parent->level + 1 : 0;
            $path  = $parent ? $parent->path . '/' . $id : $id;

            return EntityFunction::create([
                'id'          => $id,
                'name'        => $name,
                'code'        => 'EF-' . strtoupper(Str::random(5)),
                'description' => null,
                'parent_id'   => $parent->id ?? null,
                'level'       => $level,
                'path'        => $path,
                'is_active'   => true,
            ]);
        };

        // ================================
        // LEVEL 0 (ROOT)
        // ================================
        $asstManager = $createEntity('Asst. Manager Swamp Operations');

        // ================================
        // LEVEL 1
        // ================================
        $suptSiteOps = $createEntity('Supt. Site Operations Support', $asstManager);
        $suptFleetOps = $createEntity('Supt. Fleet Operations Support', $asstManager);

        // ================================
        // LEVEL 2
        // ================================
        $landSM = $createEntity('Land, SM (Bravo 2, Bravo 3)', $suptSiteOps);
        $bmSpv  = $createEntity('BM SPV (Bravo 1)', $suptFleetOps);
    }
}
