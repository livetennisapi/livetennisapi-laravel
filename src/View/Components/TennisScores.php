<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LiveTennisApi\Exception\LiveTennisApiError;
use LiveTennisApi\Exception\UpgradeRequired;
use LiveTennisApi\LiveTennisApi;
use LiveTennisApi\Model\TennisMatch;

/**
 * Server-side rendered live scores: `<x-tennis-scores />`.
 *
 *     <x-tennis-scores />                          {{-- config defaults --}}
 *     <x-tennis-scores status="upcoming" :limit="5" tour="atp" />
 *
 * The matches are fetched at render time. Any API or transport failure is
 * caught and surfaced as an empty/error state rather than fataling the page —
 * a live-scores widget must never take a page down.
 *
 * @var array<int, TennisMatch> $matches
 */
final class TennisScores extends Component
{
    /** @var array<int, TennisMatch> */
    public array $matches = [];

    /** Non-null when the fetch failed; the view renders a graceful fallback. */
    public ?string $error = null;

    /**
     * True when the API answered 403 upgrade_required — e.g. `status="completed"`
     * on a FREE key, which needs the BASIC tier or any History plan. The view
     * renders a visible upgrade notice instead of the generic error fallback.
     */
    public bool $upgradeRequired = false;

    public function __construct(
        private readonly LiveTennisApi $client,
        public string $status = 'live',
        public ?int $limit = null,
        public ?string $tour = null,
    ) {
        $this->limit ??= (int) config('livetennis.scores.limit', 10);
        if ($this->status === '') {
            $this->status = (string) config('livetennis.scores.status', 'live');
        }

        $this->load();
    }

    private function load(): void
    {
        try {
            $this->matches = $this->client->listMatches($this->status, $this->tour, $this->limit)->data;
        } catch (UpgradeRequired $e) {
            $this->upgradeRequired = true;
            $this->error = $e->getMessage();
            $this->matches = [];
        } catch (LiveTennisApiError $e) {
            $this->error = $e->getMessage();
            $this->matches = [];
        }
    }

    public function render(): View
    {
        return view('livetennis::components.tennis-scores');
    }
}
