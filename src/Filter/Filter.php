<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminSearchBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;

abstract class Filter extends BaseFilter
{
    protected $active = false;

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value): void
    {
        $this->value = $value;
        if (\is_array($value) && \array_key_exists('value', $value)) {
            list($alias, $field) = $this->association($queryBuilder, $value);

            $this->filter($queryBuilder, $alias, $field, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * {@inheritdoc}
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $queryBuilder->entityJoin($this->getParentAssociationMappings());

        return [$alias, $this->getFieldName()];
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param mixed                                            $parameter
     */
    protected function applyWhere(ProxyQueryInterface $queryBuilder, $parameter): void
    {
        if (self::CONDITION_OR === $this->getCondition()) {
            $queryBuilder->orWhere($parameter);
        } else {
            $queryBuilder->andWhere($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->active = true;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     *
     * @return string
     */
    protected function getNewParameterName(ProxyQueryInterface $queryBuilder)
    {
        // dots are not accepted in a DQL identifier so replace them
        // by underscores.
        return str_replace('.', '_', $this->getName()).'_'.$queryBuilder->getUniqueParameterId();
    }
}
