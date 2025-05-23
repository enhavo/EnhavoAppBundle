<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\AppBundle\Type;

use Enhavo\Bundle\AppBundle\Exception\TypeMissingException;

/**
 * @author gseidel
 */
class TypeFactory
{
    /**
     * @var TypeCollector
     */
    private $collector;

    /**
     * @var string
     */
    private $class;

    public function __construct($class, TypeCollector $collector)
    {
        $this->class = $class;
        $this->collector = $collector;
    }

    /**
     * @throws TypeMissingException
     *
     * @return object
     */
    public function create($options)
    {
        if (!isset($options['type'])) {
            throw new TypeMissingException(sprintf('No type was given to create "%s"', $this->class));
        }

        $type = $this->collector->getType($options['type']);
        unset($options['type']);
        $class = new $this->class($type, $options);

        return $class;
    }
}
