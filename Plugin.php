<?php namespace Ompmega\BlogAuthors;

use Backend;
use Event;
use System\Classes\PluginBase;

/**
 * Blog Authors Plugin Information File
 * @author Ompmega, Daniel Ramirez
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.Blog'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ompmega.blogauthors::lang.plugin.name',
            'description' => 'ompmega.blogauthors::lang.plugin.description',
            'author'      => 'Ompmega',
            'icon'        => 'icon-user-circle-o'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        \RainLab\Blog\Models\Post::extend(function ($model) {
            /** @var \RainLab\Blog\Models\Post $model */

            // Override the "user" relation model
            $model->belongsTo['user'] = [
                'Ompmega\BlogAuthors\Models\Author',
                'table' => 'ompmega_blogauthors_posts_authors'
            ];

            // Adds the query scope for the list filter
            $model->addDynamicMethod('scopeApplyAuthorFilter', function ($query, $filtered) {
                return $query->whereHas('user', function ($q) use ($filtered) {
                    $q->whereIn('id', $filtered);
                });
            });
        });

        // Extends the blog navigation
        Event::listen('backend.menu.extendItems', function ($manager) {
            /** @var \Backend\Classes\NavigationManager $manager */
            $manager->addSideMenuItems('RainLab.Blog', 'blog', [
                'authors' => [
                    'label' => 'ompmega.blogauthors::lang.authors.label',
                    'icon'  => 'icon-user-circle-o',
                    'code'  => 'authors',
                    'owner' => 'RainLab.Blog',
                    'url'   => Backend::url('ompmega/blogauthors/authors'),
                ]
            ]);
        });

        Event::listen('backend.form.extendFields', function ($form) {
            /** @var \Backend\Widgets\Form $form */

            if (!$form->getController() instanceof \RainLab\Blog\Controllers\Posts) {
                return;
            }

            if (!$form->model instanceof \RainLab\Blog\Models\Post) {
                return;
            }

            // Replace the old user selection field
            $form->addSecondaryTabFields([
                'user' => [
                    'label'        => 'ompmega.blogauthors::lang.author.label',
                    'type'         => 'relation',
                    'commentAbove' => 'ompmega.blogauthors::lang.authors.comment',
                    'span'         => 'right',
                    'tab'          => 'rainlab.blog::lang.post.tab_manage'
                ]
            ]);

        });

        Event::listen('backend.list.extendColumns', function ($listWidget) {
            /** @var \Backend\Widgets\Lists $listWidget */
            if (!($listWidget->getController() instanceof \RainLab\Blog\Controllers\Posts)) {
                return;
            }

            if (!($listWidget->model instanceof \RainLab\Blog\Models\Post)) {
                return;
            }

            // Replace the user column with author
            $listWidget->addColumns([
                'user' => [
                    'label'    => 'ompmega.blogauthors::lang.author.label',
                    'relation' => 'user',
                    'select'   => 'name'
                ]
            ]);
        });

        Event::listen('backend.filter.extendScopes', function ($filterWidget) {
            /** @var Backend\Widgets\Filter $filterWidget */
            if (!($filterWidget->getController() instanceof \RainLab\Blog\Controllers\Posts)) {
                return;
            }

            // Add a new filter for filtering by author(s)
            $filterWidget->addScopes([
                'user' => [
                    'label' => 'Author',
                    'scope' => 'applyAuthorFilter',
                    'modelClass' => 'Ompmega\BlogAuthors\Models\Author',
                    'nameFrom' => 'name'
                ]
            ]);
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Ompmega\BlogAuthors\Components\Author' => 'postAuthor',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'ompmega.blogauthors.access_authors' => [
                'tab'   => 'rainlab.blog::lang.blog.tab',
                'label' => 'ompmega.blogauthors::lang.authors.access_authors'
            ],
        ];
    }
}
