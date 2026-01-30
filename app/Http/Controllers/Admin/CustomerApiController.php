<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\Customer;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CustomerApiController extends Controller
{
    protected $className = Customer::class;

    protected $scopes = [];

    protected $with = [];

    protected $exportResource = CustomerResource::class;

    protected $fetcher = 'advancedFilter';

    protected $processListMethod = 'getProcessedList';

    protected $filterMethods = ['index', 'getCsv', 'getPdf'];

    protected $csvFilePrefix = 'customers';

    protected $pdfFilePrefix = 'customers';

    protected $fields = ['name', 'customer_code', 'phone'];

    protected $filters = [];

    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    public function index()
    {
        // abort_if(Gate::denies('customer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return CustomerResource::collection(
            Customer::advancedFilter()
        );
    }

    public function show(Customer $customer)
    {
        // abort_if(Gate::denies('customer_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new CustomerResource($customer->load(['orders']));
    }
}
