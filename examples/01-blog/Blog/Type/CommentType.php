<?php
namespace GraphQL\Examples\Blog\Type;

use GraphQL\Examples\Blog\AppContext;
use GraphQL\Examples\Blog\Data\Comment;
use GraphQL\Examples\Blog\TypeSystem;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class CommentType extends BaseType
{
    public function __construct(TypeSystem $types)
    {
        // Option #1: using composition over inheritance to define type, see ImageType for inheritance example
        $this->definition = new ObjectType([
            'name' => 'Comment',
            'fields' => function() use ($types) {
                return [
                    'id' => $types->id(),
                    'author' => $types->user(),
                    'parent' => $types->comment(),
                    'isAnonymous' => $types->boolean(),
                    'replies' => [
                        'type' => $types->listOf($types->comment()),
                        'args' => [
                            'after' => $types->int(),
                            'limit' => [
                                'type' => $types->int(),
                                'defaultValue' => 5
                            ]
                        ]
                    ],
                    'totalReplyCount' => $types->int(),

                    $types->htmlField('body')
                ];
            },
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                if (method_exists($this, $info->fieldName)) {
                    return $this->{$info->fieldName}($value, $args, $context, $info);
                } else {
                    return $value->{$info->fieldName};
                }
            }
        ]);
    }

    public function author(Comment $comment, $args, AppContext $context)
    {
        if ($comment->isAnonymous) {
            return null;
        }
        return $context->dataSource->findUser($comment->authorId);
    }

    public function parent(Comment $comment, $args, AppContext $context)
    {
        if ($comment->parentId) {
            return $context->dataSource->findComment($comment->parentId);
        }
        return null;
    }

    public function replies(Comment $comment, $args, AppContext $context)
    {
        $args += ['after' => null];
        return $context->dataSource->findReplies($comment->id, $args['limit'], $args['after']);
    }

    public function totalReplyCount(Comment $comment, $args, AppContext $context)
    {
        return $context->dataSource->countReplies($comment->id);
    }
}
