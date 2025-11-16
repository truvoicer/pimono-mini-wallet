<?php

namespace App\Http\Controllers\Currency\Data;

use App\Http\Controllers\Controller;
use App\Http\Resources\Currency\CurrencyResource;
use App\Http\Resources\CurrencyResource as ResourcesCurrencyResource;
use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{

    public function __construct(
        private CurrencyRepository $currencyRepository
    ) {}

    public function index(Request $request)
    {
        $query =  Currency::query();
        $search = $request->get('query', null);
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        return ResourcesCurrencyResource::collection(
            $this->currencyRepository->setQuery(
                $query
            )->paginate()
        );
    }
}
