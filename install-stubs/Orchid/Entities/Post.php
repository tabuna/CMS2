<?php

declare(strict_types=1);

namespace App\Orchid\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Orchid\Press\Entities\Many;
use Orchid\Press\Http\Filters\CreatedFilter;
use Orchid\Press\Http\Filters\SearchFilter;
use Orchid\Press\Http\Filters\StatusFilter;
use Orchid\Press\Models\Category;
use Orchid\Press\Screen\Fields\Tags;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Map;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\SimpleMDE;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\TinyMCE;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\UTM;
use Orchid\Screen\TD;

class Post extends Many
{
    /**
     * @var string
     */
    public $name = 'Example post';

    /**
     * @var string
     */
    public $description = 'Demonstrative post';

    /**
     * @var string
     */
    public $slug = 'post';

    /**
     * Slug url /news/{name}.
     *
     * @var string
     */
    public $slugFields = 'name';

    /**
     * Menu tile name.
     *
     * @var null
     */
    public $title = 'Common Posts';

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(Model $model): Model
    {
        return $model->load(['attachment', 'tags', 'taxonomies'])
            ->setAttribute('category', $model->taxonomies->map(function ($item) {
                return $item->id;
            })->toArray());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function save(Model $model)
    {
        $model->save();

        $model->taxonomies()->sync(Arr::flatten(request(['category'])));
        $model->setTags(request('tags', []));
        $model->attachment()->syncWithoutDetaching(request('attachment', []));
    }

    /**
     * Rules Validation.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id'             => 'sometimes|integer|unique:posts',
            'content.*.name' => 'required|string',
            'content.*.body' => 'required|string',
        ];
    }

    /**
     * HTTP data filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            StatusFilter::class,
            SearchFilter::class,
            CreatedFilter::class,
        ];
    }

    /**
     * @throws \Throwable|\Orchid\Screen\Exceptions\TypeException
     *
     * @return array
     */
    public function fields(): array
    {
        return [

            Field::group([

                Input::make('name')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title('Name Articles')
                    ->help('Article title'),

                Input::make('title')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title('Article Title')
                    ->help('SEO title'),

            ]),

            Field::group([

                DateTimer::make('open')
                    ->title('Opening date')
                    ->help('The opening event will take place'),

                Input::make('phone')
                    ->type('text')
                    ->mask('(999) 999-9999')
                    ->title('Phone')
                    ->help('Number Phone'),

                CheckBox::make('free')
                    ->sendTrueOrFalse()
                    ->title('Free')
                    ->placeholder('Event for free')
                    ->help('Event for free'),
            ]),

            TinyMCE::make('body')
                ->required()
                ->title('Name Articles')
                ->help('Article title'),

            Map::make('place')
                ->required()
                ->title('Object on the map')
                ->help('Enter the coordinates, or use the search'),

            Cropper::make('picture')
                ->width(500)
                ->height(300),

            UTM::make('link')
                ->title('UTM link')
                ->help('Generated link'),

            SimpleMDE::make('body2')
                ->title('Name Articles')
                ->help('Article title'),

            Quill::make('body3')
                ->title('Name Articles')
                ->help('Article title'),

            Code::make('code')
                ->title('Name Articles')
                ->help('Article title'),
        ];
    }

    /**
     * @throws \Orchid\Screen\Exceptions\TypeException
     * @throws \Throwable
     *
     * @return array
     */
    public function main(): array
    {
        return array_merge(parent::main(), [
            Select::make('category.')
                ->options(function () {
                    $options = (new Category())->getAllCategories();

                    return array_replace([0=> __('Without category')], $options);
                })
                ->multiple()
                ->title('Category')
                ->help('Select category'),

            Tags::make('tags')
                ->title('Tags')
                ->help('Keywords'),

            Upload::make('attachment')
                ->title('Upload DropBox'),
        ]);
    }

    /**
     * @throws \Throwable
     *
     * @return array
     */
    public function options(): array
    {
        return [
            TextArea::make('description')
                ->max(255)
                ->rows(5)
                ->required()
                ->title('Short description'),

            DateTimer::make('open')
                ->title('Opening date')
                ->help('The opening event will take place'),
        ];
    }

    /**
     * Grid View for post type.
     */
    public function grid(): array
    {
        return [
            TD::set('id', 'ID')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->filter('numeric')
                ->sort()
                ->linkPost('View'),

            TD::set('name', 'Name')
                ->width('250px')
                ->column('content.name')
                ->filter('text')
                ->sort(),

            TD::set('status')
                ->sort(),

            TD::set('phone', 'Phone')
                ->column('content.phone')
                ->filter('text'),

            TD::set('publish_at', 'Date of publication')
                ->filter('date')
                ->sort()
                ->align(TD::ALIGN_RIGHT)
                ->render(function ($item) {
                    return optional($item->publish_at)->toDateString();
                }),

            TD::set('created_at', 'Date of creation')
                ->filter('date')
                ->align(TD::ALIGN_RIGHT)
                ->sort()
                ->render(function ($item) {
                    return $item->created_at->toDateString();
                }),
        ];
    }
}
