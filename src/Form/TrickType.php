<?php


namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    // Génère le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom",
                'empty_data' => ' ',
                'attr' => [
                    'placeholder' => 'Nom de la figure'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'empty_data' => ' ',
                'attr' => [
                    'placeholder' => 'Description de la figure'
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => "Catégorie"
            ])
            ->add('mainPicture', PictureType::class, [
                'required'   => false,
                'label' => false
            ])
            ->add('pictures', CollectionType::class, [
                'entry_type' => PictureType::class,
                // Permet d'ajouter un nombre illimité d'image
                'allow_add' => true,
                // Permet de supprimer une image
                'allow_delete' => true,
                // Permet d'ajouter les images en appelant la méthode addPicture()
                'by_reference' => false
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                // Permet d'ajouter un nombre illimité de vidéo
                'allow_add' => true,
                // Permet de supprimer une vidéo
                'allow_delete' => true,
                // Permet d'ajouter les vidéos en appelant la méthode addVideo()
                'by_reference' => false
            ])
        ;
    }

    // Associe le formulaire à la classe Trick afin d'adapter le type de champ
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'allow_extra_fields' => true
        ]);
    }
}
