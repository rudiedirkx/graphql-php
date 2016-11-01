<?php
namespace GraphQL\Type\Definition;

use GraphQL\Type\DefinitionContainer;
use GraphQL\Type\TypeKind;
use GraphQL\Type\TypeResolver;
use GraphQL\Utils;

/**
 * Class NonNull
 * @package GraphQL\Type\Definition
 */
class NonNull extends Type implements WrappingType, OutputType, InputType
{
    /**
     * @var callable|Type
     */
    protected $ofType;

    /**
     * @param callable|Type|DefinitionContainer $type
     * @throws \Exception
     */
    public function __construct($type)
    {
        $type = TypeResolver::resolveType($type);

        Utils::invariant(
            $type instanceof Type || $type instanceof DefinitionContainer || is_callable($type),
            'Expecting instance of GraphQL\Type\Definition\Type or callable returning instance of that class'
        );
        Utils::invariant(
            !($type instanceof NonNull),
            'Cannot nest NonNull inside NonNull'
        );
        $this->ofType = $type;
    }

    /**
     * @param bool $recurse
     * @return mixed
     * @throws \Exception
     */
    public function getWrappedType($recurse = false)
    {
        $type = Type::resolve($this->ofType);

        Utils::invariant(
            !($type instanceof NonNull),
            'Cannot nest NonNull inside NonNull'
        );

        return ($recurse && $type instanceof WrappingType) ? $type->getWrappedType($recurse) : $type;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getWrappedType()->toString() . '!';
    }
}
