<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CurrencySeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Fetching currencies from API...');
        $this->fetchCurrenciesFromApi();
    }

    protected function fetchCurrenciesFromApi(): void {

        // Fetch currency data from a reliable API
        $response = Http::get('https://restcountries.com/v3.1/all?fields=currencies');
        $countries = $response->json();

        foreach ($countries as $countryData) {
            if (!isset($countryData['currencies'])) {
                continue;
            }
            foreach ($countryData['currencies'] as $currencyCode => $currencyInfo) {
                // Check if the currency already exists
                if (Currency::where('code', $currencyCode)->exists()) {
                    continue;
                }
                Currency::create([
                    'name' => $currencyInfo['name'] ?? $currencyCode,
                    'name_plural' => (!empty($currencyInfo['name']) ? Str::plural($currencyInfo['name']) : $currencyCode),
                    'code' => $currencyCode,
                    'symbol' => (!empty($currencyInfo['symbol']) ? $currencyInfo['symbol'] : $currencyCode),
                ]);
            }
        }
    }
}
