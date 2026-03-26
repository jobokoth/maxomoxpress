<?php

namespace App\Filament\PlatformAdmin\Widgets;

use App\Models\PlatformSubscription;
use App\Models\School;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSchools = School::count();
        $activeSchools = School::where('is_active', true)->count();
        $trialSchools = School::where('is_trial', true)->where('trial_ends_at', '>', now())->count();
        $expiredTrials = School::where('is_trial', true)->where('trial_ends_at', '<', now())->count();

        $activeSubs = PlatformSubscription::where('status', 'active')->count();
        $mrrKes = PlatformSubscription::where('status', 'active')->sum('amount_kes');

        return [
            Stat::make('Total Schools', $totalSchools)
                ->description("{$activeSchools} active")
                ->icon('heroicon-o-building-office-2')
                ->color('primary'),

            Stat::make('On Trial', $trialSchools)
                ->description("{$expiredTrials} expired trials")
                ->icon('heroicon-o-clock')
                ->color($expiredTrials > 0 ? 'warning' : 'success'),

            Stat::make('Paid Subscriptions', $activeSubs)
                ->icon('heroicon-o-credit-card')
                ->color('success'),

            Stat::make('Monthly Recurring Revenue', 'KES '.number_format($mrrKes))
                ->description('Active subscriptions only')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
