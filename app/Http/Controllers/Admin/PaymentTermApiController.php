<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTermRequest;
use App\Http\Requests\UpdatePaymentTermRequest;
use App\Http\Resources\Admin\PaymentTermResource;
use App\Models\PaymentTerm;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class PaymentTermApiController extends Controller
{
    protected $className = PaymentTerm::class;
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
        abort_if(Gate::denies('payment_term_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return PaymentTermResource::collection(PaymentTerm::advancedFilter());
    }

    public function store(StorePaymentTermRequest $request)
    {
        $paymentTerm = PaymentTerm::create($request->validated());

        return (new PaymentTermResource($paymentTerm))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('payment_term_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(PaymentTerm $paymentTerm)
    {
        abort_if(Gate::denies('payment_term_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PaymentTermResource($paymentTerm);
    }

    public function update(UpdatePaymentTermRequest $request, PaymentTerm $paymentTerm)
    {
        $paymentTerm->update($request->validated());

        return (new PaymentTermResource($paymentTerm))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(PaymentTerm $paymentTerm)
    {
        abort_if(Gate::denies('payment_term_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PaymentTermResource($paymentTerm),
            'meta' => [],
        ]);
    }

    public function destroy(PaymentTerm $paymentTerm)
    {
        abort_if(Gate::denies('payment_term_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $paymentTerm->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
