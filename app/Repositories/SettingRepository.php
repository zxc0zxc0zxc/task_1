<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Enums\SettingEnum;
use App\Exceptions\SettingIsNotSetException;
use App\Interfaces\Repositories\SettingRepositoryInterface;
use App\Models\Setting;

final readonly class SettingRepository implements SettingRepositoryInterface
{

    /** @inheritDoc */
    public function getValue(SettingEnum $settingEnum): string
    {
        $data = Setting::query()
            ->where('name', '=', $settingEnum->value)
            ->value('value');

        if (!$data) {
            throw new SettingIsNotSetException($settingEnum);
        }

        return (string)$data;
    }
}
