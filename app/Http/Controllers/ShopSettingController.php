<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ShopSettingController extends Controller
{
    public function edit(): View
    {
        $shopSettings = ShopSetting::query()->findOrFail(1);
        request()->attributes->set('shopSettings', $shopSettings);

        return view('settings.shop', [
            'shopSettings' => $shopSettings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'currency_code' => strtoupper((string) $request->input('currency_code')),
        ]);

        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'currency_code' => ['required', 'regex:/^[A-Z]{3}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);

        $shopSettings = ShopSetting::query()->findOrFail(1);
        $previousLogo = $shopSettings->logo_path;
        $newLogo = null;

        if ($request->hasFile('logo')) {
            $newLogo = Storage::disk('public')->putFile('shop/logos', $validated['logo']);

            if ($newLogo === false) {
                throw new RuntimeException('The shop logo could not be saved to public storage.');
            }
        }

        $logoPath = $newLogo
            ?? (! empty($validated['remove_logo']) ? null : $previousLogo);

        try {
            $shopSettings->fill([
                'shop_name' => $validated['shop_name'],
                'tagline' => $validated['tagline'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'website' => $validated['website'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'currency_code' => $validated['currency_code'],
                'logo_path' => $logoPath,
            ])->save();
        } catch (Throwable $exception) {
            if ($newLogo !== null && ! Storage::disk('public')->delete($newLogo)) {
                Log::warning('Unable to remove a newly uploaded shop logo after settings failed to save.', [
                    'path' => $newLogo,
                ]);
            }

            throw $exception;
        }

        if ($previousLogo && $previousLogo !== $logoPath
            && ! Storage::disk('public')->delete($previousLogo)) {
            Log::warning('Unable to remove the previous shop logo after it was replaced.', [
                'path' => $previousLogo,
            ]);
        }

        $request->attributes->set('shopSettings', $shopSettings->fresh());

        return redirect()->route('shop-settings.edit')
            ->with('success', 'Shop information updated successfully.');
    }
}
