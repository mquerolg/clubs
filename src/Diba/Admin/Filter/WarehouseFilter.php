<?php

namespace App\Diba\Admin\Filter;

use App\Entity\Support\Warehouses;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;

class WarehouseFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(QueryBuilder $qb, string $propertyName, $label = null): self
    {
        $rows = $qb->select('warehouses.warehouse')
            ->from(Warehouses::class, 'warehouses')
            ->distinct('warehouses.warehouse')
            ->orderBy('warehouses.warehouse', 'ASC')
            ->getQuery()->getResult();

        foreach ($rows as $value) {
            $municipalities[current($value)] = current($value);
        }

        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ChoiceFilterType::class)
            ->setChoices($municipalities ?? [])
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle')
            ->canSelectMultiple(true);
    }

    public function setChoices(array $choices): self
    {
        $this->dto->setFormTypeOption('value_type_options.choices', $choices);

        return $this;
    }

    public function renderExpanded(bool $isExpanded = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.expanded', $isExpanded);

        return $this;
    }

    public function canSelectMultiple(bool $selectMultiple = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.multiple', $selectMultiple);

        return $this;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $comparison = $filterDataDto->getComparison();
        $parameterName = $filterDataDto->getParameterName();
        $value = $filterDataDto->getValue();

        if (null !== $value && is_countable($value) && 0 !== \count($value)) {
            $orX = new Orx();

            $orX->add(sprintf('lot.warehouse %s (:%s)', $comparison, $parameterName));

            if (ComparisonType::NEQ === $comparison) {
                $orX->add(sprintf('lot.warehouse IS NULL'));
            }

            $queryBuilder->andWhere($orX)
                ->setParameter($parameterName, $value);
        }
    }
}
