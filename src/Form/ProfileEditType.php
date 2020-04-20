<?php


namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileEditType extends AbstractType
{
    // Génère le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => "Nouveau email",
                'empty_data' => ' ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'nom@example.fr'
                ]
            ])
            ->add('profilPicture', FileType::class, [
                'label' => "Nouvelle image du profil",
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nouvelle image du profil'
                ]
            ])
        ;
    }

    // Associe le formulaire à la classe User afin d'adapter le type de champ
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
