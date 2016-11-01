<?php
namespace GraphQL\Type\Definition;

use GraphQL\Type\DefinitionContainer;
use GraphQL\Type\TypeResolver;
use GraphQL\Utils;

/**
 * Class ListOfType
 * @package GraphQL\Type\Definition
 */
class ListOfType extends Type implements WrappingType, OutputType, InputType
{
    /**
     * @var callable|Type
     */
    public $ofType;

    /**
     * @param callable|Type|DefinitionContainer $type
     */
    public function __construct($type)
    {
        $type = TypeResolver::resolveType($type);

        Utils::invariant(
            $type instanceof Type || $type instanceof DefinitionContainer || is_callable($type),
            'Expecting instance of GraphQL\Type\Definition\Type or callable returning instance of that class'
        );

        $this->ofType = $type;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $str = $this->ofType instanceof Type ? $this->ofType->toString() : (string) $this->ofType;
        return '[' . $str . ']';
    }

    /**
     * @param bool $recurse
     * @return mixed
     */
    public function getWrappedType($recurse = false)
    {
        $type = Type::resolve($this->ofType);
        return ($recurse && $type instanceof WrappingType) ? $type->getWrappedType($recurse) : $type;
    }
}
