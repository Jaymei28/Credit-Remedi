<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FundabilityScoreService;
use App\Services\LenderMatchingService;
use App\Models\FundabilityScore;

class FundabilityScoreController extends Controller
{
    protected $fundabilityScoreService;
    protected $lenderMatchingService;

    public function __construct(
        FundabilityScoreService $fundabilityScoreService,
        LenderMatchingService $lenderMatchingService
    ) {
        $this->fundabilityScoreService = $fundabilityScoreService;
        $this->lenderMatchingService = $lenderMatchingService;
    }

    /**
     * Display the fundability score dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Get existing fundability score or show initial state
        $fundabilityScore = FundabilityScore::where('user_id', $user->id)
            ->with('lenderMatches.lender')
            ->first();

        return view('fundability.index', [
            'fundabilityScore' => $fundabilityScore,
            'hasScore' => $fundabilityScore !== null,
        ]);
    }

    /**
     * Calculate or recalculate the fundability score.
     */
    public function calculate(Request $request)
    {
        $user = auth()->user();

        try {
            // Calculate fundability score
            $fundabilityScore = $this->fundabilityScoreService->calculateScore($user);

            // Find matching lenders
            $matches = $this->lenderMatchingService->findMatches($user, $fundabilityScore);

            return redirect()->route('fundability.results')
                ->with('success', '✅ Your fundability score has been calculated! Found ' . count($matches) . ' matching lenders.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '❌ Error calculating fundability score: ' . $e->getMessage());
        }
    }

    /**
     * Display the fundability score results.
     */
    public function results()
    {
        $user = auth()->user();

        $fundabilityScore = FundabilityScore::where('user_id', $user->id)
            ->with('lenderMatches.lender')
            ->firstOrFail();

        // Get matches ordered by match score
        $matches = $fundabilityScore->lenderMatches()
            ->with('lender')
            ->orderBy('match_score', 'desc')
            ->get();

        return view('fundability.results', [
            'fundabilityScore' => $fundabilityScore,
            'matches' => $matches,
            'topMatches' => $matches->take(3),
        ]);
    }
}
