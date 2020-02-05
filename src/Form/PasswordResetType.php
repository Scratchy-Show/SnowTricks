<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => "Email",
                'attr' => [
                    'placeholder' => 'nom@example.fr'
                ],
                'constraints' => [
                    new NotBlank(['message' => "Un email doit être indiqué"]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => "Mot de passe",
                'attr' => [
                    'placeholder' => 'Votre nouveau mot de passe'
                ],
                'constraints' => [
                    new Length(['min' => 8, 'minMessage' => "Le mot de passe doit faire au moins {{ limit }} caractèress"]),
                    new NotBlank(['message' => "Un mot de passe doit être indiqué"]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}