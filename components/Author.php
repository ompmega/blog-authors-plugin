<?php namespace Ompmega\BlogAuthors\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ompmega\BlogAuthors\Models\Author as AuthorModel;

class Author extends ComponentBase
{
    public $author;

    public function componentDetails()
    {
        return [
            'name'        => 'Author',
            'description' => 'Display an author\'s profile information.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'Author slug',
                'description' => 'Look up an author using the supplied slug value.',
                'default' => '{{ :slug }}',
                'type' => 'string'
            ]
        ];
    }

    public function onRun()
    {
        $slug = $this->property('slug');

        try {
            $this->author = $this->page['author'] = AuthorModel::where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
    }
}
