<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\Admin\AccountResource;
use App\Http\Resources\Admin\TransactionResource;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Buyer;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Services\AccountService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class LedgerApiController extends Controller
{
    protected $className = Transaction::class;
    protected $scopes = [];
    protected $with = [];
    protected $fetcher = 'advancedFilter';
    protected $filterMethods = ['index','getCsv','getPdf'];
    protected $fields = ['name'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;
    public function index()
    {
        abort_if(Gate::denies('account_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return AccountResource::collection(Account::query()->with(['accountType:id,name'])->advancedFilter());
    }

    public function store(StoreAccountRequest $request)
    {
        $account = Account::create($request->validated());

        return (new AccountResource($account))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('account_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Account $account)
    {
        abort_if(Gate::denies('account_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AccountResource($account->load(['accountType:id,name']));
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());

        return (new AccountResource($account))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Account $account)
    {
        abort_if(Gate::denies('account_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new AccountResource($account),
            'meta' => [],
        ]);
    }

    public function destroy(Account $account)
    {
        abort_if(Gate::denies('account_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $protectedIds = array_values(config('accounting.accountType.ids',[]));

        $buyerAccountIds = Buyer::query()->pluck('account_id')->toArray();
        $protectedIds = array_merge($protectedIds,$buyerAccountIds);

        $supplierAccountIds = Supplier::query()->pluck('account_id')->toArray();
        $protectedIds = array_merge($protectedIds,$supplierAccountIds);

        $bankAccountIds = BankAccount::query()->pluck('account_id')->toArray();
        $protectedIds = array_merge($protectedIds,$bankAccountIds);

        abort_if(in_array($account->id, $protectedIds), Response::HTTP_FORBIDDEN, 'Cannot delete the protected account.');

        $account->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function transactions(Account $account)
    {
        return $this->getTransactionsData($account);
    }

    public function transactionsCsv(Account $account) {
        $data = $this->getTransactionsData($account);

        $data = !is_array($data) ? $data->toArray($data) : $data;
        return $this->generateCsv($data);
    }

    private function getTransactionsData(Account $account)
    {
        $accountId = $account->id;

        AccountService::calculateBalance($account);

        $reverseAccountTypes = [];
        $reverseAccountTypes[] = config('accounting.accountType.ids.sundryDebtors');

        $reverse = true;
        if(in_array($account->account_type_id,$reverseAccountTypes)) {
            $reverse = true;
        }

        $filters = [
            ['request'=>'f_date_range','field'=>'date','operator'=>'date_range','separator'=>' to '],
        ];
        $this->prepFilters($filters);

        $transactions = Transaction::query()
            ->where('account_id',$accountId)
            ->with(['account:id,name,has_multi_currency_transaction','currency:id,name,code,symbol','user:id,name'])
            ->advancedFilter();

        $account->load(['currency:id,name,code,symbol']);

        $transactions = TransactionService::calculateBalance($account,$transactions,$reverse);
        return TransactionResource::collection($transactions);
    }
}
