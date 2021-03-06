<?php

namespace LanguageServer\Protocol;

use PhpParser\Node;
use Exception;

/**
 * Represents information about programming constructs like variables, classes,
 * interfaces etc.
 */
class SymbolInformation
{
    /**
     * The name of this symbol.
     *
     * @var string
     */
    public $name;

    /**
     * The kind of this symbol.
     *
     * @var int
     */
    public $kind;

    /**
     * The location of this symbol.
     *
     * @var Location
     */
    public $location;

    /**
     * The name of the symbol containing this symbol.
     *
     * @var string|null
     */
    public $containerName;

    /**
     * Converts a Node to a SymbolInformation
     *
     * @param Node $node
     * @param string $fqn If given, $containerName will be extracted from it
     * @return self|null
     */
    public static function fromNode(Node $node, string $fqn = null)
    {
        $symbol = new self;
        if ($node instanceof Node\Stmt\Class_) {
            $symbol->kind = SymbolKind::CLASS_;
        } else if ($node instanceof Node\Stmt\Trait_) {
            $symbol->kind = SymbolKind::CLASS_;
        } else if ($node instanceof Node\Stmt\Interface_) {
            $symbol->kind = SymbolKind::INTERFACE;
        } else if ($node instanceof Node\Stmt\Namespace_) {
            $symbol->kind = SymbolKind::NAMESPACE;
        } else if ($node instanceof Node\Stmt\Function_) {
            $symbol->kind = SymbolKind::FUNCTION;
        } else if ($node instanceof Node\Stmt\ClassMethod) {
            $symbol->kind = SymbolKind::METHOD;
        } else if ($node instanceof Node\Stmt\PropertyProperty) {
            $symbol->kind = SymbolKind::PROPERTY;
        } else if ($node instanceof Node\Const_) {
            $symbol->kind = SymbolKind::CONSTANT;
        } else if (
            (
                ($node instanceof Node\Expr\Assign || $node instanceof Node\Expr\AssignOp)
                && $node->var instanceof Node\Expr\Variable
            )
            || $node instanceof Node\Expr\ClosureUse
            || $node instanceof Node\Param
        ) {
            $symbol->kind = SymbolKind::VARIABLE;
        } else {
            return null;
        }
        if ($node instanceof Node\Expr\Assign || $node instanceof Node\Expr\AssignOp) {
            $symbol->name = $node->var->name;
        } else if ($node instanceof Node\Expr\ClosureUse) {
            $symbol->name = $node->var;
        } else if (isset($node->name)) {
            $symbol->name = (string)$node->name;
        } else {
            return null;
        }
        $symbol->location = Location::fromNode($node);
        if ($fqn !== null) {
            $parts = preg_split('/(::|->|\\\\)/', $fqn);
            array_pop($parts);
            $symbol->containerName = implode('\\', $parts);
        }
        return $symbol;
    }

    /**
     * @param string $name
     * @param int $kind
     * @param Location $location
     * @param string $containerName
     */
    public function __construct($name = null, $kind = null, $location = null, $containerName = null)
    {
        $this->name = $name;
        $this->kind = $kind;
        $this->location = $location;
        $this->containerName = $containerName;
    }
}
