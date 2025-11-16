<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{

    public function run()
    {
        $currency = Currency::where('code', 'GBP')->first();
        if (!$currency) {
            throw new \Exception('GBP currency not found. Please run CurrencySeeder before SettingSeeder.');
        }
        $setting = Setting::first();
        if ($setting) {
            $setting->update([
                'timezone' => 'UTC',
                'currency_id' => $currency->id,
            ]);
        } else {
            Setting::factory()->create([
                'timezone' => 'UTC',
                'currency_id' => $currency->id,
            ]);
        }
    }
}
