<?php

namespace App\Diba\Admin\Filter;

use App\Diba\Admin\Filter\Type\LangFilterType;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class LangFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setChoices([
                'Català' => 'langCat',
                'Castellà' => 'langEs',
                'Anglès' => 'langAng',
                'Francès' => 'langFra',
                'Italià' => 'langIta',
                'Alemany' => 'langAle',
            ])
            ->setFormType(LangFilterType::class)
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
        $alias = $filterDataDto->getEntityAlias();
        $comparison = $filterDataDto->getComparison();
        $properties = $filterDataDto->getValue() ?? [];

        foreach ($properties as $property) {
            $orX = new Orx();
            $orX->add(sprintf('%s.%s %s 0', $alias, $property, $comparison));

            $queryBuilder->andWhere($orX);
        }
    }
}
