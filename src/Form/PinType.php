<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormInterface;

class PinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pin', PasswordType::class, [
                'label' => 'Code PIN',
                'attr' => ['placeholder' => 'Entrez votre code PIN'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le code PIN ne peut pas être vide']),
                    new Assert\Length([
                        'min' => 4,
                        'max' => 4,
                        'exactMessage' => 'Le code PIN doit contenir exactement 4 chiffres.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d{4}$/', // Accepte uniquement des chiffres
                        'message' => 'Le code PIN doit être composé uniquement de chiffres.',
                    ]),
                ],
            ]);
    }
}
