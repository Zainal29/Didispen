<?php
namespace Database\Seeders;

use App\Models\GuruPiket;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GuruPiketSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);
            GuruPiket::create(['guru_id' => 1, 'tanggal' => $date, 'shift' => 'pagi']);
            GuruPiket::create(['guru_id' => 2, 'tanggal' => $date, 'shift' => 'siang']);
        }
    }
}
