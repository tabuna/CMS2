<?php

declare(strict_types=1);

namespace Orchid\Press\Http\Layouts\Comment;

use Orchid\Screen\TD;
use Orchid\Screen\Layouts\Table;

class CommentListLayout extends Table
{
    /**
     * @var string
     */
    public $data = 'comments';

    /**
     * @return array
     */
    public function fields(): array
    {
        return [
            TD::set('approved', __('Status'))
                ->render(function ($comment) {
                    if ($comment->approved) {
                        return '<i class="icon-check text-success mx-3"></i>';
                    }

                    return '<i class="icon-close text-danger mx-3"></i>';
                }),

            TD::set('content', __('Content'))
                ->render(function ($comment) {
                    return '<a href="'.route('platform.systems.comments.edit',
                            $comment->id).'">'.str_limit($comment->content, 70).'</a>';
                }),

            TD::set('post_id', __('Recording'))
                ->render(function ($comment) {
                    if (! is_null($comment->post)) {
                        return '<a href="'.route('platform.entities.type.edit', [
                                $comment->post->type,
                                $comment->post->id,
                            ]).'"><i class="icon-text-center mx-3"></i></a>';
                    }

                    return '<i class="icon-close mx-3"></i>';
                })
                ->align(TD::ALIGN_CENTER),

            TD::set('user_id', __('User'))
                ->render(function ($comment) {
                    return '<a href="'.route('platform.systems.users.edit',
                            $comment->user_id).'"><i class="icon-user mx-3"></i></a>';
                })
                ->align(TD::ALIGN_CENTER),

            TD::set('updated_at', __('Last edit'))
                ->render(function ($comment) {
                    return $comment->updated_at;
                }),
        ];
    }
}
