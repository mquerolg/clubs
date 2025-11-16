<?php

namespace App\Diba\Admin\Filter;

use App\Entity\Status;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;

class StatusFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(QueryBuilder $qb, string $propertyName, $label = null): self
    {
        $rows = $qb->select('status.id, status.name')
            ->from(Status::class, 'status')
            ->distinct('status.name')
            ->getQuery()->getResult();

        foreach ($rows as $row) {
            $status[$row['name']] = $row['id'];
        }

        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ChoiceFilterType::class)
            ->setChoices($status ?? [])
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
            $orX->add(sprintf('lot.statusId %s (:%s)', $comparison, $parameterName));

            if (ComparisonType::NEQ === $comparison) {
                $orX->add(sprintf('lot.statusId IS NULL'));
            }

            $queryBuilder->andWhere($orX)
                ->setParameter($parameterName, $value);
        }
    }
}
