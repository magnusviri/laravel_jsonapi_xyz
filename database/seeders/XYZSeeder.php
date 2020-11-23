<?php

namespace Database\Seeders;
use App\Models\X;

use Illuminate\Database\Seeder;

class XYZSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $x1 = X::create(['name' => 'x1']);

        $x1->ys()->createMany([
            ['name' => 'x1y1'],
            ['name' => 'x1y2'],
        ]);

        $x1->zs()->createMany([
            ['name' => 'x1z1'],
            ['name' => 'x1z2'],
        ]);

        $x2 = X::create(['name' => 'x2']);

        $x2->ys()->createMany([
            ['name' => 'x2y1'],
            ['name' => 'x2y2'],
        ]);

        $x2->zs()->createMany([
            ['name' => 'x2z1'],
            ['name' => 'x2z2'],
        ]);
    }
}
