<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentModeRequest;
use App\Http\Requests\UpdatePaymentModeRequest;
use App\Http\Resources\Admin\PaymentModeResource;
use App\Models\PaymentMode;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;
use App\Services\DatabaseService;

class PaymentModeApiController extends Controller
{
    protected $className = PaymentMode::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PaymentModeResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index','getCsv','getPdf'];
    protected $csvFilePrefix = 'payment-modes-list-';
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;
    public function index()
    {
        abort_if(Gate::denies('payment_mode_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return PaymentModeResource::collection(PaymentMode::advancedFilter());
    }

    /**
     * @throws \Exception
    */
    public function store(StorePaymentModeRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = PaymentMode::query()->create($request->validated());
        });

        return (new PaymentModeResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('payment_mode_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(PaymentMode $paymentMode)
    {
        abort_if(Gate::denies('payment_mode_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PaymentModeResource($paymentMode);
    }

    /**
     * @throws \Exception
    */
    public function update(UpdatePaymentModeRequest $request, PaymentMode $paymentMode)
    {
        DatabaseService::executeTransaction(function () use ($request, $paymentMode) {
            $paymentMode->update($request->validated());
        });

        return (new PaymentModeResource($paymentMode))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(PaymentMode $paymentMode)
    {
        abort_if(Gate::denies('payment_mode_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PaymentModeResource($paymentMode),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
    */
    public function destroy(PaymentMode $paymentMode)
    {
        abort_if(Gate::denies('payment_mode_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($paymentMode) {
            $paymentMode->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
