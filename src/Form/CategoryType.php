<?php


namespace App\Form;


use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{    // Génère le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => "Catégorie",
            ])
            ->add('add', TextType::class, [
                'label' => "Ajouter une catégorie",
                'attr' => [
                    'placeholder' => 'Nom de la catégorie'
                ],
                'required'   => false
            ])
        ;
    }

    // Associe le formulaire à la classe Category afin d'adapter le type de champ
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            "allow_extra_fields" => true
        ]);
    }
}