<?php

namespace App\Http\Controllers\Api\Search;

use App\Actions\Search\SearchExternalAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(
        protected SearchExternalAction $searchExternalAction
    ) {}

    public function index(SearchRequest $request): JsonResponse
    {
        $query = $request->queryString();

        $results = $this->searchExternalAction->execute($query, [
            // later: map page, size, language, etc. from $request
        ]);

        return response()->json([
            'query'   => $query,
            'results' => $results,
        ]);
    }
}
