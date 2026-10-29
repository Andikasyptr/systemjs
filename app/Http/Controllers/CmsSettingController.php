<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CmsSettingController extends Controller
{
    public function edit()
    {
        return view('cms.settings', [
            'groups' => config('site.groups'),
            'values' => Setting::forSite(),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        $toggles = [];

        foreach (config('site.groups') as $fields) {
            foreach ($fields as $key => [$label, $type, $default]) {
                if ($type === 'toggle') { $toggles[] = $key; continue; }
                $rules[$key] = match ($type) {
                    'textarea' => 'nullable|string|max:1000',
                    'number'   => 'nullable|integer|min:0',
                    default    => 'nullable|string|max:255',
                };
            }
        }

        $data = $request->validate($rules);

        foreach ($toggles as $key) {
            $data[$key] = $request->boolean($key) ? '1' : '0';
        }

        // Rapikan nomor WA: 0812... -> 62812...
        if (array_key_exists('wa_number', $data)) {
            $n = preg_replace('/\D/', '', (string) $data['wa_number']);
            if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
            $data['wa_number'] = $n ?: null;
        }

        Setting::put($data);

        return back()->with('success', 'Tampilan website diperbarui.');
    }
}