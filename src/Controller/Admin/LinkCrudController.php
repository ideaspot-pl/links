<?php

namespace App\Controller\Admin;

use App\Entity\Link;
use App\Enum\LinkStatusEnum;
use App\Service\LinkService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class LinkCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly LinkService $linkService,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Link::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('shortUrl')->hideOnForm()->formatValue(function ($value, $entity) {
                return sprintf('<a href="%s/%s" target="_blank">%s <i class="fa fa-arrow-up-right-from-square"></i></a>', $this->getParameter('app.link_base_url'), $value, $value);
            }),
            TextField::new('longUrl'),
            TextField::new('shortUrl')->onlyOnForms()->setRequired(false),
            BooleanField::new('status')->onlyOnIndex(),
            ChoiceField::new('status')->hideOnIndex()->setChoices([
                'Active' => LinkStatusEnum::STATUS_ACTIVE->value,
                'Inactive' => LinkStatusEnum::STATUS_INACTIVE->value,
            ]),
            DateTimeField::new('createdAt')->hideOnForm(),
            TextField::new('shortUrl')->onlyOnDetail()->formatValue(function ($value, $entity) {
                return sprintf('<img alt="qr code" style="max-width: 100%%" src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=%s" >', "{$this->getParameter('app.link_base_url')}/{$value}");
            }),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('shortUrl')
            ->add('longUrl')
            ->add(ChoiceFilter::new('status')->setChoices([
                'Active' => LinkStatusEnum::STATUS_ACTIVE->value,
                'Inactive' => LinkStatusEnum::STATUS_INACTIVE->value,
            ]))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->linkService->shorten($entityInstance->getLongUrl(), $entityInstance->getShortUrl());
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormOptions([
                'validation_groups' => ['create'],
            ], [
                'validation_groups' => ['create'],
            ])
        ;
    }
}
