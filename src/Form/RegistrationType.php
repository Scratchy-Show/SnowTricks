<?php


namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    // Génère le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Pseudo",
                'attr' => [
                    'placeholder' => 'Votre pseudo'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => "Email",
                'attr' => [
                    'placeholder' => 'nom@example.fr'
                ]
            ])
            ->add('profilPicture', FileType::class, [
                'label' => "Image de profil",
                'attr' => [
                    'placeholder' => 'Image de profil'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => "Mot de passe",
                'attr' => [
                    'placeholder' => 'Votre mot de passe'
                ]
            ])
            ->add('passwordConfirm' , PasswordType::class, [
                'label' => "Confirmation du mot de passe",
                'attr' => [
                    'placeholder' => 'Confirmer votre mot de passe'
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