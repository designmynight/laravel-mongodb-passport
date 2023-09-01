<?php

namespace Sysvale\Mongodb\Console;

use Illuminate\Support\Carbon;
use Laravel\Passport\Console\PurgeCommand as PassportPurgeCommand;
use Laravel\Passport\Passport;

class PurgeCommand extends PassportPurgeCommand
{
    public function handle()
    {
        $expired = Carbon::now()->subDays(7);

        if (($this->option('revoked') && $this->option('expired')) ||
            (!$this->option('revoked') && !$this->option('expired'))
        ) {
            Passport::token()->where('revoked', true)->orWhere('expires_at', '<', $expired)->delete();
            Passport::authCode()->where('revoked', true)->orWhere('expires_at', '<', $expired)->delete();
            Passport::refreshToken()->where('revoked', true)->orWhere('expires_at', '<', $expired)->delete();

            $this->info('Purged revoked items and items expired for more than seven days.');
        } elseif ($this->option('revoked')) {
            Passport::token()->where('revoked', true)->delete();
            Passport::authCode()->where('revoked', true)->delete();
            Passport::refreshToken()->where('revoked', true)->delete();

            $this->info('Purged revoked items.');
        } elseif ($this->option('expired')) {
            Passport::token()->where('expires_at', '<', $expired)->delete();
            Passport::authCode()->where('expires_at', '<', $expired)->delete();
            Passport::refreshToken()->where('expires_at', '<', $expired)->delete();

            $this->info('Purged items expired for more than seven days.');
        }
    }
}
