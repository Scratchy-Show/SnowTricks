<?php


namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    // Génère le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Pseudo",
                'attr' => [
                    'placeholder' => 'Votre pseudo'
                ],
                'disabled' => true
            ])
            ->add('email', EmailType::class, [
                'label' => "Email",
                'attr' => [
                    'placeholder' => 'nom@example.fr'
                ],
                'disabled' => true
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
