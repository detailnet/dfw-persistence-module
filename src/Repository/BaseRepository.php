<?php

namespace Detail\Persistence\Repository;

use Detail\Commanding\Command\Listing\Sort;
use Detail\Filtering\InputFilter;
use Detail\Persistence\Exception;

abstract class BaseRepository implements
    InputFilter\FilterAwareInterface,
    RepositoryInterface
{
    use InputFilter\FilterAwareTrait;

    const FILTER_CREATE = 'create';
    const FILTER_UPDATE = 'update';

    /**
     * @param array $inputFilters
     */
    public function __construct(array $inputFilters = [])
    {
        $this->setInputFilters($inputFilters);
    }

    /**
     * @param array $orderBy
     * @return Sort[]
     */
    protected function getOrderBy(array $orderBy)
    {
        $sorting = [];

        foreach ($orderBy as $property => $sort) {
            if (!$sort instanceof Sort) {
                $direction = null;

                if (is_array($sort)) {
                    if (!isset($sort['property'])) {
                        throw new Exception\InvalidArgumentException(
                            'Invalid sorting definition; array must contain "property" key'
                        );
                    }

                    $property = $sort['property'];
                    $direction = isset($sort['direction']) ? $sort['direction'] : null;
                } elseif (is_string($sort)) {
                    $direction = $sort;
                } else {
                    throw new Exception\InvalidArgumentException(
                        sprintf(
                            'Invalid sorting definition; expected %s object, array or string; received %s',
                            Sort::CLASS,
                            is_object($sort) ? get_class($sort) : gettype($sort)
                        )
                    );
                }

                $sort = new Sort($property, $direction);
            }

            if (!in_array(strtolower($sort->getDirection()), ['asc', 'desc'])) {
                throw new Exception\RuntimeException(
                    sprintf('Invalid order direction "%s"', $sort->getDirection())
                );
            }

            $sorting[] = $sort;
        }

        return $sorting;
    }

    /**
     * @return string
     */
    abstract protected function getCollectionClass();
}
