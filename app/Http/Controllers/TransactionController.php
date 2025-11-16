<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\TransactionStoreRequest;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\UserResource;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\Transaction\TransactionService;
use Inertia\Inertia;

class TransactionController extends Controller
{

    public function __construct(
        private TransactionRepository $transactionRepository,
        private TransactionService $transactionService
    ) {}

    public function index()
    {
        $user = request()->user();
        return Inertia::render('transaction/Index', [
            'transactions' => Inertia::optional(
                fn() => TransactionResource::collection(
                    $this->transactionRepository->setQuery(
                        $user->transactions()
                    )->paginate()
                )
            ),
            'user' => UserResource::make($user)
        ]);
    }

    public function create()
    {
        return Inertia::render('transaction/Create');
    }

    public function store(TransactionStoreRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = request()->user();

        $this->transactionService->storeTransaction(
            $user,
            $request->validated()
        );

        return back()->with('success', 'Transaction successful');
    }
}
