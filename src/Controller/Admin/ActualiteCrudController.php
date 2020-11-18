<?php

namespace App\Controller\Admin;

use App\Entity\Actualite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ActualiteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Actualite::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $imageField = ImageField::new('imageFile')
            ->setLabel('Image')
            ->setFormType(VichImageType::class);

        $coverImage = ImageField::new('coverImage')
            ->setBasePath('/images/uploads')
            ->setLabel('Image');

        $fields = [
            TextField::new('title'),
            TextEditorField::new('content'),
        ];
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $fields[] = $coverImage;
        } else {
            $fields[] = $imageField;
        }
        return $fields;
    }

}
