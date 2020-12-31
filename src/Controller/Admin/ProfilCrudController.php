<?php

namespace App\Controller\Admin;

use App\Entity\Profil;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Vich\UploaderBundle\Form\Type\VichImageType;


class ProfilCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Profil::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AvatarField::new('avatar'),
            TextEditorField::new('description')
        ];
    }

}
