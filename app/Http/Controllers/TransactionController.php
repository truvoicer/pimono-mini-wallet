<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\TransactionStoreRequest;
use App\Http\Resources\SettingResource;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\Setting;
use App\Repositories\TransactionRepository;
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
        $setting = Setting::with('currency')->first();
        $user = request()->user();
        return Inertia::render('transaction/Index', [
            'transactions' => Inertia::optional(
                fn() => TransactionResource::collection(
                    $this->transactionRepository->setQuery(
                        $user->transactions()->orderBy('created_at', 'desc')
                    )->paginate()
                )
            ),
            'user' => UserResource::make($user),
            'settings' => $setting ? SettingResource::make($setting) : null
        ]);
    }

    public function create()
    {
        $setting = Setting::with('currency')->first();
        return Inertia::render('transaction/Create', [
            'settings' => $setting ? SettingResource::make($setting) : null
        ]);
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
