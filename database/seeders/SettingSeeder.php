<?php

namespace Database\Seeders;

use App\Enums\SettingEnum;
use App\Models\Setting;
use Hamcrest\Core\Set;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Setting::query()->where('name', '=', SettingEnum::FEE_PARAM)->exists()) {
            echo 'Already exists';
            return;
        }

        $setting = new Setting();
        $setting->name = SettingEnum::FEE_PARAM->value;
        $setting->value = '1.02';
        $setting->save();
    }
}
