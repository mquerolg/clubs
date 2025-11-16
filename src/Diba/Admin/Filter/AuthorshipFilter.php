<?php

namespace App\Diba\Admin\Filter;

use App\Diba\Admin\Filter\Type\TextFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

final class AuthorshipFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $comparison = $filterDataDto->getComparison();
        $parameterName = $filterDataDto->getParameterName();
        $value = $filterDataDto->getValue();

        $queryBuilder
            ->andWhere(sprintf('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(lot.authorship),\'á\',\'a\'),\'à\',\'a\'),\'ä\',\'a\'),\'é\',\'e\'),\'è\',\'e\'),\'ë\',\'e\'),\'í\',\'i\'),\'ì\',\'i\'),\'ï\',\'i\'),\'ó\',\'o\'),\'ò\',\'o\'),\'ö\',\'o\'),\'ú\',\'u\'),\'ù\',\'u\'),\'ü\',\'u\') %s :%s', $comparison, $parameterName))
            ->setParameter($parameterName, $this->makeValue($value));
    }

    public function makeValue($value)
    {
        $value = mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');

        return str_replace(
            ['á', 'à', 'ä', 'é', 'è', 'ë', 'í', 'ì', 'ï', 'ó', 'ò', 'ö', 'ú', 'ù', 'ü'],
            ['a', 'a', 'a', 'e', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'u', 'u', 'u'],
            $value
        );
    }
}
