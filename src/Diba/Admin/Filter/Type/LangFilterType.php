<?php

namespace App\Diba\Admin\Filter\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ComparisonFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LangFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static function ($data) {
                return $data;
            },
            static function ($data) {
                switch ($data['comparison']) {
                    case ComparisonType::CONTAINS:
                        if (null === $data['value'] || (0 === \count($data['value']))) {
                            $data['comparison'] = '=';
                        } else {
                            $data['comparison'] = '>';
                        }
                        break;
                    case ComparisonType::NOT_CONTAINS:
                        if (null === $data['value'] || (0 === \count($data['value']))) {
                            $data['comparison'] = '=';
                        } else {
                            $data['comparison'] = '=';
                        }
                        break;
                }

                return $data;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'comparison_type_options' => ['type' => 'array'],
            'value_type' => ChoiceType::class,
            'value_type_options' => [
                'multiple' => true,
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                ],
            ],
        ]);
        $resolver->setNormalizer('value_type_options', static function (Options $options, $value) {
            if (!isset($value['attr'])) {
                $value['attr']['data-ea-widget'] = 'ea-autocomplete';
            }

            return $value;
        });
    }

    public function getParent(): string
    {
        return ComparisonFilterType::class;
    }
}
