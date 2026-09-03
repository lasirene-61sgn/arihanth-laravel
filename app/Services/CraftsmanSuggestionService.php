<?php

namespace App\Services;

use App\Models\Craftman;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;

class CraftsmanSuggestionService
{
    /**
     * Get craftsman suggestions based on historical completions for the given work orders.
     * 
     * @param \Illuminate\Support\Collection $workOrders
     * @return array
     */
    public function getSuggestionsForWorkOrders($workOrders)
    {
        if ($workOrders->isEmpty()) {
            return [];
        }

        $craftsmen = Craftman::all();

        // Collect all criteria from the selected work orders
        $selectedCriteria = [
            'product_code' => [],
            'design_code' => [],
            'product_category_id' => [],
        ];

        foreach ($workOrders as $wo) {
            if ($wo->product_code) $selectedCriteria['product_code'][] = $wo->product_code;
            if ($wo->design_code) $selectedCriteria['design_code'][] = $wo->design_code;
            if ($wo->product_category_id) $selectedCriteria['product_category_id'][] = $wo->product_category_id;
        }

        // Make unique and remove empties
        $selectedCriteria['product_code'] = array_values(array_unique(array_filter($selectedCriteria['product_code'])));
        $selectedCriteria['design_code'] = array_values(array_unique(array_filter($selectedCriteria['design_code'])));
        $selectedCriteria['product_category_id'] = array_values(array_unique(array_filter($selectedCriteria['product_category_id'])));

        // If no criteria available to match against, return empty
        if (empty($selectedCriteria['product_code']) && empty($selectedCriteria['design_code']) && empty($selectedCriteria['product_category_id'])) {
            return [];
        }

        // Fetch all completed work orders matching ANY of the criteria
        $completedWorkOrdersQuery = WorkOrder::select('allocated_craftsman_bp_code', 'product_code', 'design_code', 'product_category_id')
            ->whereNotNull('allocated_craftsman_bp_code')
            ->where(function ($q) {
                $q->where('craftsman_status', 'completed')->orWhere('status', 'completed');
            });

        $completedWorkOrdersQuery->where(function ($q) use ($selectedCriteria) {
            if (!empty($selectedCriteria['product_code'])) $q->orWhereIn('product_code', $selectedCriteria['product_code']);
            if (!empty($selectedCriteria['design_code'])) $q->orWhereIn('design_code', $selectedCriteria['design_code']);
            if (!empty($selectedCriteria['product_category_id'])) $q->orWhereIn('product_category_id', $selectedCriteria['product_category_id']);
        });

        $completedWorkOrders = $completedWorkOrdersQuery->get();

        $craftsmanStats = [];

        foreach ($craftsmen as $craftsman) {
            $bpCode = $craftsman->craftman_code;
            $craftsmanOrders = $completedWorkOrders->where('allocated_craftsman_bp_code', $bpCode);

            if ($craftsmanOrders->isEmpty()) {
                continue;
            }

            $distinctMatches = 0;
            $totalCompletions = 0;

            // Check how many of the selected distinct work orders this craftsman has experience with
            foreach ($workOrders as $wo) {
                // Find all historical completions that match this specific work order
                $matches = $craftsmanOrders->filter(function ($co) use ($wo) {
                    if ($wo->product_code && $co->product_code === $wo->product_code) return true;
                    if ($wo->design_code && $co->design_code === $wo->design_code) return true;
                    if ($wo->product_category_id && $co->product_category_id === $wo->product_category_id) return true;
                    return false;
                });

                if ($matches->count() > 0) {
                    $distinctMatches++;
                    $totalCompletions += $matches->count();
                }
            }

            if ($totalCompletions > 0) {
                $craftsmanStats[] = [
                    'craftsman' => $craftsman,
                    'distinct_matches' => $distinctMatches,
                    'completed_count' => $totalCompletions,
                    'total_work_orders' => $workOrders->count()
                ];
            }
        }

        // Sort by distinct matches (desc), then by total completions (desc)
        usort($craftsmanStats, function ($a, $b) {
            if ($a['distinct_matches'] !== $b['distinct_matches']) {
                return $b['distinct_matches'] <=> $a['distinct_matches'];
            }
            return $b['completed_count'] <=> $a['completed_count'];
        });

        return array_slice($craftsmanStats, 0, 3);
    }
}
