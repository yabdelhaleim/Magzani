<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardService;
use Livewire\Component;

/**
 * Dashboard — Atelier design system dashboard with 15 widgets.
 *
 * Single Livewire component powers the entire dashboard so refreshes are
 * seamless and the data layer is centralised in DashboardService.
 */
class Dashboard extends Component
{
    public string $range = 'month';

    /** @var array<string, mixed>|null */
    public ?array $payload = null;

    public function mount(DashboardService $service): void
    {
        $this->refresh($service);
    }

    /**
     * Refresh the dashboard payload.
     */
    public function refresh(DashboardService $service): void
    {
        $this->payload = $service->payload($this->range);
    }

    /**
     * Switch the time range and recompute.
     */
    public function setRange(string $range, DashboardService $service): void
    {
        if (! in_array($range, array_keys(DashboardService::RANGES), true)) {
            return;
        }
        $this->range = $range;
        $this->refresh($service);
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard', [
            'payload' => $this->payload,
            'ranges'  => DashboardService::RANGES,
        ]);
    }
}
