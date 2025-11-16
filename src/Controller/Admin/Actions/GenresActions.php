<?php

namespace App\Controller\Admin\Actions;

use App\Entity\Genres;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * GenresActions
 */
trait GenresActions
{
    public function configureActions(Actions $actions): Actions
    {
        $new = Action::new('newModalAction', 'add_genere', 'fa fa-plus')
            ->linkToCrudAction('newModalAction')
            ->setCssClass('btn btn--add-action')
            ->setHtmlAttributes(['id' => 'modal-request-genres-add'])
            ->createAsGlobalAction();

        $edit = Action::new('editModalAction', '', 'fa fa-pencil')
            ->linkToCrudAction('editModalAction')
            ->setCssClass('btn-line-action modal-request-genres-edit');

        return $actions
            // ... Globals
            ->add(Crud::PAGE_INDEX, $new)
            // ... InLine
            ->add(Crud::PAGE_INDEX, $edit)
            // ... Updates

            // ... Disable
            ->disable('index', Action::DELETE, Action::DETAIL, Action::NEW, Action::EDIT)
            // ... Remove

            // ... Reorder
        ;
    }

    public function newModalAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $name = $context->getRequest()->get('name');
        $active = ((int)$context->getRequest()->get('active')) === 1 ? true : false;

        if (!is_null($name) && !empty($name)) {
            $entityManager = $this->container->get('doctrine')->getManagerForClass($this->getEntityFqcn());

            $genre = new Genres();
            $genre->setName($name);
            $genre->setActive($active == '1' ? 1 : 0);

            $entityManager->persist($genre);
            $entityManager->flush();
        }

        return $this->redirect($this->container->get(AdminUrlGenerator::class)
                    ->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
    }

    public function editModalAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $name = $context->getRequest()->get('name');
        $active = ((int)$context->getRequest()->get('active')) === 1 ? true : false;

        if (!is_null($name) && !empty($name)) {
            $entityManager = $this->container->get('doctrine')->getManagerForClass($this->getEntityFqcn());

            $genre = $context->getEntity()->getInstance();
            $genre->setName($name);
            $genre->setActive($active);

            $entityManager->persist($genre);
            $entityManager->flush();
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }
}
