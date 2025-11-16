<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Registry;

final class TemplateRegistry
{
    private $templates = [
        'layout' => 'layout.html.twig',
        'main_menu' => 'menu.html.twig',
        'exception' => 'exception.html.twig',
        'flash_messages' => 'flash_messages.html.twig',
        'crud/paginator' => 'crud/paginator.html.twig',
        'crud/index' => 'crud/index.html.twig',
        'crud/index/lots' => 'crud/index/lots.html.twig',
        'crud/index/historic' => 'crud/index/historic.html.twig',
        'crud/index/genres' => 'crud/index/genres.html.twig',
        'crud/index/uselots' => 'crud/index/uselots.html.twig',
        'crud/index/shipments' => 'crud/index/shipments.html.twig',
        'crud/detail' => 'crud/detail.html.twig',
        'crud/details/clubs' => 'crud/details/clubs.html.twig',
        'crud/details/incidences' => 'crud/details/incidences.html.twig',
        'crud/details/libraries' => 'crud/details/libraries.html.twig',
        'crud/details/lots' => 'crud/details/lots.html.twig',
        'crud/edit/clubs' => 'crud/edit/clubs.html.twig',
        'crud/index/my_clubs' => 'crud/index/my_clubs.html.twig',
        'crud/edit/incidences' => 'crud/edit/incidences.html.twig',
        'crud/edit/libraries' => 'crud/edit/libraries.html.twig',
        'crud/edit/lots' => 'crud/edit/lots.html.twig',
        'crud/new' => 'crud/new.html.twig',
        'crud/new/lots' => 'crud/new/lots.html.twig',
        'crud/new/clubs' => 'crud/new/clubs.html.twig',
        'crud/edit' => 'crud/edit.html.twig',
        'crud/action' => 'crud/action.html.twig',
        'crud/filters' => 'crud/filters.html.twig',
        'crud/field/array' => 'crud/field/array.html.twig',
        'crud/field/association' => 'crud/field/association.html.twig',
        'crud/field/avatar' => 'crud/field/avatar.html.twig',
        'crud/field/bigint' => 'crud/field/bigint.html.twig',
        'crud/field/boolean' => 'crud/field/boolean.html.twig',
        'crud/field/choice' => 'crud/field/choice.html.twig',
        'crud/field/code_editor' => 'crud/field/code_editor.html.twig',
        'crud/field/collection' => 'crud/field/collection.html.twig',
        'crud/field/color' => 'crud/field/color.html.twig',
        'crud/field/country' => 'crud/field/country.html.twig',
        'crud/field/currency' => 'crud/field/currency.html.twig',
        'crud/field/date' => 'crud/field/date.html.twig',
        'crud/field/datetime' => 'crud/field/datetime.html.twig',
        'crud/field/datetimetz' => 'crud/field/datetimetz.html.twig',
        'crud/field/decimal' => 'crud/field/decimal.html.twig',
        'crud/field/email' => 'crud/field/email.html.twig',
        'crud/field/float' => 'crud/field/float.html.twig',
        'crud/field/generic' => 'crud/field/generic.html.twig',
        'crud/field/hidden' => 'crud/field/hidden.html.twig',
        'crud/field/id' => 'crud/field/id.html.twig',
        'crud/field/image' => 'crud/field/image.html.twig',
        'crud/field/integer' => 'crud/field/integer.html.twig',
        'crud/field/language' => 'crud/field/language.html.twig',
        'crud/field/locale' => 'crud/field/locale.html.twig',
        'crud/field/money' => 'crud/field/money.html.twig',
        'crud/field/number' => 'crud/field/number.html.twig',
        'crud/field/percent' => 'crud/field/percent.html.twig',
        'crud/field/raw' => 'crud/field/raw.html.twig',
        'crud/field/smallint' => 'crud/field/smallint.html.twig',
        'crud/field/telephone' => 'crud/field/telephone.html.twig',
        'crud/field/text' => 'crud/field/text.html.twig',
        'crud/field/textarea' => 'crud/field/textarea.html.twig',
        'crud/field/text_editor' => 'crud/field/text_editor.html.twig',
        'crud/field/time' => 'crud/field/time.html.twig',
        'crud/field/timezone' => 'crud/field/timezone.html.twig',
        'crud/field/toggle' => 'crud/field/toggle.html.twig',
        'crud/field/url' => 'crud/field/url.html.twig',
        'label/empty' => 'label/empty.html.twig',
        'label/inaccessible' => 'label/inaccessible.html.twig',
        'label/null' => 'label/null.html.twig',
        'label/undefined' => 'label/undefined.html.twig',
    ];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function has(string $templateName): bool
    {
        return \array_key_exists($templateName, $this->templates);
    }

    public function get(string $templateName): string
    {
        if (!$this->has($templateName)) {
            throw new \InvalidArgumentException(sprintf('The "%s" template is not defined in EasyAdmin. Use one of these allowed template names: %s', $templateName, implode(', ', array_keys($this->templates))));
        }

        return $this->templates[$templateName];
    }

    public function setTemplate(string $templateName, string $templatePath): void
    {
        if (!$this->has($templateName)) {
            throw new \InvalidArgumentException(sprintf('The "%s" template is not defined in EasyAdmin. Use one of these allowed template names: %s', $templateName, implode(', ', array_keys($this->templates))));
        }

        $this->templates[$templateName] = $templatePath;
    }

    public function setTemplates(array $templateNamesAndPaths): void
    {
        foreach ($templateNamesAndPaths as $templateName => $templatePath) {
            $this->setTemplate($templateName, $templatePath);
        }
    }
}
