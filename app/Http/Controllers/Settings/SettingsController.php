<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SettingUpdateRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Repositories\SettingRepository;

use Illuminate\Http\RedirectResponse;

use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{

    public function __construct(
        private SettingRepository $settingRepository
    ) {}

    public function edit(): Response
    {
        $setting = Setting::with('currency')->first();
        return Inertia::render('settings/Setting', [
            'settings' => $setting? SettingResource::make($setting) : null,
        ]);
    }

    public function update(SettingUpdateRequest $request): RedirectResponse
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
        }

        $setting->fill(
            $this->settingRepository->getDataBuilder()
                ->setRequest($request)
                ->build()
        );
        $setting->save();

        return back()->with('success', 'Settings updated successfully');
    }

}
