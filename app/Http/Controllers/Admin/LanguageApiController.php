<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Http\Resources\Admin\LanguageResource;
use App\Models\FirItem;
use App\Models\Language;
use App\Models\Translation;
use App\Services\LanguageService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class LanguageApiController extends Controller
{
    protected $className = Language::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = LanguageResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
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
        abort_if(Gate::denies('language_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return LanguageResource::collection(Language::advancedFilter());
    }

    public function store(StoreLanguageRequest $request)
    {
        $language = Language::create($request->validated());

        $this->updateRelations($language,$request,true);

        return (new LanguageResource($language))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('language_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(Language $language)
    {
        abort_if(Gate::denies('language_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new LanguageResource($language);
    }

    public function update(UpdateLanguageRequest $request, Language $language)
    {
        $language->update($request->validated());

        $this->updateRelations($language,$request);

        return (new LanguageResource($language))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Language $language)
    {
        abort_if(Gate::denies('language_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new LanguageResource($language->load(['translations:id,language_id,language_term_id,translation'])),
            'meta' => [],
        ]);
    }

    public function destroy(Language $language)
    {
        abort_if(Gate::denies('language_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($language->id == config('system.defaults.language.id'), Response::HTTP_FORBIDDEN, 'You cannot delete the default language.');

        $language->translations()->delete();

        $language->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelations($obj,$request,$isNew = false) {
        if($isNew) {
            LanguageService::copyTranslations($obj);
            return;
        }
        $this->updateTranslations($obj,$request);
    }

    public function updateTranslations($obj,$request)
    {
        $translations = stringToArray($request->input('translations', ''));
        $request->merge(['translations'=>$translations]);
        $this->updateChild($request, $obj, 'translations', Translation::class,'translations','language_id');
    }
}
