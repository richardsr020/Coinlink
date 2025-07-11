<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', null, [
            'attr' => ['class' => 'form-control'],
        ])
        ->add('lastname', null, [
            'attr' => ['class' => 'form-control'],
        ])
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'form-control'],
        ])
        ->add('password', PasswordType::class, [
            'attr' => ['class' => 'form-control'],
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
